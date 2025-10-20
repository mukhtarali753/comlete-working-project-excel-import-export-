<?php

namespace App\Helpers\SheetV2;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SheetImportHelperV2
{
    public static function handle(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'file_name' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fileName = $request->input('file_name') ?: $file->getClientOriginalName();

        $path = $file->store('temp');

        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray([], $path);

        if (empty($sheets)) {
            throw new \Exception('No data found in the uploaded file');
        }

       
        Storage::delete($path);

        Log::info("Excel import started", [
            'fileName' => $fileName,
            'totalSheets' => count($sheets),
            'firstSheetRows' => count($sheets[0] ?? []),
            'firstSheetSample' => array_slice($sheets[0] ?? [], 0, 3)
        ]);

        return DB::transaction(function () use ($sheets, $fileName) {
            $fileRecord = File::create([
                'name' => $fileName,
                'user_id' => Auth::id(),
            ]);

            $importedSheets = 0;
            $importedRows = 0;
            $errors = [];

            foreach ($sheets as $sheetIndex => $sheetData) {
                Log::info("Processing sheet", [
                    'sheetIndex' => $sheetIndex,
                    'sheetDataRows' => count($sheetData),
                    'sheetDataSample' => array_slice($sheetData, 0, 2)
                ]);

                $baseName = 'Sheet' . ($sheetIndex + 1);
                $counter = 1;
                $uniqueName = $baseName;

                while (Sheet::where('file_id', $fileRecord->id)->where('name', $uniqueName)->exists()) {
                    $uniqueName = $baseName . ' (' . $counter . ')';
                    $counter++;
                }

                $maxRows = count($sheetData);
                $maxCols = 0;

                foreach ($sheetData as $row) {
                    if (is_array($row)) {
                        $maxCols = max($maxCols, count($row));
                    }
                }

                $maxRows = max($maxRows, 20);
                $maxCols = max($maxCols, 10);

                Log::info("Sheet dimensions calculated", [
                    'maxRows' => $maxRows,
                    'maxCols' => $maxCols,
                    'originalRows' => count($sheetData)
                ]);

                $completeGridData = [];
                for ($rowIndex = 0; $rowIndex < $maxRows; $rowIndex++) {
                    $rowData = [];
                    for ($colIndex = 0; $colIndex < $maxCols; $colIndex++) {
                        $cellValue = '';
                        if (isset($sheetData[$rowIndex]) && is_array($sheetData[$rowIndex]) && isset($sheetData[$rowIndex][$colIndex])) {
                            $cellValue = trim($sheetData[$rowIndex][$colIndex] ?? '');
                        }
                        $rowData[] = ['v' => $cellValue];
                    }
                    $completeGridData[] = $rowData;
                }

                $sheet = Sheet::create([
                    'file_id' => $fileRecord->id,
                    'name' => $uniqueName,
                    'order' => $sheetIndex,
                    'data' => json_encode($completeGridData),
                    'config' => json_encode([
                        'rowlen' => array_fill(0, $maxRows, 30),
                        'columnlen' => array_fill(0, $maxCols, 200),
                    ]),
                    'celldata' => json_encode([]),
                    'version' => 1,
                    'is_current' => 1,
                ]);

                $rowInsertData = [];
                foreach ($sheetData as $rowIndex => $row) {
                    if (!is_array($row)) continue;

                    $cleanRow = [];
                    foreach ($row as $cellIndex => $cell) {
                        $cleanRow[] = trim($cell ?? '');
                    }

                    while (count($cleanRow) < $maxCols) {
                        $cleanRow[] = '';
                    }

                    $rowInsertData[] = [
                        'sheet_id' => $sheet->id,
                        'sheet_data' => json_encode($cleanRow),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($rowInsertData)) {
                    SheetRow::insert($rowInsertData);
                }

                $importedSheets++;
                $sheetRowCount = count($rowInsertData);
                $importedRows += $sheetRowCount;

                Log::info("Imported complete sheet '{$uniqueName}'", [
                    'totalRows' => $maxRows,
                    'totalCols' => $maxCols,
                    'dataRows' => $sheetRowCount,
                    'hasCompleteGrid' => true
                ]);
            }

            $message = "Successfully imported {$importedSheets} sheet(s) with {$importedRows} row(s).";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            Log::info("Import completed", [
                'fileId' => $fileRecord->id,
                'importedSheets' => $importedSheets,
                'importedRows' => $importedRows,
                'finalSheetCount' => Sheet::where('file_id', $fileRecord->id)->count(),
                'finalRowCount' => SheetRow::whereHas('sheet', function ($q) use ($fileRecord) {
                    $q->where('file_id', $fileRecord->id);
                })->count()
            ]);

            return response()->json([
                'message' => $message,
                'file_id' => $fileRecord->id,
                'file_name' => $fileName,
                'imported_sheets' => $importedSheets,
                'imported_rows' => $importedRows,
                'errors' => $errors,
            ], 200);
        });
    }
}
