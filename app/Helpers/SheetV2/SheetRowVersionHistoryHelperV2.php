<?php

namespace App\Helpers\SheetV2;

use App\Models\SheetRow;

class SheetRowVersionHistoryHelperV2
{
    public static function handle($rowId)
    {
        try {
            $row = SheetRow::with('versions')->findOrFail($rowId);

            $versions = $row->versions()->orderBy('version_number', 'desc')->get()->map(function ($version) {
                return [
                    'version_number' => $version->version_number,
                    'sheet_data' => $version->sheet_data,
                    'cell_formatting' => $version->cell_formatting,
                    'created_at' => $version->created_at->format('Y-m-d H:i:s')
                ];
            });

            return response()->json([
                'current_version' => $row->version,
                'versions' => $versions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get version history: ' . $e->getMessage()
            ], 500);
        }
    }
}















