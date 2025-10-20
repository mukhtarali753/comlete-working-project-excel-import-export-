<?php

namespace App\Helpers\SheetV2;

use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SheetVersionRestoreHelperV2
{
    public static function handle($sheetId, $versionNumber)
    {
        return DB::transaction(function () use ($sheetId, $versionNumber) {
            $currentSheet = Sheet::findOrFail($sheetId);
            $fileId = $currentSheet->file_id;

            Log::info('Restoring sheet version', [
                'sheet_id' => $sheetId,
                'sheet_name' => $currentSheet->name,
                'file_id' => $fileId,
                'target_version' => $versionNumber
            ]);

            $baseName = self::getBaseName($currentSheet->name);

            $availableVersions = self::lineageQuery($fileId, $baseName)
                ->select('id', 'name', 'version')
                ->get()
                ->toArray();

            Log::info('Available versions for sheet', [
                'base_name' => $baseName,
                'available_versions' => $availableVersions
            ]);

            $targetSheet = self::lineageQuery($fileId, $baseName)
                ->where('version', (int)$versionNumber)
                ->first();

            if (!$targetSheet) {
                $targetSheet = Sheet::where('file_id', $fileId)
                    ->where('version', (int)$versionNumber)
                    ->first();

                if (!$targetSheet) {
                    $targetSheet = Sheet::where('file_id', $fileId)
                        ->where(function ($q) use ($baseName, $versionNumber) {
                            $q->where('name', $baseName)
                                ->orWhere('name', 'LIKE', $baseName . '_v' . $versionNumber)
                                ->orWhere('name', 'LIKE', $baseName . '_v%');
                        })
                        ->first();

                    if (!$targetSheet) {
                        throw new \Exception('Sheet version ' . $versionNumber . ' not found for sheet: ' . $currentSheet->name . ' (base: ' . $baseName . '). Available versions: ' . json_encode($availableVersions));
                    }
                }
            }

            Log::info('Found target sheet for restore', [
                'target_sheet_id' => $targetSheet->id,
                'target_sheet_name' => $targetSheet->name,
                'target_sheet_version' => $targetSheet->version
            ]);

            $targetSheetData = $targetSheet->data;
            $targetSheetConfig = $targetSheet->config;
            $targetSheetCelldata = $targetSheet->celldata;
            $targetRows = $targetSheet->rows;

            Log::info('Target sheet data types', [
                'data_type' => gettype($targetSheetData),
                'config_type' => gettype($targetSheetConfig),
                'celldata_type' => gettype($targetSheetCelldata),
                'rows_count' => $targetRows->count()
            ]);

            $currentSheet->update([
                'data' => $targetSheetData,
                'config' => $targetSheetConfig,
                'celldata' => $targetSheetCelldata,
                'version' => $versionNumber,
                'updated_at' => now()
            ]);

            SheetRow::where('sheet_id', $currentSheet->id)->delete();

            if ($targetRows->isNotEmpty()) {
                $rowData = [];
                foreach ($targetRows as $index => $targetRow) {
                    if ($index < 3) {
                        Log::info('Target row data types', [
                            'row_index' => $index,
                            'sheet_data_type' => gettype($targetRow->sheet_data),
                            'cell_formatting_type' => gettype($targetRow->cell_formatting),
                            'version' => $targetRow->version
                        ]);
                    }

                    $sheetData = is_array($targetRow->sheet_data)
                        ? json_encode($targetRow->sheet_data)
                        : ($targetRow->sheet_data ?? null);

                    $cellFormatting = is_array($targetRow->cell_formatting)
                        ? json_encode($targetRow->cell_formatting)
                        : ($targetRow->cell_formatting ?? null);

                    if (is_string($sheetData) || is_null($sheetData)) {
                        $rowData[] = [
                            'sheet_id' => $currentSheet->id,
                            'sheet_data' => $sheetData,
                            'cell_formatting' => $cellFormatting,
                            'version' => $targetRow->version ?? 1,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    } else {
                        Log::warning('Skipping invalid row data', [
                            'row_index' => $index,
                            'sheet_data_type' => gettype($sheetData),
                            'sheet_data_value' => $sheetData
                        ]);
                    }
                }

                if (!empty($rowData)) {
                    SheetRow::insert($rowData);
                    Log::info('Successfully inserted restored rows', [
                        'sheet_id' => $currentSheet->id,
                        'rows_count' => count($rowData)
                    ]);
                }
            }

            // After restoring content, delete the target sheet and its rows if it's a different sheet
            if ($targetSheet->id !== $currentSheet->id) {
                SheetRow::where('sheet_id', $targetSheet->id)->delete();
                $targetSheet->delete();
            }

            Log::info('Sheet restored successfully', [
                'sheet_id' => $sheetId,
                'restored_version' => $versionNumber,
                'rows_restored' => count($rowData ?? [])
            ]);

            return response()->json([
                'message' => 'Sheet restored to version ' . $versionNumber . ' successfully',
                'sheet_id' => $currentSheet->id,
                'sheet_name' => $currentSheet->name,
                'file_id' => $fileId,
                'restored_version' => $versionNumber,
                'rows_restored' => count($rowData ?? [])
            ], 200);
        });
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













