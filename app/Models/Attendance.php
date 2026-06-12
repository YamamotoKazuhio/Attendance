<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * 一括書き込み（Mass Assignment）を許可する属性
     * * データベースの attendances テーブルにあるカラム名をすべて列挙します。
     * これにより AttendanceController からの updateOrCreate 実行時に
     * 500エラー（MassAssignmentException）が発生するのを防ぎます。
     */
    protected $fillable = [
        'user_id',
        'work_date',
        'status',
        'attendance_type',
        'workplace',
        'shift',
        'start_time',
        'end_time',
        'break_time',
        'overtime',
        'work_time',
        'is_locked',
    ];

    /**
     * リレーション定義：この勤怠データは一人のユーザーに属します
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}