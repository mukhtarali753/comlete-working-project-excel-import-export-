<?php

namespace App\Helpers\SheetControllerHelper;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportExcelHelper
{
    public static function handle(Request $request)
    {
        try {
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

            Log::info("Excel import started", [
                'fileName' => $fileName,
                'totalSheets' => count($sheets),
                'firstSheetRows' => count($sheets[0] ?? []),
                'firstSheetSample' => array_slice($sheets[0] ?? [], 0, 3)
            ]);

            DB::beginTransaction();

            $fileRecord = File::create([
                'name' => $fileName,
                'user_id' => Auth::id(),
            ]);

            $importedSheets = 0;
            $importedRows = 0;
            $errors = [];

            foreach ($sheets as $sheetIndex => $sheetData) {
                try {
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

                    $sheet = Sheet::create([
                        'file_id' => $fileRecord->id,
                        'name' => $uniqueName,
                        'order' => $sheetIndex,
                    ]);

                    $importedSheets++;
                    $sheetRowCount = 0;

                    foreach ($sheetData as $rowIndex => $row) {
                        Log::info("Processing row", [
                            'rowIndex' => $rowIndex,
                            'rowType' => gettype($row),
                            'rowIsArray' => is_array($row),
                            'rowCount' => is_array($row) ? count($row) : 'N/A',
                            'rowSample' => is_array($row) ? array_slice($row, 0, 3) : $row
                        ]);

                        if (!is_array($row)) {
                            Log::warning("Skipping non-array row", ['rowIndex' => $rowIndex, 'row' => $row]);
                            continue;
                        }

                        $cleanRow = [];
                        $hasContent = false;

                        foreach ($row as $cellIndex => $cell) {
                            $value = trim($cell ?? '');
                            $cleanRow[] = $value;
                            if ($value !== '') {
                                $hasContent = true;
                            }
                        }

                        Log::info("Row processed", [
                            'rowIndex' => $rowIndex,
                            'cleanRow' => $cleanRow,
                            'hasContent' => $hasContent,
                            'willSave' => ($hasContent || $rowIndex === 0)
                        ]);

                        if ($hasContent || $rowIndex === 0) {
                            Log::info("Saving row", [
                                'sheetId' => $sheet->id,
                                'rowIndex' => $rowIndex,
                                'cleanRow' => $cleanRow,
                                'rowData' => json_encode($cleanRow)
                            ]);

                            $sheetRow = SheetRow::create([
                                'sheet_id' => $sheet->id,
                                'sheet_data' => json_encode($cleanRow),
                            ]);

                            Log::info("Row created", [
                                'sheetRowId' => $sheetRow->id,
                                'sheetId' => $sheetRow->sheet_id,
                                'dataSaved' => $sheetRow->sheet_data
                            ]);

                            $importedRows++;
                            $sheetRowCount++;
                        } else {
                            Log::info("Skipping empty row", ['rowIndex' => $rowIndex]);
                        }
                    }

                    Log::info("Imported sheet '{$uniqueName}' with {$sheetRowCount} rows");
                } catch (\Exception $e) {
                    $sheetNumber = $sheetIndex + 1;
                    $errors[] = "Error importing sheet {$sheetNumber}: " . $e->getMessage();
                    Log::error("Sheet import error: " . $e->getMessage());
                }
            }

            Storage::delete($path);

            $emptySheets = Sheet::where('file_id', $fileRecord->id)
                ->whereDoesntHave('rows')
                ->get();

            foreach ($emptySheets as $emptySheet) {
                $emptySheet->delete();
                $importedSheets--;
            }

            DB::commit();

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
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }

            Log::error("Excel import failed: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to import Excel file: ' . $e->getMessage()
            ], 500);
        }
    }
}

