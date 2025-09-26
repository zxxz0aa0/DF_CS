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
        Schema::create('account_main_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 10)->unique()->comment('總類編號（如：1, 2, 3）');
            $table->string('category_name', 100)->comment('總類名稱');
            $table->text('description')->nullable()->comment('總類描述');
            $table->integer('sort_order')->default(0)->comment('排序順序');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->unsignedBigInteger('created_by')->nullable()->comment('建立者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('最後更新者');
            $table->timestamps();

            // 索引
            $table->index('sort_order');
            $table->index('is_active');

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
        Schema::dropIfExists('account_main_categories');
    }
};