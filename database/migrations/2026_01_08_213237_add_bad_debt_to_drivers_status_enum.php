<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 修改 status 欄位的 enum,新增 bad_debt 選項
        DB::statement("ALTER TABLE drivers MODIFY COLUMN status ENUM('open', 'close', 'bad_debt') NOT NULL DEFAULT 'open' COMMENT '狀態'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回復到原本的 enum 值
        DB::statement("ALTER TABLE drivers MODIFY COLUMN status ENUM('open', 'close') NOT NULL DEFAULT 'open' COMMENT '狀態'");
    }
};
