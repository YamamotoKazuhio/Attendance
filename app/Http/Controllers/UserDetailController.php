<?php

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
        $departments = UserDetail::getDepartmentsList();
        $roles = UserDetail::getRolesList();

        return view('user_details.show', compact('user', 'departments', 'roles'));
    }

    /**
     * 更新処理
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        $is_admin = (bool)$currentUser->is_admin;

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

        // 管理者の場合のみ追加されるルール（部署・職域の選択肢バリデーション含む）
        if ($is_admin) {
            $rules['employee_code'] = 'required|string|max:50';
            $rules['name_kanji']    = 'required|string|max:100';
            $rules['joined_at']     = 'required|date';
            $rules['department']    = 'required|string|in:' . implode(',', array_keys(UserDetail::getDepartmentsList()));
            $rules['role']          = 'required|string|in:' . implode(',', array_keys(UserDetail::getRolesList()));
        }

        $validatedData = $request->validate($rules);

        // --- 1. Userテーブルの基本情報更新 (email, password) ---
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // --- 2. UserDetailテーブルの更新 (リレーション先) ---
        $detailData = collect($validatedData)->except(['email', 'password', 'password_confirmation'])->toArray();

        $user->detail()->updateOrCreate(
            ['user_id' => $user->id],
            $detailData
        );
        
        // 最新の状態にリロード（モデルイベントによる is_admin の自動連動を反映させるため）
        $user->refresh();

        return back()->with('success', '情報を更新しました。');
    }

    /**
     * 管理者用：社員一覧
     */
    public function adminIndex(Request $request)
    {
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
                $user->formatted_joined_at = $joinedDate->format('Y年m月d日');
                
                $totalMonths = $joinedDate->diffInMonths($now);
                $user->experience_years = number_format($totalMonths / 12, 1);
            } else {
                $user->formatted_joined_at = '未設定';
                $user->experience_years = '0.0';
            }

            // 有給残数の計算（修正済み）
            $totalGranted = $user->calculateTotalGrantedDays();
            $usedPaid = $user->getUsedPaidLeaveDays();
            $user->remaining_paid = $totalGranted - $usedPaid;

            // 管理者フラグの判定（システム部所属なら自動で○になる）
            $user->admin_label = $user->is_admin ? '○' : '';
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * 管理者用：新規ユーザー登録画面の表示
     */
    public function create()
    {
        $departments = UserDetail::getDepartmentsList();
        $roles = UserDetail::getRolesList();

        return view('admin.users.create', compact('departments', 'roles'));
    }

    /**
     * 管理者用：新規ユーザーの保存処理
     */
    public function storeByAdmin(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403, '登録権限がありません。');
        }

        // バリデーション
        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => 'required|email|max:255|unique:users,email',
            'password'               => ['required', 'confirmed', Password::min(8)],
            'employee_code'          => 'required|string|max:50|unique:user_details,employee_code',
            'name_kanji'             => 'required|string|max:100',
            'department'             => 'required|string|in:' . implode(',', array_keys(UserDetail::getDepartmentsList())),
            'role'                   => 'required|string|in:' . implode(',', array_keys(UserDetail::getRolesList())),
            'joined_at'              => 'required|date',
            'address_city'           => 'required|string|max:255',
            'phone_number'           => 'required|string|max:20',
            'emergency_contact'      => 'required|string|max:20',
            'emergency_relationship' => 'required|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            // システム部の場合は is_admin を 1 に、それ以外は 0 に設定
            $isAdmin = ($request->department === UserDetail::DEPT_SYSTEM) ? 1 : 0;

            // 1. users テーブルの作成
            $user = User::create([
                'name'     => $request->name_kanji,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $isAdmin,
            ]);

            // 2. user_details テーブルの作成
            UserDetail::create([
                'user_id'                => $user->id,
                'employee_code'          => $request->employee_code,
                'name_kanji'             => $request->name_kanji,
                'department'             => $request->department,
                'role'                   => $request->role,
                'joined_at'              => $request->joined_at,
                'address_city'           => $request->address_city,
                'phone_number'           => $request->phone_number,
                'emergency_contact'      => $request->emergency_contact,
                'emergency_relationship' => $request->emergency_relationship,
            ]);

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', '新しい社員を登録しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('新規社員登録エラー: ' . $e->getMessage());
            return back()->withInput()->with('error', '登録中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    /**
     * 社員情報の復元
     */
    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);

        if ($user->trashed()) {
            $user->restore();
            return redirect()->route('admin.users.index')->with('success', '社員「' . $user->name . '」さんを在職状態に復元しました。');
        }

        return back()->with('error', 'この社員はすでに在職状態です。');
    }

    /**
     * 社員情報の削除（退職処理）
     */
    public function destroy(User $user)
    {
        if (!Auth::user()->is_admin) {
            abort(403, '削除権限がありません。');
        }

        if (Auth::id() === $user->id) {
            return back()->with('error', '自分自身のアカウントを削除することはできません。');
        }

        try {
            DB::beginTransaction();

            if ($user->detail) {
                $user->detail()->delete();
            }

            $user->delete();

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', '社員「' . $user->name . '」さんの退職処理を完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("退職処理エラー: " . $e->getMessage());
            return back()->with('error', '削除中にエラーが発生しました。制約違反の可能性があります。');
        }
    }
}