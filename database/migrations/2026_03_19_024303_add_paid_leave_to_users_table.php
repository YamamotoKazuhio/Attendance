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
        Schema::table('users', function (Blueprint $table) {
            // 有給残数（例: 18.5日のような半休対応のためdecimal型）
            $table->decimal('paid_leave_remaining', 4, 1)->default(10.0)->after('department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 有給残数カラムの削除
            $table->dropColumn('paid_leave_remaining');
        });
    }
};
