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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // 外部キー
            $table->string('employee_code')->nullable(); // 社員番号
            $table->string('name_kanji')->nullable();    // 漢字氏名
            $table->date('joined_at')->nullable();       // 入社日
            
            // 後から追加されたカラム群を統合
            $table->decimal('granted_days', 4, 1)->default(0.0); // 付与日数
            $table->string('department')->nullable();            // 所属（部署）
            $table->integer('commute_fee')->default(0);          // 通勤費
            $table->string('role')->nullable();                  // ロール（Class_B, K, NA 等）

            // その他詳細情報
            $table->string('address_city')->nullable();          // 市区町村等
            $table->string('phone_number')->nullable();          // 電話番号
            $table->string('emergency_contact')->nullable();     // 緊急連絡先
            $table->string('emergency_relationship')->nullable();// 緊急連絡先との関係
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};