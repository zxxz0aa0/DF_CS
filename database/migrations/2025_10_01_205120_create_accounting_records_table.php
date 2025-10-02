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
        Schema::create('accounting_records', function (Blueprint $table) {
            $table->id();

            // 外鍵關聯
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignId('account_detail_id')->constrained('account_details')->restrictOnDelete();

            // 交易資訊
            $table->date('transaction_date');

            // 冗余欄位（歷史記錄保存）
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_id_number', 20)->nullable();
            $table->string('vehicle_license_number', 20)->nullable();

            // 金額
            $table->decimal('debit_amount', 15, 2)->nullable();
            $table->decimal('credit_amount', 15, 2)->nullable();

            // 備註
            $table->text('note')->nullable();

            // 建立者與更新者
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index('transaction_date');
            $table->index(['driver_id', 'transaction_date']);
            $table->index(['vehicle_id', 'transaction_date']);
            $table->index('driver_name');
            $table->index('driver_id_number');
            $table->index('vehicle_license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_records');
    }
};
