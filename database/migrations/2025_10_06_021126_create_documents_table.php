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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('cascade');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('cascade');
            $table->enum('document_category', ['identity', 'insurance', 'vehicle']);
            $table->string('document_name', 255);
            $table->string('document_number', 100)->nullable();
            $table->decimal('insurance_level', 10, 2)->nullable();
            $table->decimal('insurance_fee', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['valid', 'expiring_soon', 'expired'])->default('valid');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // 索引
            $table->index(['document_category']);
            $table->index(['status']);
            $table->index(['expiry_date']);
            $table->index(['driver_id', 'document_category']);
            $table->index(['vehicle_id', 'document_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
