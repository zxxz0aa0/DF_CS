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
        Schema::create('account_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type')->comment('異動表格類型');
            $table->unsignedBigInteger('auditable_id')->comment('異動記錄ID');
            $table->string('event', 50)->comment('操作類型 (created/updated/deleted)');
            $table->json('old_values')->nullable()->comment('異動前的值');
            $table->json('new_values')->nullable()->comment('異動後的值');
            $table->unsignedBigInteger('user_id')->nullable()->comment('操作使用者');
            $table->string('ip_address', 45)->nullable()->comment('操作IP位址');
            $table->text('user_agent')->nullable()->comment('使用者代理');
            $table->string('url', 500)->nullable()->comment('操作URL');
            $table->timestamp('created_at')->nullable();

            // 索引
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('created_at');

            // 外鍵約束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_audit_logs');
    }
};