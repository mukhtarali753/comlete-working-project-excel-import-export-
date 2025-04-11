<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubThemeBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_theme_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sub_theme_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('sub_theme_id')->references('id')->on('sub_themes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_theme_blocks');
    }
}
