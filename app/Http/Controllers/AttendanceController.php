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
        /** @var \App\Models\User $user */
        $user = Auth::user();
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

        // ユーザー詳細情報を読み込む
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

        return view('attendance', compact('targetUser', 'targetMonth', 'attendances', 'holidays'));
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
                    Log::info("DEBUG: 保存対象データ", ['date' => $item['work_date'] ?? 'なし', 'status' => $item['status'] ?? 'なし']);
                    $date = $item['work_date'] ?? null;
                    if (!$date) continue;

                    $rest = (isset($item['break_time']) && $item['break_time'] !== "") ? (float)$item['break_time'] : 0;

                    // 1. 勤怠データの保存
                    Attendance::updateOrCreate(
                        [
                            'user_id'   => $targetUserId,
                            'work_date' => $date
                        ],
                        [
                            'status'          => $item['status'] ?? '登録',
                            'attendance_type' => $item['attendance_type'] ?? '',
                            'workplace'       => $item['workplace'] ?? '',
                            'shift'           => $item['shift'] ?? '',
                            'start_time'      => !empty($item['start_time']) ? $item['start_time'] : null,
                            'end_time'        => !empty($item['end_time']) ? $item['end_time'] : null,
                            'break_time'      => $rest,
                            'overtime'        => (float)($item['overtime'] ?? 0),
                            'work_time'       => (float)($item['work_time'] ?? 0),
                        ]
                    );

                    // 2. 「承認待ち」の場合、申請テーブルへも反映
                    if (($item['status'] ?? '') === '承認待ち') {
                        \App\Models\LeaveApplication::updateOrCreate(
                            [
                                'user_id'     => $targetUserId,
                                'target_date' => $date,
                            ],
                            [
                                'type'       => $item['attendance_type'] ?? '未分類',
                                'days_count' => ($item['attendance_type'] === '有給') ? 1.0 : 0,
                                'reason'     => '勤務表より一括申請',
                                'status'     => 0 // 承認待ち
                            ]
                        );
                    }
                }
            });

            Log::info("勤怠および申請データの保存完了: User ID " . $targetUserId);
            return response()->json(['message' => 'データベースに保存しました。']);

        } catch (\Exception $e) {
            Log::error("勤怠保存失敗: " . $e->getMessage());
            return response()->json(['message' => '保存エラー: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 基本情報（所属・通勤費）の更新
     */
    public function updateBasicInfo(Request $request)
    {
        $userId = $request->input('user_id'); 
        $department = $request->input('department');
        $commuteFee = $request->input('commute_fee');

        if (!$userId) return response()->json(['message' => 'ユーザー不明'], 400);

        try {
            UserDetail::updateOrCreate(
                ['user_id' => $userId],
                [
                    'department' => $department,
                    'commute_fee' => $commuteFee
                ]
            );
            return response()->json(['message' => '基本情報を保存しました。']);
        } catch (\Exception $e) {
            Log::error("基本情報保存失敗: " . $e->getMessage());
            return response()->json(['message' => '保存に失敗しました。'], 500);
        }
    }

    /**
     * 管理者用：月次レポート集計一覧
     */
    public function adminMonthlyReport(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->load('detail');
        $role = optional($user->detail)->role;

        // is_admin = 1 または ロールが Class_B / Class_K の場合のみ許可
        $canAccess = $user->is_admin == 1 || in_array($role, ['Class_B', 'Class_K']);

        if (!$canAccess) {
            abort(403, '月次集計画面へのアクセス権限がありません。');
        }

        $targetMonth = substr($request->input('month', date('Y-m')), 0, 7);

        // ユーザーデータと今月の勤怠をまとめて取得するクエリの構築
        $query = User::with(['detail', 'attendances' => function ($q) use ($targetMonth) {
            $q->where('work_date', 'LIKE', "{$targetMonth}%");
        }]);

        // 【要件の絞り込み】
        // is_admin = 0 かつ Class_B または Class_K の場合は、自身と同じ部署のユーザーのみに限定する
        if ($user->is_admin != 1 && in_array($role, ['Class_B', 'Class_K'])) {
            $myDepartment = optional($user->detail)->department;
            $query->whereHas('detail', function ($q) use ($myDepartment) {
                $q->where('department', $myDepartment);
            });
        }

        $users = $query->get();

        $usersData = $users->map(function ($targetUser) use ($targetMonth) {
            $monthData = $targetUser->attendances;
            $detail = $targetUser->detail;

            // 1. 今月の有給取得日数の集計
            $monthlyPaid = $monthData->reduce(function ($carry, $att) {
                if ($att->attendance_type === '有給') return $carry + 1.0;
                if (in_array($att->attendance_type, ['午前半休', '午後半休'])) return $carry + 0.5;
                return $carry;
            }, 0);

            // 2. 基本情報の集計
            $workDays = $monthData->whereIn('attendance_type', ['出勤', '振替出勤', '現場出勤', '通常勤務'])->count();
            $dailyCommute = (float)($detail->commute_fee ?? 0);
            
            // 確定状態の判定 (is_locked カラムを使用)
            $isFinalized = $monthData->contains('is_locked', 1);

            return [
                'id'               => $targetUser->id,
                'email'            => $targetUser->email,
                'emp_id'           => $detail->employee_code ?? '-',
                'name'             => $targetUser->name,
                'name_kanji'       => $detail->name_kanji ?? '',
                'department'       => $detail->department ?? '-',
                'work_days'        => $workDays,
                'monthly_paid'     => $monthlyPaid,
                'remaining_paid'   => $targetUser->paid_leave_remaining, 
                'total_work'       => $monthData->sum('work_time'),
                'total_ot'         => $monthData->sum('overtime'),
                'total_commute'    => $workDays * $dailyCommute,
                'is_finalized'     => $isFinalized,
            ];
        });

        return view('admin.monthly_report', [
            'users' => $usersData,
            'targetMonth' => $targetMonth,
        ]);
    }

    public function finalizeMonth(Request $request) { return $this->setLockStatus($request, 1, '月次確定しました。'); }
    public function reopenMonth(Request $request) { return $this->setLockStatus($request, 0, '確定を解除しました。'); }

    private function setLockStatus(Request $request, $status, $message)
    {
        $userId = $request->input('user_id');
        $month = $request->input('month');

        if (!$userId || !$month) return back()->with('error', '不正なリクエストです。');

        Attendance::where('user_id', $userId)
            ->where('work_date', 'LIKE', "{$month}%")
            ->update(['is_locked' => $status]);

        return back()->with('success', $message);
    }

    public function automateMonth(Request $request) { return redirect()->route('admin.monthly_report'); }
    public function showAutomationResult() { return view('admin.automation_result'); }

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
                            try { $holidays[] = Carbon::parse($columns[0])->format('Y-m-d'); } catch (\Exception $e) {}
                        }
                    }
                    return $holidays;
                }
            } catch (\Exception $e) { Log::warning("Holiday Fetch Error: " . $e->getMessage()); }
            return [];
        });
    }

    public function toggleStatus($id)
    {
        $attendance = Attendance::findOrFail($id);
    
        if ($attendance->status === '承認済み') {
            $attendance->status = '登録';
            $attendance->is_locked = 0; 
        } else {
            $attendance->status = '承認済み';
            $attendance->is_locked = 1; 
        }
    
        $attendance->save();
        return back()->with('success', 'ステータスを更新しました。');
    }
}