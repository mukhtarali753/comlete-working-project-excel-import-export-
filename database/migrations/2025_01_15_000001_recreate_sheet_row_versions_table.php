<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RecreateSheetRowVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop the existing table
        Schema::dropIfExists('sheet_row_versions');
        
        // Recreate with the correct structure
        Schema::create('sheet_row_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sheet_row_id')->nullable(); // Made nullable
            $table->unsignedBigInteger('sheet_id');
            $table->json('sheet_data');
            $table->json('cell_formatting')->nullable();
            $table->integer('version_number');
            $table->timestamp('created_at');
            
            // Only add foreign key for sheet_id, not sheet_row_id
            $table->foreign('sheet_id')->references('id')->on('sheets')->onDelete('cascade');
            
            // Indexes
            $table->index(['sheet_id', 'version_number']);
            $table->index(['sheet_row_id']); // Separate index for nullable column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sheet_row_versions');
    }
}
