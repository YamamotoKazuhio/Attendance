<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceTestUserSeeder extends Seeder
{
    public function run()
    {
        $user = User::where('name', 'TEST')->first();
        if (!$user) {
            $this->command->error("ユーザー 'TEST' が見つかりません。");
            return;
        }

        $year = 2026;
        $month = 2;
        
        // --- 追記：2月の祝日リスト ---
        $holidays = [
            "$year-02-11", // 建国記念の日
            "$year-02-23", // 天皇誕生日
            "$year-02-24", // 振替休日（2026年は23日が月曜なので火曜が振替）
        ];

        $startDate = Carbon::create($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;
        $data = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::create($year, $month, $day);
            $dateStr = $currentDate->format('Y-m-d');

            // --- 判定ロジックの修正 ---
            $isWeekend = $currentDate->isWeekend();
            $isHoliday = in_array($dateStr, $holidays); // 祝日かチェック

            // 土日、または祝日の場合は「休日」として扱う
            if ($isWeekend || $isHoliday) {
                $data[] = [
                    'user_id'         => $user->id,
                    'work_date'       => $dateStr,
                    'attendance_type' => '休日',
                    'workplace'       => '',
                    'shift'           => '',
                    'start_time'      => null,
                    'end_time'        => null,
                    'break_time'      => 0,
                    'overtime'        => 0,
                    'work_time'       => 0,
                    'is_locked'       => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            } else {
                // 平日の「出勤」データ
                $data[] = [
                    'user_id'         => $user->id,
                    'work_date'       => $dateStr,
                    'attendance_type' => '出勤',
                    'workplace'       => '社内・本社',
                    'shift'           => '常勤：09:00 - 18:00',
                    'start_time'      => '09:00:00',
                    'end_time'        => '18:00:00',
                    'break_time'      => 1.0,
                    'overtime'        => 0,
                    'work_time'       => 8.0,
                    'is_locked'       => 0,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
        }

        // 既存データの削除と再投入
        Attendance::where('user_id', $user->id)
                  ->where('work_date', 'LIKE', "$year-02%")
                  ->delete();

        Attendance::insert($data);
        $this->command->info("ユーザー 'TEST' の2月分（祝日対応済）データを投入しました。");
    }
}