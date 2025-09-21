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
        Schema::table('vehicles', function (Blueprint $table) {
            // 車隊相關欄位 - 添加在notes欄位後面
            $table->string('fleet_name', 100)->nullable()->comment('車隊名稱')->after('notes');
            $table->string('fleet_category', 50)->nullable()->comment('車隊類別')->after('fleet_name');
            $table->string('fleet_number', 20)->nullable()->comment('車隊編號')->after('fleet_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // 移除車隊相關欄位
            $table->dropColumn(['fleet_number', 'fleet_category', 'fleet_name']);
        });
    }
};
