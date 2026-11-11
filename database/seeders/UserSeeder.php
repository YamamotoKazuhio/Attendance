<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'システム部' => 'sys',
            '総務部'     => 'soumu',
            '営業部'     => 'eigyo',
            '開発部'     => 'kaihatsu',
            '製造部'     => 'seizo',
        ];

        $empIndex = 1;

        foreach ($departments as $dept => $prefix) {
            // is_admin はシステム部 (1)、それ以外 (0)
            $isAdmin = ($dept === 'システム部') ? 1 : 0;

            for ($i = 1; $i <= 10; $i++) {
                $employeeCode = 'EMP' . sprintf('%03d', $empIndex);

                // 職位・ロールの割り当て (1人目: Class_B, 2人目: Class_K, 3〜10人目: 一般職)
                if ($i === 1) {
                    $roleClass = UserDetail::ROLE_CLASS_B; // 'Class_B'
                } elseif ($i === 2) {
                    $roleClass = UserDetail::ROLE_CLASS_K; // 'Class_K'
                } else {
                    $roleClass = UserDetail::ROLE_CLASS_NA; // 'Class_NA'
                }

                // メールアドレスと氏名の特例指定
                if ($dept === 'システム部' && $i === 1) {
                    $email = 'admin@example.com';
                    $name = 'システム 部長';
                } elseif ($dept === '総務部' && $i === 3) {
                    $email = 'test@example.com';
                    $name = '総務 太郎';
                } else {
                    $email = "{$prefix}_{$i}@example.com";
                    $name = "{$dept} 職員{$i}";
                }

                // 1. users テーブルへの保存
                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'     => $name,
                        'password' => Hash::make('Passw0rd!'),
                        'is_admin' => $isAdmin,
                    ]
                );

                // 2. user_details テーブルへの保存（社員番号、部署、職域、入社日など）
                UserDetail::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_code'          => $employeeCode,
                        'name_kanji'             => $name,
                        'department'             => $dept,
                        'role'                   => $roleClass,
                        'joined_at'              => '2020-04-01', // 入社日を追加
                        'address_city'           => '東京都千代田区',
                        'phone_number'           => '090-0000-' . sprintf('%04d', $empIndex),
                        'emergency_contact'      => '090-9999-' . sprintf('%04d', $empIndex),
                        'emergency_relationship' => '家族',
                    ]
                );

                $empIndex++;
            }
        }
    }
}