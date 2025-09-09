<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixExistingVersionHistoryRowIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix existing version history entries that have null sheet_row_id
        // We need to link them to their corresponding rows based on the data content
        
        $versionHistoryEntries = DB::table('sheet_row_versions')
            ->whereNull('sheet_row_id')
            ->get();
        
        foreach ($versionHistoryEntries as $versionEntry) {
            // Find the corresponding row by matching the sheet_data
            $matchingRow = DB::table('sheet_rows')
                ->where('sheet_id', $versionEntry->sheet_id)
                ->where('sheet_data', $versionEntry->sheet_data)
                ->first();
            
            if ($matchingRow) {
                // Update the version history entry with the correct row ID
                DB::table('sheet_row_versions')
                    ->where('id', $versionEntry->id)
                    ->update(['sheet_row_id' => $matchingRow->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Set sheet_row_id back to null for all version history entries
        DB::table('sheet_row_versions')
            ->whereNotNull('sheet_row_id')
            ->update(['sheet_row_id' => null]);
    }
}
