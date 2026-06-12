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
        'department',
        'commute_fee',
        'employee_code',          // 社員番号
        'name_kanji',             // 氏名
        'joined_at',              // 入社年月日
        'address_city',           // 現住所（市町村）
        'phone_number',           // 連絡先
        'emergency_contact',      // 緊急連絡先
        'emergency_relationship', // 本人との関係
        'department',
    ];

    /**
     * この詳細情報を所有するユーザーを取得（逆リレーション）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 日付キャストの定義
    protected $casts = [
        'joined_at' => 'date', // または 'datetime'
    ];
}
