<?php

namespace App\Helpers\SheetControllerHelper;

use App\Models\SheetRow;
use App\Models\SheetRowVersion;
use Illuminate\Support\Facades\DB;

class RestoreRowVersionHelper
{
    public static function handle($rowId, $versionNumber)
    {
        try {
            DB::beginTransaction();

            $row = SheetRow::findOrFail($rowId);
            $version = SheetRowVersion::where('sheet_row_id', $rowId)
                ->where('version_number', $versionNumber)
                ->first();

            if (!$version) {
                throw new \Exception('Version not found');
            }

            SheetRowVersion::create([
                'sheet_row_id' => $row->id,
                'sheet_id' => $row->sheet_id,
                'sheet_data' => $row->sheet_data,
                'cell_formatting' => $row->cell_formatting,
                'version_number' => $row->version,
                'created_at' => $row->created_at
            ]);

            $row->update([
                'sheet_data' => $version->sheet_data,
                'cell_formatting' => $version->cell_formatting,
                'version' => $row->version + 1
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Version restored successfully',
                'new_version' => $row->version
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to restore version: ' . $e->getMessage()
            ], 500);
        }
    }
}










































