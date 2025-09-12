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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('職務名稱');
            $table->string('code')->unique()->comment('職務代碼');
            $table->text('description')->nullable()->comment('職務描述');
            $table->foreignId('role_id')->constrained()->onDelete('cascade')->comment('所屬角色');
            $table->boolean('is_active')->default(true)->comment('是否啟用');
            $table->integer('sort_order')->default(0)->comment('排序順序');
            $table->timestamps();
            
            $table->index(['role_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
