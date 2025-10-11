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
        Schema::create('recurring_cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('recurring_cost_templates')->onDelete('cascade')->comment('組合ID');
            $table->foreignId('account_detail_id')->constrained('account_details')->onDelete('cascade')->comment('會計科目ID');
            $table->unsignedInteger('amount')->comment('金額');
            $table->string('note', 255)->nullable()->comment('備註');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            // 索引
            $table->index('template_id', 'idx_template_id');
            $table->index('account_detail_id', 'idx_account_detail_id');
            $table->index('sort_order', 'idx_sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_cost_items');
    }
};
