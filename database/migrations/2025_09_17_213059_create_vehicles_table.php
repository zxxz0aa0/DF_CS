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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();

            // 基本關聯資訊
            $table->unsignedBigInteger('company_category_id')->comment('公司類別ID');
            $table->unsignedBigInteger('company_id')->comment('公司名稱ID');

            // 車輛基本資訊
            $table->string('license_number', 20)->unique()->comment('車牌號碼');
            $table->string('replacement_license', 20)->nullable()->comment('替補車號');
            $table->string('vehicle_type', 50)->nullable()->comment('車輛類型');
            $table->string('owner_name', 100)->comment('車主名稱');
            $table->text('address')->nullable()->comment('地址');

            // 車輛製造資訊
            $table->string('brand', 50)->nullable()->comment('車輛廠牌');
            $table->year('manufacture_year')->nullable()->comment('出廠年');
            $table->tinyInteger('manufacture_month')->nullable()->comment('出廠月');
            $table->string('vehicle_form', 50)->nullable()->comment('車輛形式');
            $table->decimal('engine_displacement', 8, 2)->nullable()->comment('排氣量');
            $table->string('fuel_type', 20)->nullable()->comment('燃料種類');
            $table->string('vehicle_model', 100)->nullable()->comment('車輛款式');
            $table->string('vehicle_style', 100)->nullable()->comment('車輛樣式');
            $table->string('engine_number', 50)->nullable()->comment('引擎號碼');
            $table->string('chassis_number', 50)->nullable()->comment('車身號碼');
            $table->tinyInteger('passenger_capacity')->nullable()->comment('載運人數');
            $table->string('vehicle_color', 30)->nullable()->comment('車輛顏色');

            // 發照資訊
            $table->smallInteger('license_issue_year')->nullable()->comment('發照年(西元)');
            $table->tinyInteger('license_issue_month')->nullable()->comment('發照月');
            $table->tinyInteger('license_issue_day')->nullable()->comment('發照日');

            // 檢驗資訊
            $table->smallInteger('inspection_year')->nullable()->comment('檢驗年(西元)');
            $table->tinyInteger('inspection_month')->nullable()->comment('檢驗月');
            $table->tinyInteger('inspection_day')->nullable()->comment('檢驗日');

            // 入籍資訊 (預設當前日期)
            $table->smallInteger('registration_year')->nullable()->comment('車輛入籍年(西元)');
            $table->tinyInteger('registration_month')->nullable()->comment('車輛入籍月');
            $table->tinyInteger('registration_day')->nullable()->comment('車輛入籍日');

            // 退籍資訊
            $table->smallInteger('deregistration_year')->nullable()->comment('車輛退籍年(西元)');
            $table->tinyInteger('deregistration_month')->nullable()->comment('車輛退籍月');
            $table->tinyInteger('deregistration_day')->nullable()->comment('車輛退籍日');

            // 其他資訊
            $table->string('property_type', 50)->nullable()->comment('產權類別');
            $table->text('notes')->nullable()->comment('備註');
            $table->enum('vehicle_status', ['active', 'inactive'])->default('active')->comment('車輛狀態(在籍/退籍)');

            // 審計資訊
            $table->unsignedBigInteger('created_by')->nullable()->comment('建立者');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('更新者');
            $table->timestamps();
            $table->softDeletes()->comment('軟刪除時間');

            // 索引
            $table->index('license_number', 'idx_license_number');
            $table->index('company_category_id', 'idx_company_category');
            $table->index('company_id', 'idx_company');
            $table->index('vehicle_status', 'idx_vehicle_status');
            $table->index(['inspection_year', 'inspection_month', 'inspection_day'], 'idx_inspection_date');
            $table->index(['registration_year', 'registration_month', 'registration_day'], 'idx_registration_date');
            $table->index(['deregistration_year', 'deregistration_month', 'deregistration_day'], 'idx_deregistration_date');

            // 外鍵約束
            $table->foreign('company_category_id')->references('id')->on('company_categories')->onDelete('restrict');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};