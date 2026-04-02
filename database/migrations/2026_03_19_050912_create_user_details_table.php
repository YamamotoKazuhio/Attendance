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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // usersテーブルと紐付け
            $table->string('employee_code')->nullable();       // 社員番号
            $table->string('name_kanji')->nullable();          // 氏名
            $table->date('joined_at')->nullable();             // 入社年月日
            $table->string('address_city')->nullable();        // 現住所（市町村）
            $table->string('phone_number')->nullable();        // 連絡先
            $table->string('emergency_contact')->nullable();    // 緊急連絡先
            $table->string('emergency_relationship')->nullable(); // 本人との関係
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
