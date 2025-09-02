<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sheet_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('sheet_id')->nullable()->constrained('sheets')->nullOnDelete();
            $table->unsignedBigInteger('version_number');
            $table->boolean('is_current')->default(false)->index();
            $table->json('data');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['file_id', 'sheet_id', 'version_number']);
            $table->index(['file_id', 'sheet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sheet_histories');
    }
};


