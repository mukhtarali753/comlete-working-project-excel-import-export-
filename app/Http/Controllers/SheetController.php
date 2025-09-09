<?php
namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use App\Models\SheetRowVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
// History feature removed

class SheetController extends Controller
{
    public function index()
    {
        $businesses = [];
        return view('file.preview', compact('businesses'));
    }

    public function saveSheets(Request $request)
    {
        // Set execution time limit for large operations
        set_time_limit(600); // 10 minutes
        
        // Set memory limit
        ini_set('memory_limit', '512M');
        
        try {
            // Set database connection timeout
            DB::statement('SET SESSION wait_timeout = 600');
            DB::statement('SET SESSION interactive_timeout = 600');
            
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'sheets' => 'required|array',
                'sheets.*.name' => 'required|string|max:255',
                'sheets.*.data' => 'required|string',
                'sheets.*.order' => 'nullable|integer|min:0',
                'sheets.*.id' => 'nullable|exists:sheets,id',
                'sheets.*.rowUpdates' => 'nullable|array',
                'sheets.*.rowUpdates.*.rowIndex' => 'nullable|integer|min:0',
                'sheets.*.rowUpdates.*.rowId' => 'nullable|integer',
                'sheets.*.rowUpdates.*.data' => 'nullable|array',
                'sheets.*.rowUpdates.*.modified' => 'nullable|boolean',
                'file_id' => 'nullable|exists:files,id',
                'enable_version_history' => 'nullable|boolean',
            ]);
            
            // Log the received data for debugging
            Log::info('Save request received', [
                'file_id' => $data['file_id'] ?? null,
                'sheets_count' => count($data['sheets']),
                'enable_version_history' => $data['enable_version_history'] ?? 'not set',
                'sheets_data' => array_map(function($sheet) {
                    return [
                        'id' => $sheet['id'] ?? null,
                        'name' => $sheet['name'] ?? null,
                        'rowUpdates_count' => count($sheet['rowUpdates'] ?? []),
                        'rowUpdates_sample' => array_slice($sheet['rowUpdates'] ?? [], 0, 3)
                    ];
                }, $data['sheets'])
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
                    // Find existing sheet
                    $existingSheet = Sheet::where('id', $sheetData['id'])
                        ->where('file_id', $file->id)
                        ->first();
                    
                    if ($existingSheet) {
                        // For existing sheets, update the current sheet instead of creating new entries
                        // This avoids the unique constraint issue while still maintaining version history
                        
                        // Create version history before updating
                        if (!empty($sheetData['data'])) {
                            $this->createVersionHistoryForSheet($existingSheet);
                            
                            // Increment the sheet version number
                            $maxVersion = SheetRowVersion::where('sheet_id', $existingSheet->id)->max('version_number');
                            $nextVersion = (is_null($maxVersion) ? 0 : (int)$maxVersion) + 1;
                            $existingSheet->version = $nextVersion;
                        }
                        
                        // Update the existing sheet
                        $existingSheet->update([
                            'data' => $sheetData['data'],
                            'config' => $sheetData['config'] ?? null,
                            'celldata' => $sheetData['celldata'] ?? null,
                            'version' => $existingSheet->version,
                            'updated_at' => now(),
                        ]);
                        
                        $sheet = $existingSheet;
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
                                'data' => $sheetData['data'],
                                'config' => $sheetData['config'] ?? null,
                                'celldata' => $sheetData['celldata'] ?? null,
                                'version' => 1,
                                'is_current' => 1,
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
                        'data' => $sheetData['data'],
                        'config' => $sheetData['config'] ?? null,
                        'celldata' => $sheetData['celldata'] ?? null,
                        'version' => 1,
                        'is_current' => 1,
                    ]);
                }

                $updatedSheetIds[] = $sheet->id;

                // Only create version history if this is an existing sheet with data and version history is enabled
                // Also check if the sheet is too large (more than 1000 rows) to prevent timeouts
                $rowCount = $sheet->rows()->count();
                $shouldCreateVersionHistory = !empty($sheetData['id']) && $rowCount > 0 && 
                    (!isset($data['enable_version_history']) || $data['enable_version_history']) &&
                    $rowCount <= 1000; // Limit version history to sheets with 1000 rows or less
                
