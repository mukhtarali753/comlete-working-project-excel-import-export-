<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetRowVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheet_row_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sheet_row_id');
            $table->unsignedBigInteger('sheet_id');
            $table->json('sheet_data');
            $table->json('cell_formatting')->nullable();
            $table->integer('version_number');
            $table->timestamp('created_at');
            
            $table->foreign('sheet_row_id')->references('id')->on('sheet_rows')->onDelete('cascade');
            $table->foreign('sheet_id')->references('id')->on('sheets')->onDelete('cascade');
            
            $table->index(['sheet_row_id', 'version_number']);
            $table->index(['sheet_id', 'version_number']);
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
