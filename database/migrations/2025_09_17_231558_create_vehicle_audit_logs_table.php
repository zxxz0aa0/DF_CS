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
        Schema::create('vehicle_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action', 50); // create, update, delete, deregister, reactivate, import
            $table->string('description')->nullable(); // 操作描述
            $table->json('old_values')->nullable(); // 變更前的值
            $table->json('new_values')->nullable(); // 變更後的值
            $table->json('changes')->nullable(); // 變更的欄位
            $table->string('ip_address', 45)->nullable(); // IP地址
            $table->string('user_agent')->nullable(); // 瀏覽器資訊
            $table->timestamp('created_at');

            // 索引
            $table->index(['vehicle_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_audit_logs');
    }
};
