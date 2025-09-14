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
        Schema::create('vehicle_license_audit_logs', function (Blueprint $table) {
            $table->id();
            
            // 關聯資訊
            $table->unsignedBigInteger('vehicle_license_id');
            $table->string('action', 50); // created, updated, deleted, transferred, revoked
            
            // 審計資料
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // 操作者資訊
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('created_at');
            
            // 外鍵約束
            $table->foreign('vehicle_license_id')->references('id')->on('vehicle_licenses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // 索引
            $table->index(['vehicle_license_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_license_audit_logs');
    }
};
