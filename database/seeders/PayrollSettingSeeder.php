<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollSetting;

class PayrollSettingSeeder extends Seeder
{
    public function run(): void
    {
        // 基本給の初期値（レベル1〜5）
        PayrollSetting::setVal('base_salaries', [
            1 => 250000,
            2 => 300000,
            3 => 350000,
            4 => 400000,
            5 => 450000,
        ], '基本給（レベル1〜5）');

        // 昇給額（年数に応じた額、初期値5000円/年）
        PayrollSetting::setVal('raise_per_year', 5000, '昇給額（円/年）');

        // 各種手当・控除・税金等のデフォルト設定
        PayrollSetting::setVal('allowances', [
            'skill' => 50000,          // 技能手当
            'position_k' => 50000,     // 役職手当 (Class_K)
            'position_b' => 100000,    // 役職手当 (Class_B)
            'resident_tax' => 15000,   // 住民税（一律等）
            'care_insurance' => 3000,  // 介護保険料
            'reserve_fund' => 5000,    // 積立金
        ], '各種手当・固定控除の初期設定');
    }
}