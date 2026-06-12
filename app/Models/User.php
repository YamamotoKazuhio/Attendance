<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'department',         // 追加
        'daily_commute_fee',  // 追加
    ];

    /**
     * シリアライズ時に隠す属性
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * キャスト属性
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    // --- リレーション定義 ---

    /**
     * ユーザー詳細情報 (UserDetail) とのリレーション
     */
    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }

    /**
     * 勤務データ (Attendance) とのリレーション
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * 休暇申請 (LeaveApplication) とのリレーション
     */
    public function leaveApplications()
    {
        return $this->hasMany(LeaveApplication::class);
    }

    // --- 有給休暇 計算ロジック ---

    /**
     * 法律（労働基準法）に基づく総付与日数を計算
     */
    public function getTotalPaidLeaveDays()
    {
        if (!$this->detail || !$this->detail->joined_at) {
            return 0;
        }

        $joinedDate = Carbon::parse($this->detail->joined_at);
        $now = Carbon::now();
        $years = $joinedDate->diffInYears($now);
        $months = $joinedDate->diffInMonths($now);

        // 継続勤務年数に応じた付与日数 (労働基準法準拠)
        if ($months < 6) return 0;   // 6ヶ月未満
        if ($years < 1)  return 10;  // 0.5年
        if ($years < 2)  return 11;  // 1.5年
        if ($years < 3)  return 12;  // 2.5年
        if ($years < 4)  return 14;  // 3.5年
        if ($years < 5)  return 16;  // 4.5年
        if ($years < 6)  return 18;  // 5.5年

        return 20; // 6.5年以上
    }

    /**
     * 有給使用日数を計算
     * 勤怠テーブル（attendances）の実績データから「承認済み」のものを集計します
     */
    public function getUsedPaidLeaveDays()
{
    // attendances テーブルの実績を正とする（'承認済み' かつ '有給'関連）
    return $this->attendances()
        ->where('status', '承認済み')
        ->whereIn('attendance_type', ['有給', '午前半休', '午後半休'])
        ->get()
        ->sum(function ($att) {
            return ($att->attendance_type === '有給') ? 1.0 : 0.5;
        });
}
    /**
     * 有給残日数 (アクセサ)
     * ビュー側で $user->paid_leave_remaining と書くとここが呼ばれる
     */
    public function getPaidLeaveRemainingAttribute()
    {
        // 1. 付与日数の計算ロジック (既存のメソッドを呼び出し)
        $total = $this->getTotalPaidLeaveDays();
        
        // 2. 消化日数の計算ロジック (先ほど修正した勤怠実績ベースのメソッド)
        $used = $this->getUsedPaidLeaveDays();
        
        $remaining = $total - $used;

        // マイナスにならないようにし、数値として返す
        return max(0, (float)$remaining);
    }

    /**
     * 有給休暇の総付与日数を計算・取得する
     */
    public function calculateTotalGrantedDays()
    {
        // 1. すでにDBに値が入っている場合はそれを返す
        if ($this->detail && $this->detail->granted_days > 0) {
            return (float) $this->detail->granted_days;
        }

        // 2. DBが0または未設定の場合は、法律ロジックの結果を返す
        return (float) $this->getTotalPaidLeaveDays();
    }
}
