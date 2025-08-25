<?php
namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SheetController extends Controller
{
    public function index()
    {
        $businesses = [];
        return view('file.preview', compact('businesses'));
    }

    public function saveSheets(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'sheets' => 'required|array',
                'sheets.*.name' => 'required|string|max:255',
                'sheets.*.data' => 'required|string',
                'sheets.*.order' => 'nullable|integer|min:0',
                'sheets.*.id' => 'nullable|exists:sheets,id',
                'file_id' => 'nullable|exists:files,id',
            ]);

            DB::beginTransaction();

            $file = null;

            if (!empty($data['file_id'])) {
                $file = File::find($data['file_id']);
                if (!$file) {
                    throw new \Exception('File not found with ID: ' . $data['file_id']);
                }
            }

            if (!$file) {
                $file = File::create([
                    'name' => $data['name'],
                    'user_id' => Auth::check() ? Auth::id() : null,
                ]);
            }

            // Ensure we have a valid file
            if (!$file || !$file->id) {
                throw new \Exception('Failed to create or retrieve file');
            }

            // Track which sheets we're processing
            $updatedSheetIds = [];

            foreach ($data['sheets'] as $sheetData) {
                // Validate sheet data (excluding file_id since it's handled at the file level)
                $sheetValidationRules = Sheet::getBasicValidationRules();
                $sheetValidationRules['data'] = 'required|string';
                
                $validator = Validator::make($sheetData, $sheetValidationRules);
                if ($validator->fails()) {
                    throw new \Exception('Validation failed for sheet: ' . $validator->errors()->first());
                }

                // Handle new sheets vs existing sheets differently
                if (!empty($sheetData['id'])) {
                    // Update existing sheet
                    $sheet = Sheet::where('id', $sheetData['id'])
                        ->where('file_id', $file->id)
                        ->first();
                    
                    if ($sheet) {
                        $sheet->update([
                            'name' => $sheetData['name'],
                            'order' => $sheetData['order'] ?? 0,
                        ]);
                    } else {
                        // Sheet ID provided but not found, check if sheet with same name exists
                        $existingSheet = Sheet::where('file_id', $file->id)
                            ->where('name', $sheetData['name'])
                            ->first();
                        
                        if ($existingSheet) {
                            // Use existing sheet with same name
                            $sheet = $existingSheet;
                        } else {
                            // Generate unique name for new sheet
                            $baseName = $sheetData['name'];
                            $counter = 1;
                            $uniqueName = $baseName;
                            
                            while (Sheet::where('file_id', $file->id)->where('name', $uniqueName)->exists()) {
                                $uniqueName = $baseName . ' (' . $counter . ')';
                                $counter++;
                            }
                            
                            $sheet = Sheet::create([
                                'file_id' => $file->id,
                                'name' => $uniqueName,
                                'order' => $sheetData['order'] ?? 0,
                            ]);
                        }
                    }
                } else {
                    // Create new sheet, but check for duplicate names
                    $baseName = $sheetData['name'];
                    $counter = 1;
                    $uniqueName = $baseName;
                    
                    while (Sheet::where('file_id', $file->id)->where('name', $uniqueName)->exists()) {
                        $uniqueName = $baseName . ' (' . $counter . ')';
                        $counter++;
                    }
                    
                    $sheet = Sheet::create([
                        'file_id' => $file->id,
                        'name' => $uniqueName,
                        'order' => $sheetData['order'] ?? 0,
                    ]);
                }

                $updatedSheetIds[] = $sheet->id;

                // Delete all existing rows for this sheet
                SheetRow::where('sheet_id', $sheet->id)->delete();

                $rows = json_decode($sheetData['data'], true);

                foreach ($rows as $rowIndex => $row) {
                    if (!is_array($row)) continue;

                    $cleanRow = [];
                    $formatRow = [];
                    $allEmpty = true;

                    foreach ($row as $colIndex => $cell) {
                        $value = is_array($cell) && array_key_exists('v', $cell) ? trim((string)$cell['v']) : '';
                        if ($value !== '') {
                            $cleanRow[(string)$colIndex] = $value; // sparse map colIndex => value
                            $allEmpty = false;
                        }
                        // Capture formatting only for this cell if present
                        if (is_array($cell)) {
                            $format = [];
                            foreach (['ct','bg','fc','bl','it','un','ff','fs','ht','vt','tb','tr'] as $key) {
                                if (array_key_exists($key, $cell)) {
                                    $format[$key] = $cell[$key];
                                }
                            }
                            if (!empty($format)) {
                                $formatRow[(string)$colIndex] = $format; // sparse map colIndex => format
                            }
                        }
                    }

                    if ($allEmpty && $rowIndex !== 0) continue;

                    SheetRow::create([
                        'sheet_id' => $sheet->id,
                        'sheet_data' => $cleanRow, // sparse map
                        'cell_formatting' => !empty($formatRow) ? $formatRow : null,
                    ]);
                }
            }

            // Note: We're not automatically deleting sheets that aren't in the current request
            // This prevents accidental deletion when adding new sheets
            // Sheets should be explicitly deleted through the delete endpoint

            DB::commit();

            return response()->json([
                'message' => 'Sheets and rows saved successfully.',
                'file_id' => $file->id,
                'sheets' => $file->sheets()->select('id', 'name', 'order')->get()->toArray()
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to save sheets: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSheetData(Sheet $sheet)
    {
        return response()->json([
            'sheet_name' => $sheet->name,
            'rows' => $sheet->rows->map(function ($row) {
                return $row->sheet_data;
            })->toArray(),
        ]);
    }

    public function show(File $file)
    {
        $sheets = $file->sheets()->orderBy('order')->get()->map(function ($sheet) {
            $rows = $sheet->rows->map(function ($row) {
                // Reconstruct row from sparse maps: sheet_data[colIndex] => value, cell_formatting[colIndex] => format
                $values = is_array($row->sheet_data) ? $row->sheet_data : [];
                $formats = is_array($row->cell_formatting) ? $row->cell_formatting : [];

                // Determine max column index present
                $colIndices = array_map('intval', array_unique(array_merge(array_keys($values), array_keys($formats))));
                $maxCol = empty($colIndices) ? -1 : max($colIndices);
                $cells = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $cell = ['v' => isset($values[(string)$i]) ? $values[(string)$i] : ''];
                    if (isset($formats[(string)$i]) && is_array($formats[(string)$i])) {
                        foreach ($formats[(string)$i] as $k => $v) {
                            $cell[$k] = $v;
                        }
                    }
                    $cells[] = $cell;
                }
                return $cells;
            })->toArray();

            return [
                'id' => $sheet->id,
                'name' => $sheet->name,
                'data' => $rows,
                'config' => [
                    'rowlen' => array_fill(0, count($rows), 30),
                    'columnlen' => array_fill(0, count($rows[0] ?? []), 200),
                ],
                'order' => $sheet->order,
            ];
        })->toArray();

        return response()->json(['file' => $file, 'sheets' => $sheets]);
    }

    public function listFiles()
    {
        $files = File::select('id', 'name')
            ->withCount('sheets')
            ->get(); 

        return response()->json(['files' => $files]);
    }

    public function deleteSheet($id)
    {
        try {
            DB::beginTransaction();
            
            $sheet = Sheet::findOrFail($id);
            
            // Delete all rows associated with this sheet
            SheetRow::where('sheet_id', $sheet->id)->delete();
            
            // Delete the sheet itself
            $sheet->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Sheet deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete sheet: ' . $e->getMessage()
            ], 500);
        }
    }

    public function importExcel(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
                'file_name' => 'nullable|string|max:255',
            ]);

            $file = $request->file('file');
            $fileName = $request->input('file_name') ?: $file->getClientOriginalName();
            
            // Store the file temporarily
            $path = $file->store('temp');
            
            // Use Maatwebsite Excel to read the file
            $sheets = \Maatwebsite\Excel\Facades\Excel::toArray([], $path);
            
            if (empty($sheets)) {
                throw new \Exception('No data found in the uploaded file');
            }
            
            // Debug: Log the structure of imported data
            Log::info("Excel import started", [
                'fileName' => $fileName,
                'totalSheets' => count($sheets),
                'firstSheetRows' => count($sheets[0] ?? []),
                'firstSheetSample' => array_slice($sheets[0] ?? [], 0, 3)
            ]);
            
            DB::beginTransaction();

            // Create or update the file record
            $fileRecord = File::create([
                'name' => $fileName,
                'user_id' => Auth::id(),
            ]);

            $importedSheets = 0;
            $importedRows = 0;
            $errors = [];

            foreach ($sheets as $sheetIndex => $sheetData) {
                try {
                    // Debug: Log sheet processing
                    Log::info("Processing sheet", [
                        'sheetIndex' => $sheetIndex,
                        'sheetDataRows' => count($sheetData),
                        'sheetDataSample' => array_slice($sheetData, 0, 2)
                    ]);
                    
                    // Generate unique sheet name to avoid duplicates
                    $baseName = 'Sheet' . ($sheetIndex + 1);
                    $counter = 1;
                    $uniqueName = $baseName;
                    
                    while (Sheet::where('file_id', $fileRecord->id)->where('name', $uniqueName)->exists()) {
                        $uniqueName = $baseName . ' (' . $counter . ')';
                        $counter++;
                    }
                    
                    // Create sheet record
                    $sheet = Sheet::create([
                        'file_id' => $fileRecord->id,
                        'name' => $uniqueName,
                        'order' => $sheetIndex,
                    ]);

                    $importedSheets++;
                    $sheetRowCount = 0;

                    // Import rows with proper data handling
                    foreach ($sheetData as $rowIndex => $row) {
                        // Debug: Log every row for analysis
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

                        // Simple row processing - always save non-empty rows
                        $cleanRow = [];
                        $hasContent = false;
                        
                        foreach ($row as $cellIndex => $cell) {
                            $value = trim($cell ?? '');
                            $cleanRow[] = $value;
                            if ($value !== '') {
                                $hasContent = true;
                            }
                        }

                        // Debug: Log row processing details
                        Log::info("Row processed", [
                            'rowIndex' => $rowIndex,
                            'cleanRow' => $cleanRow,
                            'hasContent' => $hasContent,
                            'willSave' => ($hasContent || $rowIndex === 0) // Always save header row
                        ]);

                        // Save row if it has content or is the header row
                        if ($hasContent || $rowIndex === 0) {
                            // Debug: Log row being saved
                            Log::info("Saving row", [
                                'sheetId' => $sheet->id,
                                'rowIndex' => $rowIndex,
                                'cleanRow' => $cleanRow,
                                'rowData' => json_encode($cleanRow)
                            ]);

                            // Create sheet row with the actual data
                            $sheetRow = SheetRow::create([
                                'sheet_id' => $sheet->id,
                                'sheet_data' => json_encode($cleanRow),
                            ]);
                            
                            // Debug: Verify row was created
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
                    
                    // Log sheet import details
                    Log::info("Imported sheet '{$uniqueName}' with {$sheetRowCount} rows");
                    
                } catch (\Exception $e) {
                    $sheetNumber = $sheetIndex + 1;
                    $errors[] = "Error importing sheet {$sheetNumber}: " . $e->getMessage();
                    Log::error("Sheet import error: " . $e->getMessage());
                }
            }

            // Clean up temporary file
            \Illuminate\Support\Facades\Storage::delete($path);

            // Clean up empty sheets (sheets with no rows)
            $emptySheets = Sheet::where('file_id', $fileRecord->id)
                ->whereDoesntHave('rows')
                ->get();
                
            foreach ($emptySheets as $emptySheet) {
                $emptySheet->delete();
                $importedSheets--; // Adjust count since we're removing empty sheets
            }

            DB::commit();

            $message = "Successfully imported {$importedSheets} sheet(s) with {$importedRows} row(s).";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            // Debug: Final verification
            Log::info("Import completed", [
                'fileId' => $fileRecord->id,
                'importedSheets' => $importedSheets,
                'importedRows' => $importedRows,
                'finalSheetCount' => Sheet::where('file_id', $fileRecord->id)->count(),
                'finalRowCount' => SheetRow::whereHas('sheet', function($q) use ($fileRecord) {
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
            // Clean up temporary file if it exists
            if (isset($path) && \Illuminate\Support\Facades\Storage::exists($path)) {
                \Illuminate\Support\Facades\Storage::delete($path);
            }
            
            Log::error("Excel import failed: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to import Excel file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test method to debug import functionality
     */
    public function testImport()
    {
        try {
            // Test basic database operations
            $testFile = File::create([
                'name' => 'TEST_FILE_' . time(),
                'user_id' => Auth::id() ?? 1,
            ]);
            
            $testSheet = Sheet::create([
                'file_id' => $testFile->id,
                'name' => 'TEST_SHEET',
                'order' => 0,
            ]);
            
            $testRow = SheetRow::create([
                'sheet_id' => $testSheet->id,
                'sheet_data' => json_encode(['Test', 'Data', 'Row']),
            ]);
            
            // Verify data was created
            $verification = [
                'file_created' => $testFile->id,
                'sheet_created' => $testSheet->id,
                'row_created' => $testRow->id,
                'row_data' => $testRow->sheet_data,
                'database_connection' => 'OK'
            ];
            
            // Clean up test data
            $testRow->delete();
            $testSheet->delete();
            $testFile->delete();
            
            return response()->json([
                'message' => 'Test successful',
                'verification' => $verification
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Test failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function export(File $file, $type = 'xlsx')
    {
        try {
            $fileName = $file->name . '.' . $type;
            
            // Get all sheets for this file
            $sheets = $file->sheets()->orderBy('order')->get();
            
            $exportData = [];
            foreach ($sheets as $sheet) {
                $rows = $sheet->rows->map(function ($row) {
                    return $row->sheet_data;
                })->toArray();
                
                $exportData[$sheet->name] = $rows;
            }

            // Create Excel export using Maatwebsite Excel
            return \Maatwebsite\Excel\Facades\Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                private $data;
                
                public function __construct($data) {
                    $this->data = $data;
                }
                
                public function sheets(): array {
                    $sheets = [];
                    foreach ($this->data as $sheetName => $rows) {
                        $sheets[$sheetName] = new class($rows) implements \Maatwebsite\Excel\Concerns\ToArray {
                            private $rows;
                            
                            public function __construct($rows) {
                                $this->rows = $rows;
                            }
                            
                            public function array(array $array) {
                                return $this->rows;
                            }
                        };
                    }
                    return $sheets;
                }
            }, $fileName);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}