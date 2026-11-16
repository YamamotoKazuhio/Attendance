<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    /**
     * 一般ユーザー用：経費精算メイン画面
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // ログインユーザーの詳細情報をロード
        if ($user) {
            $user->load('detail');
        }

        // ログインユーザーの最新30件を取得
        $expenses = Expense::where('user_id', $user->id)
            ->orderBy('occured_date', 'desc')
            ->orderBy('id', 'desc')
            ->take(30)
            ->get();

        // viewに $user を渡す
        return view('expenses', compact('expenses', 'user'));
    }

    /**
     * 管理者専用：経費承認パネル画面
     */
    public function adminIndex()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('detail');
        $role = optional($user->detail)->role;

        // 権限チェック：is_admin = 1 または ロールが Class_B / Class_K のみ許可
        $canAccess = $user->is_admin == 1 || in_array($role, ['Class_B', 'Class_K']);

        if (!$canAccess) {
            abort(403, '経費承認パネルへのアクセス権限がありません。');
        }

        // 経費データと一緒に user とその先の detail をロード
        $query = Expense::with(['user.detail'])
            ->where('status', '>=', 0);

        // is_admin = 0 かつ Class_B または Class_K の場合は、自身と同じ部署のユーザーの申請のみに限定する
        if ($user->is_admin != 1 && in_array($role, ['Class_B', 'Class_K'])) {
            $myDepartment = optional($user->detail)->department;
            $query->whereHas('user.detail', function ($q) use ($myDepartment) {
                $q->where('department', $myDepartment);
            });
        }

        $adminExpenses = $query->orderBy('updated_at', 'desc')->get();

        return view('expenses_admin', compact('adminExpenses'));
    }

    /**
     * 支払管理：承認済み経費一覧画面（検索対応）
     */
    public function paymentAdminIndex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('detail');
        $role = optional($user->detail)->role;

        // 権限チェック（管理者 または Class_B / Class_K のみアクセス可能）
        $canAccess = $user->is_admin == 1 || in_array($role, ['Class_B', 'Class_K']);
        if (!$canAccess) {
            abort(403, '経費支払管理画面へのアクセス権限がありません。');
        }

        // 検索パラメータの取得
        $targetMonth = $request->input('month', date('Y-m'));
        $targetMonth = substr($targetMonth, 0, 7);
        $searchDepartment = $request->input('department');
        $searchEmployeeCode = $request->input('employee_code');
        $searchName = $request->input('name');

        // 【要件】承認済み（status = 2）のデータのみ取得
        $query = Expense::with(['user.detail'])
            ->where('status', 2);

        // 対象月で絞り込み（occured_dateの年月）
        if (!empty($targetMonth)) {
            $query->where('occured_date', 'LIKE', "{$targetMonth}%");
        }

        // 部署名で絞り込み
        if (!empty($searchDepartment)) {
            $query->whereHas('user.detail', function ($q) use ($searchDepartment) {
                $q->where('department', $searchDepartment);
            });
        }

        // 社員番号で絞り込み
        if (!empty($searchEmployeeCode)) {
            $query->whereHas('user.detail', function ($q) use ($searchEmployeeCode) {
                $q->where('employee_code', 'LIKE', "%{$searchEmployeeCode}%");
            });
        }

        // 氏名で絞り込み
        if (!empty($searchName)) {
            $query->whereHas('user', function ($q) use ($searchName) {
                $q->where('name', 'LIKE', "%{$searchName}%")
                  ->orWhereHas('detail', function ($subQ) use ($searchName) {
                      $subQ->where('name_kanji', 'LIKE', "%{$searchName}%");
                  });
            });
        }

        $expenses = $query->orderBy('occured_date', 'desc')->get();

        // 部署一覧プルダウン用
        $departments = UserDetail::whereNotNull('department')->distinct()->pluck('department');

        return view('payment.expenses_admin', compact(
            'expenses', 
            'targetMonth', 
            'departments', 
            'searchDepartment', 
            'searchEmployeeCode', 
            'searchName'
        ));
    }

    /**
     * 経費データの新規登録
     */
    public function store(Request $request)
    {
        $request->validate([
            'occured_date' => 'required|date',
            'category'     => 'required|string',
            'subcategory'  => 'required|string',
            'amount'       => 'required|numeric',
            'prepaid'      => 'nullable|numeric',
        ]);

        $amount = $request->amount;
        $prepaid = $request->prepaid ?? 0;

        Expense::create([
            'user_id'       => Auth::id(),
            'occured_date'  => $request->occured_date,
            'category'      => $request->category,
            'subcategory'   => $request->subcategory,
            'departure'     => $request->departure,
            'destination'   => $request->destination,
            'payee'         => $request->payee,
            'amount'        => $amount,
            'prepaid'       => $prepaid,
            'claim_amount'  => $amount - $prepaid, // 演算子抜けを修正
            'receipt'       => $request->receipt,
            'status'        => 0, // 0: 申請待ち
        ]);

        return response()->json(['message' => '登録が完了しました']);
    }

    /**
     * ステータス更新（申請・承認・却下）
     */
    public function updateStatus(Request $request, Expense $expense)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 権限チェック：管理者であるか、もしくはデータの所有者本人のみ許可
        if (!$user->is_admin && $user->id !== $expense->user_id) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        $newStatus = (int)$request->status;
        $data = ['status' => $newStatus];

        // 却下（status: 3）の場合、却下理由を保存
        if ($newStatus === 3) {
            $data['reject_reason'] = $request->reject_reason;
        }
        // 承認（status: 2）または再申請などの場合は理由をクリア
        else {
            $data['reject_reason'] = null;
        }

        $expense->update($data);

        return response()->json(['message' => 'ステータスを更新しました']);
    }

    /**
     * データの削除
     */
    public function destroy(Expense $expense)
    {
        // 自分のデータのみ削除可能
        if (Auth::id() !== $expense->user_id) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        // 承認済みのデータは削除不可とする
        if ($expense->status === 2) {
            return response()->json(['error' => '承認済みのデータは削除できません'], 400);
        }

        $expense->delete();

        return response()->json(['message' => 'データを削除しました']);
    }
}