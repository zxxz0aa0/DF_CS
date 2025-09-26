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
        Schema::create('account_sub_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_category_id')->comment('總類外鍵');
            $table->string('sub_category_code', 10)->comment('子分類編號（如：01, 02, 03）');
            $table->string('sub_category_name', 100)->comment('子分類名稱');
            $table->text('description')->nullable()->comment('子分類描述');
            $table->integer('sort_order')->default(0)->comment('排序順序');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->unsignedBigInteger('created_by')->nullable()->comment('建立者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('最後更新者');
            $table->timestamps();

            // 索引
            $table->index('main_category_id');
            $table->index('sort_order');
            $table->index('is_active');

            // 複合唯一索引：同一總類內的子分類編號不可重複
            $table->unique(['main_category_id', 'sub_category_code']);

            // 外鍵約束
            $table->foreign('main_category_id')->references('id')->on('account_main_categories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_sub_categories');
    }
};