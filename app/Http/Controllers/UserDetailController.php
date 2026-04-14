<?php

/* app/Http/Controllers/UserDetailController.php */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserDetailController extends Controller

{
    /**
     * 詳細情報の表示
     */
    public function show(User $user)
    {
        // 管理者でない、かつ本人でない場合は閲覧拒否
        if (!Auth::user()->is_admin && Auth::id() !== $user->id) {
            abort(403, '閲覧権限がありません。');
        }

        $user->load('detail');
        return view('user_details.show', compact('user'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        $is_admin = (bool)Auth::user()->is_admin;

        // 管理者でない、かつ本人でない場合は更新拒否
        if (!$is_admin && Auth::id() !== $user->id) {
            abort(403, '更新権限がありません。');
        }

        // --- バリデーションルールの定義 ---
        $rules = [
            'email'                  => 'required|email|max:255|unique:users,email,' . $user->id,
            'address_city'           => 'required|string|max:255',
            'phone_number'           => 'required|string|max:20',
            'emergency_contact'      => 'required|string|max:20',
            'emergency_relationship' => 'required|string|max:50',
        ];

        // パスワード入力がある場合のみバリデーションを追加
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        // 管理者の場合のみ追加されるルール
        if ($is_admin) {
            $rules['employee_code'] = 'required|string|max:50';
            $rules['name_kanji']    = 'required|string|max:100';
            $rules['joined_at']     = 'required|date';
            $rules['is_admin']      = 'required|boolean';
        }

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
        }

        $validatedData = $request->validate($rules);

        // --- 1. Userテーブルの基本情報更新 (email, password) ---
        $user->email = $request->email;

        // 管理者が操作している場合のみ、権限を更新（自分自身の権限変更はミスの元なので、他人の場合のみにするのが安全）
        if ($is_admin && $currentUser->id !== $user->id) {
            $user->is_admin = $request->is_admin;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // --- 2. UserDetailテーブルの更新 (リレーション先) ---
        // $validatedDataからUserテーブル側の項目を除外して更新
        $detailData = collect($validatedData)->except(['email', 'password', 'password_confirmation'])->toArray();

        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $detailData
        );

        return back()->with('success', '情報を更新しました。');
    }

    /**
     * 管理者用：社員一覧
     */
    // App\Http\Controllers\UserDetailController.php

    /**
     * 管理者用：社員一覧
     */
    public function adminIndex(Request $request) // 引数に Request を追加
    {
        // 1. クエリの作成
        $query = User::with('detail');

        // URLパラメータ ?tab=all がある場合は退職者も含める
        if ($request->query('tab') === 'all') {
            $query->withTrashed();
        }

        $users = $query->get();
        $now = \Carbon\Carbon::now();

        foreach ($users as $user) {
            $detail = $user->detail;
            $joinedAt = optional($detail)->joined_at;

            if ($joinedAt) {
                $joinedDate = \Carbon\Carbon::parse($joinedAt);

                // 1. 入社年月日のフォーマット整形
                $user->formatted_joined_at = $joinedDate->format('Y年m月d日');

                // 2. 勤続年数の計算
                $totalMonths = $joinedDate->diffInMonths($now);
                $user->experience_years = number_format($totalMonths / 12, 1);
            } else {
                $user->formatted_joined_at = '未設定';
                $user->experience_years = '0.0';
            }

            // 3. 有給残数の計算
            // 退職者の場合、現在の日付ではなく「退職日」までの計算にするなどの工夫も可能ですが、
            // 現状は既存ロジックを維持します。
            $totalGranted = $user->calculateTotalGrantedDays();
            $usedPaid = $user->getUsedPaidLeaveDays();
            $user->remaining_paid = $totalGranted - $usedPaid;

            //4. 管理者フラグの判定（表示用）
            $user->admin_label = $user->is_admin ? '○' : '';
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * 社員情報の復元（追加分）
     */
    public function restore($id)
    {
        // IDから退職者も含めて検索
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            $user->restore(); // deleted_at を NULL に戻す
            return redirect()->route('admin.users.index')->with('success', '社員「' . $user->name . '」さんを在職状態に復元しました。');
        }

        return back()->with('error', 'この社員はすでに在職状態です。');
    }

    /**
     * 社員情報の削除
     */
    public function destroy(User $user)
    {
        // 管理者チェック
        if (!Auth::user()->is_admin) {
            abort(403, '削除権限がありません。');
        }

        // 本人削除の禁止
        if (Auth::id() === $user->id) {
            return back()->with('error', '自分自身のアカウントを削除することはできません。');
        }

        try {
            // トランザクション開始（データの整合性を守る）
            DB::beginTransaction();

            // 1. user_details テーブルのデータを削除（物理削除でOK、またはSoftDeletes設定があれば論理削除）
            if ($user->detail) {
                $user->detail()->delete();
            }

            // 2. attendances テーブルのデータをどうするか判断
            // ※退職後も勤務履歴を残す必要がある場合は、ここは何もしない。
            // ※ただし、DB制約でエラーが出る場合は、この段階で削除するか、
            //   マイグレーションで 'on delete cascade' を設定する必要があります。

            // 3. ユーザー本体を論理削除（SoftDeletesが効いているので、deleted_atに日付が入る）
            $user->delete();

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', '社員「' . $user->name . '」さんの退職処理を完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            // ログに詳細なエラーを出す（後で確認するため）
            Log::error("退職処理エラー: " . $e->getMessage());
            return back()->with('error', '削除中にエラーが発生しました。制約違反の可能性があります。');
        }
    }
}
