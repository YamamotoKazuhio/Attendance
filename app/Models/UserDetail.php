<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDetail extends Model
{
    /**
     * 一括指定（Mass Assignment）を許可する属性
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'employee_code',          // 社員番号
        'name_kanji',             // 氏名
        'joined_at',              // 入社年月日
        'department',             // 所属部署
        'role',                   // 職域（Class_B, Class_K, Class_NA）
        'commute_fee',
        'address_city',           // 現住所（市町村）
        'phone_number',           // 連絡先
        'emergency_contact',      // 緊急連絡先
        'emergency_relationship', // 本人との関係
    ];

    /**
     * 部長職以上の定義
     */
    const ROLE_CLASS_B = 'Class_B';
    /**
     * 係長職以上〜部長職未満の定義
     */
    const ROLE_CLASS_K = 'Class_K';
    /**
     * 一般社員層の定義
     */
    const ROLE_CLASS_NA = 'Class_NA';

    public static function getRolesList()
    {
        return [
            self::ROLE_CLASS_B  => 'B級職（部長職以上）',
            self::ROLE_CLASS_K  => 'K級職（係長〜部長未満）',
            self::ROLE_CLASS_NA => '一般職（一般社員層）',
        ];
    }

    /**
     * 5部門の定義
     */
    const DEPT_SYSTEM = 'システム部';
    const DEPT_GENERAL = '総務部';
    const DEPT_SALES = '営業部';
    const DEPT_DEV = '開発部';
    const DEPT_MFG = '製造部';

    public static function getDepartmentsList()
    {
        return [
            self::DEPT_SYSTEM  => 'システム部（システム部門・システム管理）',
            self::DEPT_GENERAL => '総務部（総務部門）',
            self::DEPT_SALES   => '営業部（営業部門）',
            self::DEPT_DEV     => '開発部（開発部門）',
            self::DEPT_MFG     => '製造部（製造部門）',
        ];
    }

    /**
     * この詳細情報を所有するユーザーを取得（逆リレーション）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 日付キャストの定義
     */
    protected $casts = [
        'joined_at' => 'date',
    ];

    /**
     * モデルの保存イベント：部署に応じた管理者権限（is_admin）の自動連動
     */
    protected static function booted()
    {
        static::saving(function ($userDetail) {
            if ($userDetail->user) {
                // システム部の場合は is_admin = 1、それ以外は 0 に自動連動
                if ($userDetail->department === self::DEPT_SYSTEM) {
                    $userDetail->user->is_admin = 1;
                } else {
                    $userDetail->user->is_admin = 0;
                }
                $userDetail->user->save();
            }
        });
    }
}