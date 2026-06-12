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
        Log::info("デバッグ: 現在接続しているDBパスは: " . config('database.connections.sqlite.database'));

        try {
            // ユーザーIDが取得できているか確認
            $userId = Auth::id();
            Log::info("デバッグ: ユーザーID確認", ['user_id' => $userId]);

            if (!$userId) {
                throw new \Exception("ユーザーがログインしていません。");
            }

            // 1. バリデーション（一旦エラーを出力させるために細分化）
            $request->validate([
                'target_date' => 'required',
                'type'        => 'required',
                'days_count'  => 'required',
            ]);

            // 2. 作成処理
            $application = LeaveApplication::create([
                'user_id'     => $userId,
                'target_date' => $request->target_date,
                'type'        => $request->type,
                'days_count'  => $request->days_count,
                'reason'      => $request->reason ?? '勤務表から選択',
                'status'      => 0,
            ]);

            Log::info("デバッグ: DB保存完了 ID: " . $application->id);
            return response()->json(['message' => '申請成功'], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("バリデーションエラー: ", $e->errors());
            return response()->json(['message' => 'バリデーション失敗'], 422);
        } catch (\Exception $e) {
            Log::error("エラー発生: " . $e->getMessage());
            return response()->json(['message' => 'エラー: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 管理者用：申請一覧を表示 (GET /admin/leave-requests)
     */
    public function adminIndex()
    {
        // 1. デバッグ用に全件取得してログ出力（問題切り分けのため残しています）
        $allApplications = LeaveApplication::all();
        Log::info("デバッグ: DB内の全申請データ数: " . $allApplications->count());

        // 2. 承認対象として許可するタイプを定義
        $allowedTypes = ['午前半休', '午後半休', '有給', '特別休暇', '休出'];

        // 3. 承認待ち かつ 指定したタイプのみを取得
        $applications = LeaveApplication::with('user.detail')
            ->where('status', 0)
            ->whereIn('type', $allowedTypes)
            ->orderBy('target_date', 'asc')
            ->get();

        Log::info("デバッグ: 抽出された承認待ちデータ数: " . $applications->count());

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
                        'status'          => '承認済み',
                        'is_locked'       => 1, // 1: 承認済み（本人編集不可）
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
