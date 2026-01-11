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
        Schema::table('accounting_records', function (Blueprint $table) {
            // 在 vehicle_license_number 欄位之後新增車隊編號欄位
            $table->string('vehicle_fleet_number', 255)->nullable()->after('vehicle_license_number');

            // 新增索引以提升查詢效能
            $table->index('vehicle_fleet_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_records', function (Blueprint $table) {
            // 回滾時移除索引與欄位
            $table->dropIndex(['vehicle_fleet_number']);
            $table->dropColumn('vehicle_fleet_number');
        });
    }
};
