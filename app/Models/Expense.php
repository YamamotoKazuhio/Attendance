<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    // 一括代入を許可する属性
    protected $fillable = [
        'user_id',
        'occured_date',
        'category',
        'subcategory',
        'departure',
        'destination',
        'payee',
        'amount',
        'prepaid',
        'claim_amount',
        'receipt',
        'status',          // 追加
        'reject_reason'    // 追加
    ];

    /**
     * この経費を登録したユーザーを取得（リレーション）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}