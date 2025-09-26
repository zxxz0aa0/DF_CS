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
        Schema::create('account_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_category_id')->comment('總類外鍵');
            $table->unsignedBigInteger('sub_category_id')->comment('子分類外鍵');
            $table->string('account_code', 20)->unique()->comment('完整科目編號');
            $table->string('account_name', 150)->comment('科目名稱');
            $table->string('account_name_en', 150)->nullable()->comment('英文科目名稱');
            $table->text('description')->nullable()->comment('科目說明');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->comment('科目性質');
            $table->enum('debit_credit', ['debit', 'credit'])->comment('借貸性質');
            $table->boolean('is_summary')->default(false)->comment('是否為統馭科目');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('上級科目');
            $table->integer('level')->default(1)->comment('科目層級');
            $table->integer('sort_order')->default(0)->comment('排序順序');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->text('notes')->nullable()->comment('備註');
            $table->unsignedBigInteger('created_by')->nullable()->comment('建立者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('最後更新者');
            $table->timestamps();

            // 索引
            $table->index('main_category_id');
            $table->index('sub_category_id');
            $table->index('parent_id');
            $table->index('account_type');
            $table->index('is_active');
            $table->index('level');
            $table->index('sort_order');

            // 外鍵約束
            $table->foreign('main_category_id')->references('id')->on('account_main_categories')->onDelete('cascade');
            $table->foreign('sub_category_id')->references('id')->on('account_sub_categories')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('account_details')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_details');
    }
};