<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Backfill null versions first to avoid unique index conflicts
        DB::table('sheets')->whereNull('version')->update(['version' => 1]);

        Schema::table('sheets', function (Blueprint $table) {
            // Drop the old unique index on (file_id, name) if it exists
            try {
                $table->dropUnique('sheets_file_id_name_unique');
            } catch (\Throwable $e) {
                // Index might already be dropped; ignore
            }

            // Add new unique index including version to allow versioned duplicates
            $table->unique(['file_id', 'name', 'version'], 'sheets_file_id_name_version_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sheets', function (Blueprint $table) {
            // Drop the unique index that includes version
            try {
                $table->dropUnique('sheets_file_id_name_version_unique');
            } catch (\Throwable $e) {
                // Ignore if it does not exist
            }

            // Restore the original unique index on (file_id, name)
            $table->unique(['file_id', 'name'], 'sheets_file_id_name_unique');
        });
    }
};


