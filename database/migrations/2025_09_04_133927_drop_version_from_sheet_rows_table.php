<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropVersionFromSheetRowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sheet_rows', function (Blueprint $table) {
            if (Schema::hasColumn('sheet_rows', 'version')) {
                $table->dropColumn('version');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sheet_rows', function (Blueprint $table) {
            if (!Schema::hasColumn('sheet_rows', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('cell_formatting');
            }
        });
    }
}
