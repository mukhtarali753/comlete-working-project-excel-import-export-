<?php
namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            }

            if (!$file) {
                $file = File::create([
                    'name' => $data['name'],
                    'user_id' => Auth::check() ? Auth::id() : null,
                ]);
            }

            // Track which sheets we're processing
            $updatedSheetIds = [];

            foreach ($data['sheets'] as $sheetData) {
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
                        // Sheet ID provided but not found, create new one
                        $sheet = Sheet::create([
                            'file_id' => $file->id,
                            'name' => $sheetData['name'],
                            'order' => $sheetData['order'] ?? 0,
                        ]);
                    }
                } else {
                    // Create new sheet
                    $sheet = Sheet::create([
                        'file_id' => $file->id,
                        'name' => $sheetData['name'],
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
                    $allEmpty = true;

                    foreach ($row as $cell) {
                        $value = is_array($cell) && isset($cell['v']) ? trim($cell['v']) : '';
                        $cleanRow[] = $value;
                        if ($value !== '') {
                            $allEmpty = false;
                        }
                    }

                    if ($allEmpty && $rowIndex !== 0) continue;

                    SheetRow::create([
                        'sheet_id' => $sheet->id,
                        'sheet_data' => json_encode($cleanRow),
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
                return json_decode($row->sheet_data, true);
            })->toArray(),
        ]);
    }

    public function show(File $file)
    {
        $sheets = $file->sheets()->orderBy('order')->get()->map(function ($sheet) {
            $rows = $sheet->rows->map(function ($row) {
                return array_map(function ($value) {
                    return ['v' => $value];
                }, json_decode($row->sheet_data, true));
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
            
            DB::beginTransaction();

            // Create or update the file record
            $fileRecord = File::create([
                'name' => $fileName,
                'user_id' => Auth::id(),
            ]);

            $importedSheets = 0;
            $importedRows = 0;

            foreach ($sheets as $sheetIndex => $sheetData) {
                // Create sheet record
                $sheet = Sheet::create([
                    'file_id' => $fileRecord->id,
                    'name' => 'Sheet' . ($sheetIndex + 1),
                    'order' => $sheetIndex,
                ]);

                $importedSheets++;

                // Import rows
                foreach ($sheetData as $rowIndex => $row) {
                    if (!is_array($row)) continue;

                    $cleanRow = [];
                    $allEmpty = true;

                    foreach ($row as $cell) {
                        $value = trim($cell ?? '');
                        $cleanRow[] = $value;
                        if ($value !== '') {
                            $allEmpty = false;
                        }
                    }

                    // Skip empty rows (except header row)
                    if ($allEmpty && $rowIndex !== 0) continue;

                    SheetRow::create([
                        'sheet_id' => $sheet->id,
                        'sheet_data' => json_encode($cleanRow),
                    ]);
                    $importedRows++;
                }
            }

            // Clean up temporary file
            \Illuminate\Support\Facades\Storage::delete($path);

            DB::commit();

            return response()->json([
                'message' => "Successfully imported {$importedSheets} sheet(s) with {$importedRows} row(s).",
                'file_id' => $fileRecord->id,
                'file_name' => $fileName,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to import Excel file: ' . $e->getMessage()
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
                    return json_decode($row->sheet_data, true);
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