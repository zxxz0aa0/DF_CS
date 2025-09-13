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
        Schema::create('position_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained()->onDelete('cascade')->comment('職務ID');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade')->comment('權限ID');
            $table->timestamps();

            $table->unique(['position_id', 'permission_id']);
            $table->index('position_id');
            $table->index('permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_permissions');
    }
};
