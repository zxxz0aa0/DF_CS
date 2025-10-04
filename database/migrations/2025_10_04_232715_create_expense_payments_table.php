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
        Schema::create('expense_payments', function (Blueprint $table) {
            $table->id();
            $table->date('record_date')->comment('紀錄日期');
            $table->time('record_time')->comment('紀錄時間');

            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete()->comment('隊員ID');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete()->comment('車輛ID');
            $table->string('member_code', 50)->nullable()->comment('隊員編號');
            $table->string('member_name', 100)->comment('隊員姓名');
            $table->string('vehicle_license_number', 20)->nullable()->comment('車牌號碼');
            $table->string('item_name', 120)->comment('款項名稱');

            $table->decimal('gross_amount', 12, 2)->comment('支付金額');
            $table->decimal('deduction', 12, 2)->default(0)->comment('應扣款');
            $table->decimal('net_amount', 12, 2)->comment('實付金額');

            $table->date('payment_date')->nullable()->comment('支付日期');
            $table->string('payment_method', 30)->nullable()->comment('支付方式');
            $table->enum('status', ['pending', 'paid'])->default('pending')->comment('款項狀態');
            $table->text('note')->nullable()->comment('備註');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('建立者ID');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('最後更新者ID');
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete()->comment('支付經手人ID');
            $table->timestamp('paid_at')->nullable()->comment('支付時間');

            $table->timestamps();
            $table->softDeletes();

            $table->index('record_date');
            $table->index('status');
            $table->index('driver_id');
            $table->index('member_code');
            $table->index('vehicle_license_number');

            $table->unique(['driver_id', 'record_date', 'record_time', 'deleted_at'], 'expense_payments_driver_datetime_unique');
            $table->unique(['member_code', 'record_date', 'record_time', 'deleted_at'], 'expense_payments_code_datetime_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_payments');
    }
};
