<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // department がない場合のみ追加
            if (!Schema::hasColumn('users', 'department')) {
                $table->string('department')->nullable()->after('name');
            }
            // daily_commute_fee がない場合のみ追加
            if (!Schema::hasColumn('users', 'daily_commute_fee')) {
                $table->integer('daily_commute_fee')->default(0)->after('department');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['department', 'daily_commute_fee']);
        });
    }
};
