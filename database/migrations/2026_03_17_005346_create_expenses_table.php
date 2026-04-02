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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 誰の経費か
            $table->date('occured_date');      // 発生日
            $table->string('category');        // 種別（交通費等）
            $table->string('subcategory');     // 詳細（鉄道等）
            $table->string('departure')->nullable();   // 出発地
            $table->string('destination')->nullable(); // 目的地
            $table->string('payee')->nullable();       // 支払先
            $table->integer('amount');         // 金額
            $table->integer('prepaid')->default(0);    // 前払い額
            $table->integer('claim_amount');   // 請求額
            $table->string('receipt');         // 領収書（あり/なし）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
