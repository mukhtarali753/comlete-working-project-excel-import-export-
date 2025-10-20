<?php

namespace App\Helpers\SheetControllerHelper;

use App\Models\Sheet;

class DebugSheetVersionsHelper
{
    public static function handle($sheetId)
    {
        try {
            $sheet = Sheet::findOrFail($sheetId);
            $fileId = $sheet->file_id;
            $baseName = self::getBaseName($sheet->name);

            $lineageSheets = self::lineageQuery($fileId, $baseName)->get();
            $allFileSheets = Sheet::where('file_id', $fileId)->get();

            return response()->json([
                'current_sheet' => [
                    'id' => $sheet->id,
                    'name' => $sheet->name,
                    'version' => $sheet->version,
                    'is_current' => $sheet->is_current
                ],
                'base_name' => $baseName,
                'lineage_sheets' => $lineageSheets->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'version' => $s->version,
                        'is_current' => $s->is_current
                    ];
                }),
                'all_file_sheets' => $allFileSheets->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'name' => $s->name,
                        'version' => $s->version,
                        'is_current' => $s->is_current
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private static function lineageQuery($fileId, $baseName)
    {
        return Sheet::where('file_id', $fileId)
            ->where(function ($q) use ($baseName) {
                $q->where('name', $baseName)
                    ->orWhere('name', 'LIKE', $baseName . '\\_v%');
            });
    }

    private static function getBaseName($name)
    {
        if (preg_match('/^(.*)_v\\d+$/', $name, $matches)) {
            return $matches[1];
        }
        return $name;
    }
}


























