<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sheets', function (Blueprint $table) {
            $table->longText('data')->nullable()->after('order');
            $table->longText('config')->nullable()->after('data');
        });
    }

    public function down(): void
    {
        Schema::table('sheets', function (Blueprint $table) {
            $table->dropColumn(['data', 'config']);
        });
    }
};


