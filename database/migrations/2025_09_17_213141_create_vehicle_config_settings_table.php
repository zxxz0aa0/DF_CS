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
        Schema::create('vehicle_config_settings', function (Blueprint $table) {
            $table->id();

            // 關聯公司類別
            $table->unsignedBigInteger('company_category_id')->comment('公司類別ID');

            // 配置選項
            $table->boolean('enable_replacement_license')->default(false)->comment('是否啟用替補車號選擇');
            $table->boolean('enable_owner_dropdown')->default(false)->comment('是否啟用車主下拉選單');

            // 審計資訊
            $table->unsignedBigInteger('created_by')->nullable()->comment('建立者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();

            // 唯一約束 - 每個公司類別只能有一個配置
            $table->unique('company_category_id', 'unique_category');

            // 外鍵約束
            $table->foreign('company_category_id')->references('id')->on('company_categories')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_config_settings');
    }
};