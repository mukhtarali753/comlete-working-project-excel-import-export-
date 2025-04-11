<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThemeBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('theme_blocks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('theme_id');
        $table->string('title');
        $table->text('description')->nullable();
        $table->timestamps();

        // $table->foreign('theme_id')->references('id')->on('themes')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('theme_blocks');
    }
}
