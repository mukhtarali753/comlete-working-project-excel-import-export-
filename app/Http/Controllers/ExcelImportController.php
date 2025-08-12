<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ExcelImportController extends Controller
{
    public function index()
    {
        $files = File::withCount('sheets')->orderBy('created_at', 'desc')->get();
        return view('excel.import', compact('files'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            
            // Store the file temporarily
            $path = $file->store('temp');
            
            // Get sheet names
            $sheetNames = $this->getSheetNames($path);
            
            // Preview first sheet
            $previewData = Excel::toArray([], $path)[0] ?? [];
            
            return view('excel.preview', compact('fileName', 'sheetNames', 'previewData', 'path'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error reading file: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_path' => 'required|string',
            'file_name' => 'required|string',
            'selected_sheets' => 'required|array|min:1',
        ]);

        try {
            $filePath = $request->input('file_path');
            $fileName = $request->input('file_name');
            $selectedSheets = $request->input('selected_sheets');

            // Create the file record
            $file = File::create([
                'name' => $fileName,
                'user_id' => Auth::id(),
            ]);

            $importedSheets = 0;
            $importedRows = 0;
            $errors = [];

            // Import each selected sheet
            foreach ($selectedSheets as $sheetName) {
                try {
                    $sheetData = Excel::toArray([], $filePath);
                    $sheetIndex = $this->getSheetIndex($sheetName, $filePath);
                    
                    if ($sheetIndex !== false) {
                        $rows = $sheetData[$sheetIndex] ?? [];
                        
                        // Create sheet record
                        $sheet = Sheet::create([
                            'file_id' => $file->id,
                            'name' => $sheetName,
                            'order' => $this->getSheetOrder($sheetName),
                        ]);

                        // Import rows
                        foreach ($rows as $rowIndex => $row) {
                            if ($this->isEmptyRow($row)) {
                                continue;
                            }

                            $cleanRow = $this->cleanRowData($row);
                            
                            if (!empty($cleanRow)) {
                                SheetRow::create([
                                    'sheet_id' => $sheet->id,
                                    'sheet_data' => json_encode($cleanRow),
                                ]);
                                $importedRows++;
                            }
                        }
                        
                        $importedSheets++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error importing sheet '{$sheetName}': " . $e->getMessage();
                }
            }

            // Clean up temporary file
            Storage::delete($filePath);

            // Clean up empty sheets
            Sheet::where('file_id', $file->id)
                ->whereDoesntHave('rows')
                ->delete();

            $message = "Successfully imported {$importedSheets} sheet(s) with {$importedRows} row(s).";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->route('excel.import.index')
                ->with('success', $message)
                ->with('file_id', $file->id);

        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function show(File $file)
    {
        $sheets = $file->sheets()->orderBy('order')->get()->map(function ($sheet) {
            $rows = $sheet->rows->map(function ($row) {
                return json_decode($row->sheet_data, true);
            })->toArray();

            return [
                'id' => $sheet->id,
                'name' => $sheet->name,
                'data' => $rows,
                'row_count' => count($rows),
            ];
        })->toArray();

        return view('excel.show', compact('file', 'sheets'));
    }

    public function download(File $file, $type = 'xlsx')
    {
        $fileName = $file->name . '.' . $type;
        
        // Create Excel export using the existing export functionality
        $sheets = $file->sheets()->orderBy('order')->get();
        
        $exportData = [];
        foreach ($sheets as $sheet) {
            $rows = $sheet->rows->map(function ($row) {
                return json_decode($row->sheet_data, true);
            })->toArray();
            
            $exportData[$sheet->name] = $rows;
        }

        return Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
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
    }

    private function getSheetNames($filePath)
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile(storage_path('app/' . $filePath));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load(storage_path('app/' . $filePath));
            
            $sheetNames = [];
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                $sheetNames[] = $sheetName;
            }
            
            return $sheetNames;
        } catch (\Exception $e) {
            // Fallback: try to get sheet names using Excel facade
            try {
                $sheetData = Excel::toArray([], $filePath);
                $sheetNames = [];
                for ($i = 0; $i < count($sheetData); $i++) {
                    $sheetNames[] = 'Sheet' . ($i + 1);
                }
                return $sheetNames;
            } catch (\Exception $e2) {
                return ['Sheet1'];
            }
        }
    }

    private function getSheetIndex($sheetName, $filePath)
    {
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile(storage_path('app/' . $filePath));
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load(storage_path('app/' . $filePath));
            
            $sheetNames = $spreadsheet->getSheetNames();
            return array_search($sheetName, $sheetNames);
        } catch (\Exception $e) {
            // Fallback: extract number from sheet name
            if (preg_match('/(\d+)$/', $sheetName, $matches)) {
                return (int)$matches[1] - 1; // Convert to 0-based index
            }
            return 0;
        }
    }

    private function isEmptyRow($row): bool
    {
        if (!is_array($row)) return true;
        
        foreach ($row as $cell) {
            if (!empty(trim($cell))) {
                return false;
            }
        }
        return true;
    }

    private function cleanRowData($row): array
    {
        if (!is_array($row)) return [];
        
        $cleanRow = [];
        foreach ($row as $cell) {
            $value = is_string($cell) ? trim($cell) : (string)$cell;
            $cleanRow[] = $value;
        }
        
        return $cleanRow;
    }

    private function getSheetOrder($sheetName): int
    {
        if (preg_match('/(\d+)$/', $sheetName, $matches)) {
            return (int)$matches[1];
        }
        return 0;
    }
}

