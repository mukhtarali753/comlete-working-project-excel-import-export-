<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheetRowsTable extends Migration
{
    public function up()
    {
        Schema::create('sheet_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sheet_id')->constrained()->onDelete('cascade');
            $table->json('sheet_data');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sheet_rows');
    }
}