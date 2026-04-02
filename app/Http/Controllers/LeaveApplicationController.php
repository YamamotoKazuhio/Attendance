<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveApplication;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LeaveApplicationController extends Controller
{
    /**
     * ユーザーからの申請を保存する (POST /attendance/leave-apply)
     */
    public function store(Request $request)
    {
        try {
            // 1. バリデーション
            $request->validate([
                'target_date' => 'required|date',
                'type'        => 'required|string',
                'days_count'  => 'required|numeric',
                'reason'      => 'nullable|string|max:255',
            ]);

            // 【追加】重複チェック：同一ユーザー、同一日、かつ「申請中(0)」または「承認済み(1)」のデータがあるか
            $exists = LeaveApplication::where('user_id', Auth::id())
                ->where('target_date', $request->target_date)
                ->whereIn('status', [0, 1])
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => '重複エラー：その日は既に申請済みか、承認されています。'
                ], 422);
            }

            // 2. データの作成
            LeaveApplication::create([
                'user_id'     => Auth::id(),
                'target_date' => $request->target_date,
                'type'        => $request->type,
                'days_count'  => (float)$request->days_count, // 数値として確実にキャスト
                'reason'      => $request->reason,
                'status'      => 0, // 0: 申請中
            ]);

            return response()->json(['message' => '申請を送信しました。']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => '入力内容に不備があります。', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // エラーをログ(storage/logs/laravel.log)に記録
            Log::error("LeaveApplication Store Error: " . $e->getMessage());
            return response()->json(['message' => 'サーバー内でエラーが発生しました。'], 500);
        }
    }

    /**
     * 管理者用：申請一覧を表示 (GET /admin/leave-requests)
     */
    public function adminIndex()
    {
        // 未承認(status=0)の申請を、対象日が近い順に取得
        $applications = LeaveApplication::with('user')
            ->where('status', 0)
            ->orderBy('target_date', 'asc')
            ->get();

        return view('admin.leave_requests', compact('applications'));
    }

    /**
     * 管理者用：承認処理 (POST /admin/leave-requests/{id}/approve)
     */
    public function approve(LeaveApplication $leaveApplication)
    {
        try {
            DB::transaction(function () use ($leaveApplication) {
                // 1. ステータスを「承認済み」に更新
                $leaveApplication->update([
                    'status' => 1,
                    'approved_by' => Auth::id()
                ]);

                // 2. 有給系の場合のみ、ユーザーの残日数を減らす
                $isPaidLeave = str_contains($leaveApplication->type, '有給') || str_contains($leaveApplication->type, '半休');

                if ($isPaidLeave && $leaveApplication->days_count > 0) {
                    $user = $leaveApplication->user;
                    // カラム名などは既存のUserモデルの実装に準拠
                    $user->decrement('paid_leave_remaining', $leaveApplication->days_count);
                }

                // 3. 勤務表(attendances)へ反映
                $attType = ($leaveApplication->type === '残業申請') ? '出勤' : $leaveApplication->type;

                Attendance::updateOrCreate(
                    [
                        'user_id'   => $leaveApplication->user_id,
                        'work_date' => $leaveApplication->target_date
                    ],
                    [
                        'attendance_type' => $attType,
                        'is_locked'       => 2, // 2: 承認済み（本人編集不可）
                    ]
                );
            });

            return back()->with('success', "{$leaveApplication->user->name}さんの申請を承認しました。");
        } catch (\Exception $e) {
            Log::error("LeaveApplication Approve Error: " . $e->getMessage());
            return back()->with('error', '承認処理中にエラーが発生しました。');
        }
    }

    /**
     * 管理者用：却下処理 (POST /admin/leave-requests/{id}/reject)
     */
    public function reject(LeaveApplication $leaveApplication)
    {
        // 承認済み(1)の状態からでも却下(2)に変更可能にする
        $leaveApplication->update(['status' => 2]);

        return back()->with('success', '申請を却下（または承認取り消し）しました。');
    }
}
