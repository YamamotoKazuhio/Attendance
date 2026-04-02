<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('target_date');           // 対象日
            $table->string('type');                // 有給, 半休, 特別休暇, 残業申請など
            $table->decimal('days_count', 2, 1);   // 消費日数 (1.0 や 0.5)
            $table->string('reason')->nullable();  // 申請理由
            $table->integer('status')->default(0); // 0:申請中, 1:承認, 2:却下
            $table->foreignId('approved_by')->nullable()->constrained('users'); // 承認した管理者
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
