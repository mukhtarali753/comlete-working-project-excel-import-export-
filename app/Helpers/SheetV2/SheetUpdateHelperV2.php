<?php

namespace App\Helpers\SheetV2;

use App\Models\Sheet;

class SheetUpdateHelperV2
{
    
    public static function updateExistingSheet(
        Sheet $existingSheet,
        array $sheetData,
        bool $enableVersionHistory,
        callable $getBaseName,
        callable $createVersionHistoryForSheet,
        callable $getNextVersion,
        callable $generateUniqueVersionedName
    ): Sheet {
       
        $baseName = $getBaseName($existingSheet->name);

       
        $existingSheet->update(['is_current' => 0]);

      
        if ($enableVersionHistory) {
            $createVersionHistoryForSheet($existingSheet);
        }

       
        $nextVersion = $getNextVersion($existingSheet->file_id, $baseName);

        
        $displayName = $generateUniqueVersionedName($existingSheet->file_id, $baseName, $nextVersion);

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