                Log::info('Sheet ' . $sheet->id . ' - Row count: ' . $rowCount . ', Should create version history: ' . ($shouldCreateVersionHistory ? 'Yes' : 'No'));
                
                if ($shouldCreateVersionHistory) {
                    Log::info('Creating version history for sheet ' . $sheet->id);
                    $this->createVersionHistoryForSheet($sheet);
                } elseif ($rowCount > 1000) {
                    Log::info('Skipping version history for large sheet ' . $sheet->id . ' with ' . $rowCount . ' rows');
                } else {
                    Log::info('Skipping version history for sheet ' . $sheet->id . ' - Conditions not met');
                }

                // Delete all existing rows for this sheet and rebuild from payload
                SheetRow::where('sheet_id', $sheet->id)->delete();

                $rows = json_decode($sheetData['data'], true);

                // Process rows in chunks to prevent memory issues
                $chunkSize = 50; // Reduced chunk size for better performance
                $rowChunks = array_chunk($rows, $chunkSize, true);
                $totalChunks = count($rowChunks);

                foreach ($rowChunks as $chunkIndex => $rowChunk) {
                    $this->processRowChunk($sheet, $rowChunk, $chunkIndex * $chunkSize);
                    
                    // Log progress for large operations
                    if ($totalChunks > 10 && $chunkIndex % 10 === 0) {
                        Log::info('Processing sheet ' . $sheet->id . ': ' . round(($chunkIndex / $totalChunks) * 100, 1) . '% complete');
                    }
                }
            }

            // Note: We're not automatically deleting sheets that aren't in the current request
            // This prevents accidental deletion when adding new sheets
            // Sheets should be explicitly deleted through the delete endpoint

            DB::commit();

            return response()->json([
                'message' => 'Sheets and rows saved successfully with version history.',
                'file_id' => $file->id,
                'sheets' => $file->sheets()->select('id', 'name', 'order')->get()->toArray()
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save sheets failed: ' . $e->getMessage(), [
                'file_id' => $data['file_id'] ?? null,
                'sheets_count' => count($data['sheets'] ?? []),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to save sheets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create version history for a sheet using bulk insert for better performance
     */
    private function createVersionHistoryForSheet($sheet)
    {
        try {
            // Get all existing rows data before deletion with their IDs
            $existingRows = $sheet->rows()->get(['id', 'sheet_data', 'cell_formatting', 'created_at']);
            
            if ($existingRows->isEmpty()) {
                return; // No rows to process
            }
            
            // Get the next version number
            $maxVersion = SheetRowVersion::where('sheet_id', $sheet->id)->max('version_number');
            $nextVersion = (is_null($maxVersion) ? 0 : (int)$maxVersion) + 1;
            
            // Create version history entries with proper row references
            $versionData = [];
            
            foreach ($existingRows as $row) {
                // Ensure sheet_data is properly encoded
                $sheetData = is_string($row->sheet_data) ? $row->sheet_data : json_encode($row->sheet_data);
                
                // Ensure cell_formatting is properly encoded
                $cellFormatting = null;
                if ($row->cell_formatting) {
                    $cellFormatting = is_string($row->cell_formatting) ? $row->cell_formatting : json_encode($row->cell_formatting);
                }
                
                $versionData[] = [
                    'sheet_row_id' => $row->id, // Keep the actual row ID reference
                    'sheet_id' => $sheet->id,
                    'sheet_data' => $sheetData,
                    'cell_formatting' => $cellFormatting,
                    'version_number' => $nextVersion,
                    'created_at' => $row->created_at
                ];
            }
            
            // Bulk insert all version data at once
            if (!empty($versionData)) {
                try {
                    SheetRowVersion::insert($versionData);
                    Log::info('Successfully created version history for sheet ' . $sheet->id . ' with ' . count($versionData) . ' entries');
                } catch (\Exception $e) {
                    Log::error('Failed to insert version history for sheet ' . $sheet->id . ': ' . $e->getMessage());
                    throw $e;
                }
            } else {
                Log::warning('No version data to insert for sheet ' . $sheet->id);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to create version history for sheet ' . $sheet->id . ': ' . $e->getMessage());
            // Don't fail the entire save operation if version history fails
        }
    }

    /**
     * Process a chunk of rows for better performance
     */
    private function processRowChunk($sheet, $rows, $startIndex)
    {
        $rowData = [];
        
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

            if ($allEmpty && $startIndex + $rowIndex !== 0) continue;

            $rowData[] = [
                'sheet_id' => $sheet->id,
                'sheet_data' => json_encode($cleanRow),
                'cell_formatting' => !empty($formatRow) ? json_encode($formatRow) : null,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        // Bulk insert rows for better performance
        if (!empty($rowData)) {
            try {
                SheetRow::insert($rowData);
            } catch (\Exception $e) {
                Log::error('Failed to insert rows for sheet ' . $sheet->id . ': ' . $e->getMessage());
                throw $e; // Re-throw to trigger rollback
            }
        }
    }

    /**
     * Apply targeted row updates, preserving existing data and creating versions when enabled
     */
    private function applyRowUpdates(Sheet $sheet, array $rowUpdates, bool $enableVersionHistory): void
    {
        foreach ($rowUpdates as $update) {
            $rowId = $update['rowId'] ?? null;
            $dataRow = $update['data'] ?? [];
            if (!is_array($dataRow)) { continue; }

            // Build sparse maps for values and formatting
            $cleanRow = [];
            $formatRow = [];
            foreach ($dataRow as $colIndex => $cell) {
                $value = is_array($cell) && array_key_exists('v', $cell) ? trim((string)$cell['v']) : (is_string($cell) ? trim($cell) : '');
                if ($value !== '') {
                    $cleanRow[(string)$colIndex] = $value;
                }
                if (is_array($cell)) {
                    $format = [];
                    foreach (['ct','bg','fc','bl','it','un','ff','fs','ht','vt','tb','tr'] as $key) {
                        if (array_key_exists($key, $cell)) {
                            $format[$key] = $cell[$key];
                        }
                    }
                    if (!empty($format)) {
                        $formatRow[(string)$colIndex] = $format;
                    }
                }
            }

            if ($rowId) {
                $row = SheetRow::where('sheet_id', $sheet->id)->where('id', $rowId)->first();
                if ($row) {
                    // Version current row if enabled
                    if ($enableVersionHistory) {
                        try {
                            // Ensure sheet_data is properly encoded
                            $sheetData = is_string($row->sheet_data) ? $row->sheet_data : json_encode($row->sheet_data);
                            
                            // Ensure cell_formatting is properly encoded
                            $cellFormatting = null;
                            if ($row->cell_formatting) {
                                $cellFormatting = is_string($row->cell_formatting) ? $row->cell_formatting : json_encode($row->cell_formatting);
                            }
                            
                            SheetRowVersion::create([
                                'sheet_row_id' => $row->id,
                                'sheet_id' => $sheet->id,
                                'sheet_data' => $sheetData,
                                'cell_formatting' => $cellFormatting,
                                'version_number' => $sheet->version ?? 1,
                                'created_at' => $row->updated_at ?? now(),
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to create row version for row '.$row->id.': '.$e->getMessage());
                        }
                    }
                    // Update row
                    $row->update([
                        'sheet_data' => json_encode($cleanRow),
                        'cell_formatting' => !empty($formatRow) ? json_encode($formatRow) : null,
                    ]);
                    continue;
                }
            }

            // Create new row if no rowId or not found
            SheetRow::create([
                'sheet_id' => $sheet->id,
                'sheet_data' => json_encode($cleanRow),
                'cell_formatting' => !empty($formatRow) ? json_encode($formatRow) : null,
            ]);
        }
    }

    public function getSheetData(Sheet $sheet)
    {
        return response()->json([
            'sheet_name' => $sheet->name,
            'rows' => $sheet->rows->map(function ($row) {
                $sheetData = is_array($row->sheet_data) ? $row->sheet_data : (is_string($row->sheet_data) ? json_decode($row->sheet_data, true) : []);
                return is_array($sheetData) ? $sheetData : [];
            })->toArray(),
        ]);
    }

    public function show(File $file)
    {
        $sheets = $file->sheets()->orderBy('order')->get()->map(function ($sheet) {
            $rows = $sheet->rows->map(function ($row) {
                // Reconstruct row from sparse maps: sheet_data[colIndex] => value, cell_formatting[colIndex] => format
                $values = is_array($row->sheet_data) ? $row->sheet_data : (is_string($row->sheet_data) ? json_decode($row->sheet_data, true) : []);
                $formats = is_array($row->cell_formatting) ? $row->cell_formatting : (is_string($row->cell_formatting) ? json_decode($row->cell_formatting, true) : []);

                // Ensure values and formats are arrays
                $values = is_array($values) ? $values : [];
                $formats = is_array($formats) ? $formats : [];

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

            // Get row IDs for tracking changes
            $rowIds = $sheet->rows->pluck('id')->toArray();

            return [
                'id' => $sheet->id,
                'name' => $sheet->name,
                'data' => $rows,
                'rowIds' => $rowIds, // Include row IDs for tracking
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

    public function getSheetsByFile($id)
    {
        $file = File::findOrFail($id);
        $sheets = $file->sheets()->orderBy('order')->get(['id','name','order']);
        return response()->json($sheets);
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

    /**
     * Restore an entire sheet to a selected version.
     * This updates the current sheet with the selected version's data from sheet_row_versions.
     */
    public function restoreSheetVersion($sheetId, $versionNumber)
    {
        try {
            DB::beginTransaction();

            $currentSheet = Sheet::findOrFail($sheetId);
            
            Log::info('Restoring sheet version', [
                'sheet_id' => $sheetId,
                'target_version' => $versionNumber,
                'sheet_name' => $currentSheet->name,
                'file_id' => $currentSheet->file_id
            ]);

            // Get version history entries for the target version
            $versionEntries = SheetRowVersion::where('sheet_id', $currentSheet->id)
                ->where('version_number', (int)$versionNumber)
                ->get();

            if ($versionEntries->isEmpty()) {
                throw new \Exception('Version ' . $versionNumber . ' not found in version history');
            }

            // Create version history for current sheet before restoring
            $this->createVersionHistoryForSheet($currentSheet);

            // Replace current sheet rows with the historical rows
            SheetRow::where('sheet_id', $currentSheet->id)->delete();

            // Restore rows from version history
            if ($versionEntries->count() > 0) {
                $insertRows = [];
                $now = now();
                foreach ($versionEntries as $versionEntry) {
                    // Ensure sheet_data is properly encoded
                    $sheetData = is_string($versionEntry->sheet_data) ? $versionEntry->sheet_data : json_encode($versionEntry->sheet_data);
                    
                    // Ensure cell_formatting is properly encoded
                    $cellFormatting = null;
                    if ($versionEntry->cell_formatting) {
                        $cellFormatting = is_string($versionEntry->cell_formatting) ? $versionEntry->cell_formatting : json_encode($versionEntry->cell_formatting);
                    }
                    
                    $insertRows[] = [
                        'sheet_id' => $currentSheet->id,
                        'sheet_data' => $sheetData,
                        'cell_formatting' => $cellFormatting,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                SheetRow::insert($insertRows);
            }

            DB::commit();

            return response()->json([
                'message' => 'Sheet restored to version ' . $versionNumber . ' successfully',
                'sheet_id' => $currentSheet->id,
                'version' => $currentSheet->version,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to restore sheet: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get version history for a specific sheet row
     */
    public function getRowVersionHistory($rowId)
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

    /**
     * Get version history for an entire sheet
     */
    public function getSheetVersionHistory($sheetId)
    {
        try {
            $sheet = Sheet::findOrFail($sheetId);
            
            Log::info('Getting version history for sheet ' . $sheetId, [
                'sheet_name' => $sheet->name,
                'file_id' => $sheet->file_id,
                'current_version' => $sheet->version,
                'is_current' => $sheet->is_current
            ]);
            
            // Sheet-level history (version, is_current, created_at)
            // Since we're not creating multiple sheet entries, we'll use the sheet_row_versions table
            // to track version history at the row level
            $versionHistoryEntries = SheetRowVersion::where('sheet_id', $sheet->id)
                ->orderBy('version_number', 'desc')
                ->get();

            // Group by version number to get unique versions
            $uniqueVersions = $versionHistoryEntries->groupBy('version_number');
            
            $sheetHistory = [];
            foreach ($uniqueVersions as $versionNumber => $entries) {
                $latestEntry = $entries->first();
                $sheetHistory[] = [
                    'sheet_id' => $sheet->id,
                    'version' => $versionNumber,
                    'is_current' => $versionNumber == $sheet->version,
                    'created_at' => $latestEntry->created_at ? $latestEntry->created_at->toIso8601String() : null,
                ];
            }
            
            // Add current version if not already included
            $currentVersionExists = collect($sheetHistory)->contains('version', $sheet->version);
            if (!$currentVersionExists) {
                $sheetHistory[] = [
                    'sheet_id' => $sheet->id,
                    'version' => $sheet->version ?? 1,
                    'is_current' => true,
                    'created_at' => $sheet->updated_at ? $sheet->updated_at->toIso8601String() : null,
                ];
            }
            
            // Sort by version number descending
            $sheetHistory = collect($sheetHistory)->sortByDesc('version')->values()->toArray();

            // Row-level history (existing behavior), kept for detailed drilldown
            $versionHistoryEntries = SheetRowVersion::where('sheet_id', $sheetId)
                ->orderBy('sheet_row_id')
                ->orderBy('version_number', 'desc')
                ->get();

            $versionHistory = [];
            $groupedVersions = $versionHistoryEntries->groupBy('sheet_row_id');
            
            foreach ($groupedVersions as $rowId => $versions) {
                // Get current row data
                $currentRow = $sheet->rows()->where('id', $rowId)->first();
                $currentVersion = $currentRow ? $currentRow->version : 1;
                
                // Format version history
                $formattedVersions = $versions->map(function ($version) {
                    // Ensure sheet_data is properly decoded if it's a JSON string
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
            
            // Debug: Log the first few version history entries
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

    /**
     * Ensure only one version per sheet has is_current = 1
     */
    private function ensureSingleCurrentVersion($fileId, $sheetName, $excludeSheetId = null)
    {
        $query = Sheet::where('file_id', $fileId)
            ->where('name', $sheetName);
            
        if ($excludeSheetId) {
            $query->where('id', '!=', $excludeSheetId);
        }
        
        $query->update(['is_current' => 0]);
    }

    /**
     * Restore a previous version of a sheet row
     */
    public function restoreRowVersion($rowId, $versionNumber)
    {
        try {
            DB::beginTransaction();
            
            $row = SheetRow::findOrFail($rowId);
            $version = SheetRowVersion::where('sheet_row_id', $rowId)
                ->where('version_number', $versionNumber)
                ->first();
            
            if (!$version) {
                throw new \Exception('Version not found');
            }
            
            // Create version history for current row before restoring
            SheetRowVersion::create([
                'sheet_row_id' => $row->id,
                'sheet_id' => $row->sheet_id,
                'sheet_data' => $row->sheet_data,
                'cell_formatting' => $row->cell_formatting,
                'version_number' => $row->version,
                'created_at' => $row->created_at
            ]);
            
            // Restore the previous version
            $row->update([
                'sheet_data' => $version->sheet_data,
                'cell_formatting' => $version->cell_formatting,
                'version' => $row->version + 1
            ]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Version restored successfully',
                'new_version' => $row->version
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to restore version: ' . $e->getMessage()
            ], 500);
        }
    }
}