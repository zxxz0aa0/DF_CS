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
        Schema::create('recurring_cost_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('組合名稱');
            $table->text('description')->nullable()->comment('說明');
            $table->unsignedInteger('total_amount')->default(0)->comment('組合總金額');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->unsignedBigInteger('created_by')->nullable()->comment('建立者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('最後更新者');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('name', 'idx_name');
            $table->index('is_active', 'idx_is_active');
            $table->index('created_by', 'idx_created_by');

            // 外鍵約束
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_cost_templates');
    }
};
