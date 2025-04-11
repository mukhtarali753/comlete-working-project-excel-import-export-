<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubThemesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable("sub_themes")) {
        Schema::create('sub_themes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('theme_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('subtheme_id')->references('id')->on('sub_themes')->onDelete('cascade');
        
        });
    }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sub_themes');
    }
}
