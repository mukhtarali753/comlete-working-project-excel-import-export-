<?php

namespace App\Helpers\SheetControllerHelper;

use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Support\Facades\DB;

class DeleteSheetHelper
{
    /**
     * Delete a sheet and create a new version of the file without that sheet
     */
    public static function deleteSheet(
        int $sheetId,
        callable $getBaseName,
        callable $generateUniqueVersionedName
    ): array {
        DB::beginTransaction();

        try {
            $sheet = Sheet::findOrFail($sheetId);
            $fileId = $sheet->file_id;
            $baseToRemove = $getBaseName($sheet->name);

            // Next file version
            $currentVersions = Sheet::where('file_id', $fileId)
                ->where('is_current', 1)
                ->pluck('version')
                ->toArray();

            $maxCurrentVersion = 0;
            foreach ($currentVersions as $v) {
                $maxCurrentVersion = max($maxCurrentVersion, (int)($v ?? 0));
            }
            $nextFileVersion = max(1, $maxCurrentVersion + 1);

            // Current sheets
            $currentSheets = Sheet::where('file_id', $fileId)
                ->where('is_current', 1)
                ->get();

            // Mark all current as not current
            Sheet::where('file_id', $fileId)
                ->where('is_current', 1)
                ->update(['is_current' => 0]);

            // Recreate snapshots excluding the removed base
            foreach ($currentSheets as $cs) {
                $baseName = $getBaseName($cs->name);
                if ($baseName === $baseToRemove) {
                    continue;
                }

                $displayName = $generateUniqueVersionedName($fileId, $baseName, $nextFileVersion);

                $newSheet = Sheet::create([
                    'file_id'   => $fileId,
                    'name'      => $displayName,
                    'order'     => $cs->order,
                    'data'      => $cs->data,
                    'config'    => $cs->config,
                    'celldata'  => $cs->celldata,
                    'version'   => $nextFileVersion,
                    'is_current'=> 1,
                ]);

                // Copy rows
                $sourceRows = SheetRow::where('sheet_id', $cs->id)
                    ->get(['sheet_data', 'cell_formatting']);

                if ($sourceRows->count() > 0) {
                    $insertRows = [];
                    foreach ($sourceRows as $row) {
                        $insertRows[] = [
                            'sheet_id'        => $newSheet->id,
                            'sheet_data'      => is_string($row->sheet_data)
                                ? $row->sheet_data
                                : json_encode($row->sheet_data),
                            'cell_formatting' => $row->cell_formatting
                                ? (is_string($row->cell_formatting)
                                    ? $row->cell_formatting
                                    : json_encode($row->cell_formatting))
                                : null,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }
                    if (!empty($insertRows)) {
                        SheetRow::insert($insertRows);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'file_id' => $fileId,
                'version' => $nextFileVersion,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
