<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_date',
        'type',
        'days_count',
        'reason',
        'status',
        'approved_by',
    ];

    // 申請したユーザーへのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 承認者（管理者）へのリレーション
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
