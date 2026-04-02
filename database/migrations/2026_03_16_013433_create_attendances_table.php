<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ユーザー紐付け
            $table->date('work_date'); // 日付
            $table->string('attendance_type')->nullable(); // 勤怠（出勤・欠勤等）
            $table->string('workplace')->nullable(); // 勤務先
            $table->string('shift')->nullable(); // シフト名
            $table->time('start_time')->nullable(); // 開始時刻
            $table->time('end_time')->nullable(); // 終了時刻
            $table->decimal('break_time', 4, 2)->default(0); // 休憩時間
            $table->decimal('overtime', 4, 2)->default(0); // 残業時間
            $table->decimal('work_time', 4, 2)->default(0); // 稼働時間
            $table->boolean('is_locked')->default(false); // 登録ロック状態
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};