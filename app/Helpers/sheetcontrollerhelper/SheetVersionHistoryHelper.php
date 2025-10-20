<?php

namespace App\Helpers\SheetControllerHelper;

use App\Models\Sheet;
use App\Models\SheetRowVersion;
use Illuminate\Support\Facades\Log;

class SheetVersionHistoryHelper
{
    public static function handle($sheetId)
    {
        try {
            $sheet = Sheet::findOrFail($sheetId);

            Log::info('Getting version history for sheet ' . $sheetId, [
                'sheet_name' => $sheet->name,
                'file_id' => $sheet->file_id,
                'current_version' => $sheet->version,
                'is_current' => $sheet->is_current
            ]);

            $baseName = self::getBaseName($sheet->name);
            $sheetLineage = self::lineageQuery($sheet->file_id, $baseName)
                ->orderBy('version', 'desc')
                ->get(['id', 'name', 'version', 'is_current', 'updated_at']);

            $sheetHistory = $sheetLineage->map(function ($s) {
                $displayVersion = is_null($s->version) || (int)$s->version < 1 ? 1 : (int)$s->version;

                return [
                    'sheet_id' => $s->id,
                    'version' => $displayVersion,
                    'is_current' => (bool)$s->is_current,
                    'created_at' => $s->updated_at ? $s->updated_at->toIso8601String() : null,
                ];
            })->toArray();

            $versionHistoryEntries = SheetRowVersion::where('sheet_id', $sheetId)
                ->orderBy('sheet_row_id')
                ->orderBy('version_number', 'desc')
                ->get();

            $versionHistory = [];
            $groupedVersions = $versionHistoryEntries->groupBy('sheet_row_id');

            foreach ($groupedVersions as $rowId => $versions) {
                $currentRow = $sheet->rows()->where('id', $rowId)->first();
                $currentVersion = $currentRow ? $currentRow->version : 1;

                $formattedVersions = $versions->map(function ($version) {
                    $sheetData = $version->sheet_data;
                    if (is_string($sheetData)) {
                        $decodedData = json_decode($sheetData, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $sheetData = $decodedData;
                        }
                    }

                    return [
                        'version_number' => $version->version_number,
                        'sheet_data' => $sheetData,
                        'cell_formatting' => $version->cell_formatting,
                        'created_at' => $version->created_at ? $version->created_at->toIso8601String() : null
                    ];
                });

                $versionHistory[] = [
                    'row_id' => $rowId,
                    'current_version' => $currentVersion,
                    'versions' => $formattedVersions
                ];
            }

            if (!empty($versionHistory)) {
                Log::info('Version history for sheet ' . $sheetId, [
                    'total_rows' => count($versionHistory),
                    'first_row' => $versionHistory[0] ?? null,
                    'first_version_data' => isset($versionHistory[0]['versions'][0]) ? $versionHistory[0]['versions'][0] : null
                ]);
            }

            return response()->json([
                'sheet_name' => $sheet->name,
                'sheet_history' => $sheetHistory,
                'version_history' => $versionHistory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get sheet version history: ' . $e->getMessage()
            ], 500);
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


























