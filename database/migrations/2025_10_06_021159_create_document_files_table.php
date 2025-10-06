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
        Schema::create('document_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50);
            $table->bigInteger('file_size');
            $table->integer('sort_order')->default(0);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            // 索引
            $table->index(['document_id', 'sort_order']);
            $table->index(['file_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_files');
    }
};
