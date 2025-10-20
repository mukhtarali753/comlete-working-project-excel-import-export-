<?php

namespace App\Helpers\SheetControllerHelper;

use App\Models\Sheet;

class UpdateSheetHelper
{
    /**
     * Update an existing sheet with versioning
     */
    public static function updateExistingSheet(
        Sheet $existingSheet,
        array $sheetData,
        bool $enableVersionHistory,
        callable $getBaseName,
        callable $createVersionHistoryForSheet,
        callable $getNextVersion,
        callable $generateUniqueVersionedName
    ): Sheet {
        // Base name of the sheet
        $baseName = $getBaseName($existingSheet->name);

        // Mark current sheet as not current
        $existingSheet->update(['is_current' => 0]);

        // Create version history if enabled
        if ($enableVersionHistory) {
            $createVersionHistoryForSheet($existingSheet);
        }

        // Get next unique version
        $nextVersion = $getNextVersion($existingSheet->file_id, $baseName);

        // Generate display name
        $displayName = $generateUniqueVersionedName($existingSheet->file_id, $baseName, $nextVersion);

        // Create the new sheet version
        return Sheet::create([
            'file_id'   => $existingSheet->file_id,
            'name'      => $displayName,
            'order'     => $existingSheet->order,
            'data'      => $sheetData['data'],
            'config'    => $sheetData['config'] ?? $existingSheet->config,
            'celldata'  => $sheetData['celldata'] ?? $existingSheet->celldata,
            'version'   => $nextVersion,
            'is_current'=> 1,
        ]);
    }
}
