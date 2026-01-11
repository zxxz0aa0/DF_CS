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
            // 新增 created_at 索引以優化日期範圍查詢
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounting_records', function (Blueprint $table) {
            // 移除 created_at 索引
            $table->dropIndex(['created_at']);
        });
    }
};
