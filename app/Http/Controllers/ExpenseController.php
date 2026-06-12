<?php

/* app/Http/Controllers/ExpenseController.php */

namespace App\Http\Controllers;

use App\Models\Expense;
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
        // 経費データと一緒に user とその先の detail (社員番号・漢字氏名用) もロード
        $adminExpenses = Expense::with(['user.detail'])
            ->where('status', '>=', 0)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('expenses_admin', compact('adminExpenses'));
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
            'claim_amount'  => $amount - $prepaid,
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
        // 修正: Boolean値として判定する
        // 管理者であれば true、自分の申請であれば true になるようにします
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
