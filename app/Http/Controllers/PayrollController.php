<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\PayrollSetting;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * 給与管理一覧画面
     */
    public function index(Request $request)
    {
        $targetMonth = $request->input('month', date('Y-m'));
        $targetMonth = substr($targetMonth, 0, 7);

        // 検索パラメータの取得
        $searchDepartment = $request->input('department');
        $searchEmployeeCode = $request->input('employee_code');
        $searchName = $request->input('name');

        // DBから給与設定値をロード
        $baseSalaries = PayrollSetting::getVal('base_salaries', [1 => 250000, 2 => 300000, 3 => 350000, 4 => 400000, 5 => 450000]);
        $raisePerYear = PayrollSetting::getVal('raise_per_year', 5000);
        $allowances = PayrollSetting::getVal('allowances', [
            'skill' => 50000, 
            'position_k' => 50000, 
            'position_b' => 100000,
            'resident_tax' => 15000, 
            'care_insurance' => 3000, 
            'reserve_fund' => 5000
        ]);

        // クエリの構築
        $query = User::with(['detail', 'attendances' => function ($q) use ($targetMonth) {
            $q->where('work_date', 'LIKE', "{$targetMonth}%");
        }]);

        // 部署名で絞り込み
        if (!empty($searchDepartment)) {
            $query->whereHas('detail', function ($q) use ($searchDepartment) {
                $q->where('department', $searchDepartment);
            });
        }

        // 社員番号で絞り込み
        if (!empty($searchEmployeeCode)) {
            $query->whereHas('detail', function ($q) use ($searchEmployeeCode) {
                $q->where('employee_code', 'LIKE', "%{$searchEmployeeCode}%");
            });
        }

        // 氏名で絞り込み (users.name または user_details.name_kanji)
        if (!empty($searchName)) {
            $query->where(function ($q) use ($searchName) {
                $q->where('name', 'LIKE', "%{$searchName}%")
                  ->orWhereHas('detail', function ($subQ) use ($searchName) {
                      $subQ->where('name_kanji', 'LIKE', "%{$searchName}%");
                  });
            });
        }

        $users = $query->get();

        // プルダウン用の部署一覧を取得（重複なし）
        $departments = UserDetail::whereNotNull('department')
            ->distinct()
            ->pluck('department');

        $payrollData = $users->map(function ($user) use ($targetMonth, $baseSalaries, $raisePerYear, $allowances) {
            $detail = $user->detail;
            if (!$detail) {
                return null; 
            }

            $attendances = $user->attendances;

            // 【判定】月内に勤務データが存在し、かつすべて「承認済み」かつ `is_locked == 1` かどうか
            $isCompleted = false;
            if ($attendances->isNotEmpty()) {
                $isCompleted = $attendances->every(function ($att) {
                    return $att->status === '承認済み' && $att->is_locked == 1;
                });
            }

            // 1. 基本給の算出（レベルに応じた額 + 勤続年数に応じた昇給額）
            $level = $detail->salary_level ?? 1; 
            $baseSalary = $baseSalaries[$level] ?? 250000;

            // 勤続年数の計算（入社日から当月末まで）
            $joinedAt = $detail->joined_at ? Carbon::parse($detail->joined_at) : Carbon::parse('2020-04-01');
            $yearsOfService = max(0, $joinedAt->diffInYears(Carbon::parse($targetMonth . '-01')));
            $raiseAmount = $yearsOfService * $raisePerYear;
            
            // 画面や計算のベースとなる基本給（レベル基本給 + 昇給分）
            $totalBaseSalary = $baseSalary + $raiseAmount;

            // 2. 技能手当
            $skillAllowance = $allowances['skill'] ?? 50000;

            // 3. 役職手当
            $positionAllowance = 0;
            if ($detail->role === 'Class_K') {
                $positionAllowance = $allowances['position_k'] ?? 50000;
            } elseif ($detail->role === 'Class_B') {
                $positionAllowance = $allowances['position_b'] ?? 100000;
            }

            // 4. 残業手当
            $totalOvertime = $attendances->sum('overtime');
            $hourlyRate = ($totalBaseSalary / 160);
            $overtimeAllowance = round($hourlyRate * 1.25 * $totalOvertime);

            // 5. 通勤手当
            $commuteFeePerDay = (float)($detail->commute_fee ?? 0);
            $workDays = $attendances->whereIn('attendance_type', ['出勤', '振替出勤', '現場出勤', '通常勤務'])->count();
            $commuteAllowance = $commuteFeePerDay * $workDays;

            // 6. 不就労控除
            $absentDays = $attendances->where('attendance_type', '欠勤')->count();
            $absentDeduction = ($totalBaseSalary / 20) * $absentDays;

            $lateEarlyHours = $attendances->sum('late_early_hours');
            $lateEarlyDeduction = ($totalBaseSalary / 20 / 8) * $lateEarlyHours;
            
            $totalDeduction = round($absentDeduction + $lateEarlyDeduction);

            // 総支給額
            $grossPay = $totalBaseSalary + $skillAllowance + $positionAllowance + $overtimeAllowance + $commuteAllowance - $totalDeduction;

            // 7. 各種控除
            $incomeTax = round($grossPay * 0.05);
            $residentTax = $allowances['resident_tax'] ?? 15000;
            $socialInsurance = round($grossPay * 0.15);
            $careInsurance = $allowances['care_insurance'] ?? 3000;
            $reserveFund = $allowances['reserve_fund'] ?? 5000;

            // 8. 銀行振込額
            $totalDeductionsTax = $incomeTax + $residentTax + $socialInsurance + $careInsurance + $reserveFund;
            $netPay = $grossPay - $totalDeductionsTax;

            return [
                'department'         => $detail->department ?? '-',
                'employee_code'      => $detail->employee_code ?? '-',
                'name_kanji'         => $detail->name_kanji ?? $user->name,
                'is_completed'       => $isCompleted, // 登録・承認完了フラグ
                'base_salary'        => $totalBaseSalary, // 昇給反映後の基本給
                'skill_allowance'    => $skillAllowance,
                'position_allowance' => $positionAllowance,
                'overtime_allowance' => $overtimeAllowance,
                'commute_allowance'  => $commuteAllowance,
                'total_deduction'    => $totalDeduction,
                'gross_pay'          => $grossPay,
                'income_tax'         => $incomeTax,
                'resident_tax'       => $residentTax,
                'social_insurance'   => $socialInsurance,
                'care_insurance'     => $careInsurance,
                'reserve_fund'       => $reserveFund,
                'net_pay'            => $netPay,
            ];
        })->filter();

        return view('payment.payroll', [
            'payrollData'        => $payrollData,
            'targetMonth'        => $targetMonth,
            'departments'        => $departments,
            'searchDepartment'   => $searchDepartment,
            'searchEmployeeCode' => $searchEmployeeCode,
            'searchName'         => $searchName,
        ]);
    }

    /**
     * 給与設定画面の表示
     */
    public function settingsIndex()
    {
        $baseSalaries = PayrollSetting::getVal('base_salaries', [1 => 250000, 2 => 300000, 3 => 350000, 4 => 400000, 5 => 450000]);
        $raisePerYear = PayrollSetting::getVal('raise_per_year', 5000);
        $allowances = PayrollSetting::getVal('allowances', [
            'skill' => 50000, 
            'position_k' => 50000, 
            'position_b' => 100000,
            'resident_tax' => 15000, 
            'care_insurance' => 3000, 
            'reserve_fund' => 5000
        ]);

        return view('payment.settings', compact('baseSalaries', 'raisePerYear', 'allowances'));
    }

    /**
     * 給与設定の保存
     */
    public function settingsUpdate(Request $request)
    {
        $baseSalaries = $request->input('base_salaries', []);
        $raisePerYear = $request->input('raise_per_year', 5000);
        $allowances = $request->input('allowances', []);

        PayrollSetting::setVal('base_salaries', array_map('intval', $baseSalaries), '基本給（レベル1〜5）');
        PayrollSetting::setVal('raise_per_year', (int)$raisePerYear, '昇給額（円/年）');
        PayrollSetting::setVal('allowances', array_map('intval', $allowances), '各種手当・控除設定');

        return redirect()->route('payment.settings')->with('success', '給与設定を保存しました。');
    }
}