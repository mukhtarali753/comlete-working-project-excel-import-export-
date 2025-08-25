<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sheet_rows', function (Blueprint $table) {
            if (!Schema::hasColumn('sheet_rows', 'cell_formatting')) {
                $table->longText('cell_formatting')->nullable()->after('sheet_data');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sheet_rows', function (Blueprint $table) {
            if (Schema::hasColumn('sheet_rows', 'cell_formatting')) {
                $table->dropColumn('cell_formatting');
            }
        });
    }
};
