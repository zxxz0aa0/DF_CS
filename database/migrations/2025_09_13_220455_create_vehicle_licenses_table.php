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
        Schema::create('vehicle_licenses', function (Blueprint $table) {
            $table->id();
            
            // 基本資訊
            $table->unsignedBigInteger('company_id');
            $table->string('county', 50)->nullable();
            $table->string('license_number', 20)->nullable()->index();
            $table->string('holder_name', 100)->nullable();
            $table->year('license_year')->nullable();
            $table->tinyInteger('license_month')->nullable()->comment('1-12');
            
            // 前車牌資訊
            $table->string('previous_license_number', 20)->nullable();
            $table->string('previous_holder_name', 100)->nullable();
            $table->year('previous_license_year')->nullable();
            $table->tinyInteger('previous_license_month')->nullable()->comment('1-12');
            
            // 其他資訊
            $table->text('notes')->nullable();
            $table->date('replacement_date')->nullable();
            $table->date('revocation_date')->nullable();
            $table->enum('status', ['active', 'revoked', 'transferred'])->default('active');
            
            // 審計資訊
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // 外鍵約束
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // 索引
            $table->index(['company_id', 'status']);
            $table->index('status');
            $table->index('revocation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_licenses');
    }
};
