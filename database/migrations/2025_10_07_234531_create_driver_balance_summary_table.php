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
        Schema::create('driver_balance_summary', function (Blueprint $table) {
            $table->unsignedBigInteger('driver_id')->primary();
            $table->string('driver_name', 100);
            $table->string('driver_id_number', 20);
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->date('last_transaction_date')->nullable();
            $table->integer('transaction_count')->default(0);
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('driver_id')
                  ->references('id')
                  ->on('drivers')
                  ->onDelete('cascade');

            $table->index('balance');
            $table->index('driver_id_number');
            $table->index('last_transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_balance_summary');
    }
};
