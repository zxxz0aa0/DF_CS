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
        Schema::table('users', function (Blueprint $table) {
            // 1. 帳號 (改用帳號登入)
            $table->string('username')->unique()->after('name');

            // 2. 身分證字號 (加密、必填、唯一)
            $table->text('id_number')->after('username');
            $table->unique(['id_number']);

            // 3. 出生日期
            $table->date('birth_date')->nullable()->after('id_number');

            // 4. 家用電話
            $table->string('home_phone', 20)->nullable()->after('birth_date');

            // 5. 手機號碼
            $table->string('mobile_phone', 20)->nullable()->after('home_phone');

            // 6. 緊急聯絡人
            $table->string('emergency_contact', 100)->nullable()->after('mobile_phone');

            // 7. 聯絡人電話
            $table->string('emergency_phone', 20)->nullable()->after('emergency_contact');

            // 8. 部門
            $table->string('department', 100)->nullable()->after('emergency_phone');

            // 9. 職務
            $table->string('position', 100)->nullable()->after('department');

            // 10. 性別
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('position');

            // 11. 通訊地址
            $table->text('address')->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'id_number',
                'birth_date',
                'home_phone',
                'mobile_phone',
                'emergency_contact',
                'emergency_phone',
                'department',
                'position',
                'gender',
                'address',
            ]);
        });
    }
};
