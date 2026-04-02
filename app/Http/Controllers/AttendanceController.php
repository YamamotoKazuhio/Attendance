<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 勤務表メイン画面（個人用・管理者閲覧用）
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        /** @var \App\Models\User $targetUser */
        $targetUser = $user;
        $query = $request->input('search_query');

        // 管理者が検索した場合、対象ユーザーを切り替える
        if ($user->is_admin && $query) {
            $foundUser = User::whereHas('detail', function ($q) use ($query) {
                $q->where('employee_code', $query);
            })
                ->orWhere('name', 'LIKE', "%{$query}%")
                ->first();

            if ($foundUser) {
                $targetUser = $foundUser;
            }
        }

        $targetUser->load('detail');

        // 表示対象月の設定
        $targetMonth = $request->input('month', date('Y-m'));
        $targetMonth = substr($targetMonth, 0, 7);

        // 勤怠データの取得
        $attendances = Attendance::where('user_id', $targetUser->id)
            ->where('work_date', 'LIKE', "{$targetMonth}%")
            ->orderBy('work_date', 'asc')
            ->get();

        // 祝日データの取得
        $holidays = $this->getPublicHolidays();

        return view('attendance', [
            'user' => $user,
            'targetUser' => $targetUser,
            'attendances' => $attendances,
            'targetMonth' => $targetMonth, // キー名をBlade側に合わせる
            'holidays' => $holidays,
        ]);
    }

    /**
     * 勤怠データの保存（JSからのPOST）
     */
    public function store(Request $request)
    {
        $data = $request->input('attendances');
        $targetUserId = $request->input('user_id');

        if (!$targetUserId) {
            return response()->json(['message' => 'ユーザーIDが指定されていません。'], 400);
        }

        try {
            DB::transaction(function () use ($data, $targetUserId) {
                foreach ($data as $item) {
                    // デバッグ用：$itemの中身をログに焼く（後で確認するため）
                    // \Log::info('Saving item: ', (array)$item);

                    // JS側から 'date' または 'work_date' という名前で日付が来ているか確認
                    $date = $item['date'] ?? ($item['work_date'] ?? null);

                    if (!$date) continue;

                    // 休憩時間の取得
                    $rest = (isset($item['rest']) && $item['rest'] !== "") ? (float)$item['rest'] : 0;

                    // データベースへの書き込み
                    \App\Models\Attendance::updateOrCreate(
                        [
                            'user_id'   => $targetUserId,
                            'work_date' => $date
                        ],
                        [
                            'attendance_type' => $item['type'] ?? '',
                            'workplace'       => $item['workplace'] ?? '',
                            'shift'           => $item['shift'] ?? '',
                            'start_time'      => !empty($item['start']) ? $item['start'] : null,
                            'end_time'        => !empty($item['end']) ? $item['end'] : null,
                            'break_time'      => $rest,
                            'overtime'        => (float)($item['ot'] ?? 0),
                            'work_time'       => (float)($item['work'] ?? 0),
                            'is_locked'       => $item['is_locked'] ?? 0,
                        ]
                    );
                }
            });

            return response()->json(['message' => 'データベースに保存しました。']);
        } catch (\Exception $e) {
            Log::error("勤怠保存失敗: " . $e->getMessage());
            return response()->json(['message' => '保存エラー: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 管理者用：月次レポート集計一覧
     */
    public function adminMonthlyReport(Request $request)
    {
        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $targetMonth = $request->input('month', date('Y-m'));
        $targetMonth = substr($targetMonth, 0, 7);

        // ユーザーと、その月の勤怠データを一括取得
        $users = User::with(['detail', 'attendances' => function ($q) use ($targetMonth) {
            $q->where('work_date', 'LIKE', "{$targetMonth}%");
        }])->get();

        $usersData = $users->map(function ($user) {
            $monthData = $user->attendances;
            $detail = $user->detail;

            // 1. 稼働日数の集計
            $workDays = $monthData->whereIn('attendance_type', ['出勤', '振替出勤', '現場出勤', '通常勤務'])->count();

            // 2. 今月の有給取得日数（全休1.0 + 半休0.5）
            $paidFull = $monthData->where('attendance_type', '有給')->count();
            $halfPaid = $monthData->whereIn('attendance_type', ['午前半休', '午後半休'])->count() * 0.5;
            $monthlyPaidDays = $paidFull + $halfPaid;

            // 3. 基本情報と通勤費計算
            $dailyCommute = (float)($user->daily_commute_fee ?? 0);
            $totalCommute = $workDays * $dailyCommute;

            // 4. 有給残数（将来的にusersテーブルにカラムを追加することを想定）
            // 現状カラムがない場合は一律0を表示するように安全策をとる
            $remainingPaid = $user->paid_leave_remaining ?? 0;

            // 5. その月のデータが1件以上あるか確認
            $hasData = $monthData->isNotEmpty();

            // 6. 全てのデータが 1（承認待ち）以上か判定
            $isAllLockedOrMore = $hasData && $monthData->every(fn($att) => $att->is_locked >= 1);

            // 7. 全てのデータが 2（承認済み）以上か判定
            $isAllApprovedOrMore = $hasData && $monthData->every(fn($att) => $att->is_locked >= 2);

            // 表示用ステータスを決定
            $statusText = '未確定';
            if ($isAllApprovedOrMore) {
                $statusText = '承認済み・変更不可';
            } elseif ($isAllLockedOrMore) {
                $statusText = '確定（承認待ち）';
            }

            return [
                'id'              => $user->id,
                'emp_id'          => $detail->employee_code ?? '-',
                'name'            => $detail->name_kanji ?? $user->name,
                'monthly_paid'    => $monthlyPaidDays, // 今月の有給取得日数
                'remaining_paid'  => $remainingPaid,   // 有給残日数
                'total_work'      => $monthData->sum('work_time'),
                'total_ot'        => $monthData->sum('overtime'),
                'total_commute'   => $totalCommute,
                'status_text'     => $statusText,
                'is_finalized'    => $isAllLockedOrMore ? 1 : 0,
            ];
        });

        return view('admin.monthly_report', [
            'users'       => $usersData,
            'targetMonth' => $targetMonth,
        ]);
    }

    /**
     * 管理者用：月次確定
     */
    public function finalizeMonth(Request $request)
    {
        return $this->setLockStatus($request, 1, '月次確定しました。');
    }

    /**
     * 管理者用：確定解除
     */
    public function reopenMonth(Request $request)
    {
        return $this->setLockStatus($request, 0, '確定を解除しました。');
    }

    /**
     * ロック状態の一括更新
     */
    private function setLockStatus(Request $request, $status, $message)
    {
        $userId = $request->input('user_id');
        $month = $request->input('month');

        if (!$userId || !$month) {
            return back()->with('error', '不正なリクエストです。');
        }

        Attendance::where('user_id', $userId)
            ->where('work_date', 'LIKE', "{$month}%")
            ->update(['is_locked' => $status]);

        return back()->with('success', $message);
    }

    /**
     * 基本情報（所属・通勤費）の更新
     */
    public function updateBasicInfo(Request $request)
    {
        $userId = $request->input('user_id');
        $department = $request->input('department');
        $commuteFee = $request->input('commute_fee');

        try {
            $user = User::findOrFail($userId);
            $user->department = $department;
            $user->daily_commute_fee = $commuteFee;
            $user->save();

            return response()->json(['message' => '基本情報を保存しました。']);
        } catch (\Exception $e) {
            return response()->json(['message' => '保存失敗: ' . $e->getMessage()], 500);
        }
    }

    public function automateMonth(Request $request)
    {
        $targetMonth = $request->input('month'); // "2026-03"
        $thresholdDays = 20; // 判定基準（20日）

        $users = User::with(['detail', 'attendances' => function ($q) use ($targetMonth) {
            $q->where('work_date', 'LIKE', "{$targetMonth}%");
        }])->get();

        $results = [];

        foreach ($users as $user) {
            $attendances = $user->attendances;
            $errors = [];

            // 1. 入力日数のカウント
            $filledDays = $attendances->whereNotNull('attendance_type')
                ->where('attendance_type', '!=', '')
                ->count();

            // 2. 入力日数チェック
            if ($filledDays < $thresholdDays) {
                $errors[] = "入力日数が不足しています（現在{$filledDays}日 / 目標{$thresholdDays}日）";
            }

            // 3. 各日の内容チェック（既存のロジック）
            foreach ($attendances as $att) {
                if (in_array($att->attendance_type, ['出勤', '現場出勤']) && (!$att->start_time || !$att->end_time)) {
                    $errors[] = "{$att->work_date}: 打刻漏れがあります。";
                }
                // 他のチェック（開始 > 終了など）もここに含まれる
            }

            // 4. 全チェックを通過した場合のみ一括承認（確定）
            $isApproved = false;
            if (empty($errors)) {
                Attendance::where('user_id', $user->id)
                    ->where('work_date', 'LIKE', "{$targetMonth}%")
                    ->update(['is_locked' => 2]);
                $isApproved = true;
            }

            $results[] = [
                'emp_code'   => $user->detail->employee_code ?? '-',
                'name'       => $user->detail->name_kanji ?? $user->name,
                'dept'       => $user->department ?? '未設定',
                'total_work' => $attendances->sum('work_time'),
                'total_ot'   => $attendances->sum('overtime'),
                'filled_days' => $filledDays, // 追加：入力日数
                'threshold'   => $thresholdDays,
                'errors'     => $errors,
                'status'     => $isApproved ? '承認済み' : '要修正',
            ];
        }

        return redirect()->route('admin.monthly.result')->with([
            'automation_results' => $results,
            'target_month' => $targetMonth
        ]);
    }

    public function showAutomationResult()
    {
        $results = session('automation_results');
        if (!$results) return redirect()->route('admin.monthly_report');

        return view('admin.automation_result', [
            'results' => $results,
            'month'   => session('target_month')
        ]);
    }

    /**
     * 祝日データの取得 (内閣府CSV)
     */
    private function getPublicHolidays()
    {
        return Cache::remember('japan_public_holidays', 86400, function () {
            $url = 'https://www8.cao.go.jp/chosei/shukujitsu/syukujitsu.csv';
            try {
                $response = Http::get($url);
                if ($response->successful()) {
                    $content = mb_convert_encoding($response->body(), 'UTF-8', 'SJIS');
                    $lines = explode("\n", $content);
                    $holidays = [];
                    foreach ($lines as $index => $line) {
                        if ($index === 0 || empty(trim($line))) continue;
                        $columns = explode(',', $line);
                        if (isset($columns[0])) {
                            try {
                                $holidays[] = Carbon::parse($columns[0])->format('Y-m-d');
                            } catch (\Exception $e) {
                            }
                        }
                    }
                    return $holidays;
                }
            } catch (\Exception $e) {
                Log::warning("Holiday Fetch Error: " . $e->getMessage());
            }
            return [];
        });
    }
}
