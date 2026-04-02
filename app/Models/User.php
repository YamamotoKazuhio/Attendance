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
     * 有給使用日数を計算（半休対応・承認済みデータのみ）
     * ※attendancesテーブル内のレコードを基に集計します
     */
    public function getUsedPaidLeaveDays()
    {
        // leave_applications テーブルから、このユーザーの「承認済み(1)」の有給関連申請を取得
        // ※status: 0=申請中, 1=承認済, 2=却下 と想定
        return DB::table('leave_applications')
            ->where('user_id', $this->id)
            ->where('status', 1) // 承認済みのみをカウント対象にする
            ->whereIn('type', ['有給', '午前半休', '午後半休'])
            ->sum('days_count'); // 保存されている 1.0 や 0.5 を直接合計する
    }

    /**
     * 有給残日数 (アクセサ)
     * $user->paid_leave_remaining で取得可能
     */
    public function getPaidLeaveRemainingAttribute()
    {
        $total = $this->getTotalPaidLeaveDays();
        $used = $this->getUsedPaidLeaveDays();

        $remaining = $total - $used;

        return max(0, $remaining);
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
