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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            
            // 外鍵關聯
            $table->foreignId('company_category_id')->nullable()->constrained('company_categories')->onDelete('set null');
            $table->unsignedBigInteger('recurring_cost_id')->nullable()->comment('行費ID (預留欄位)');
            
            // 基本資料
            $table->string('name', 100)->comment('姓名');
            $table->string('id_number', 10)->unique()->comment('身分證字號');
            $table->date('birthday')->nullable()->comment('生日');
            $table->text('contact_address')->nullable()->comment('通訊地址');
            $table->text('residence_address')->nullable()->comment('戶籍地址');
            
            // 聯絡資訊
            $table->string('home_phone', 20)->nullable()->comment('住家電話');
            $table->string('mobile_phone1', 20)->nullable()->comment('手機號碼1');
            $table->string('mobile_phone2', 20)->nullable()->comment('手機號碼2');
            $table->string('emergency_contact', 100)->nullable()->comment('緊急聯絡人');
            $table->string('emergency_phone', 20)->nullable()->comment('緊急聯絡人電話');
            
            // 日期資訊
            $table->date('registration_date')->comment('入籍日期');
            $table->date('deregistration_date')->nullable()->comment('退籍日期');
            $table->date('fleet_join_date')->nullable()->comment('加入車隊日期');
            $table->date('fleet_leave_date')->nullable()->comment('退出車隊日期');
            $table->date('license_expire_date')->nullable()->comment('駕照到期日');
            $table->date('professional_license_expire_date')->nullable()->comment('執登到期日');
            
            // 狀態
            $table->enum('status', ['open', 'close'])->default('open')->comment('狀態');
            
            $table->timestamps();
            $table->softDeletes();
            
            // 索引
            $table->index('name', 'idx_name');
            $table->index('id_number', 'idx_id_number');
            $table->index('status', 'idx_status');
            $table->index('company_category_id', 'idx_company_category');
            $table->index('license_expire_date', 'idx_license_expire');
            $table->index('professional_license_expire_date', 'idx_professional_expire');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
