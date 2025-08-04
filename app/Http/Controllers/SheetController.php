<?php
namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
                'file_id' => 'nullable|exists:files,id',
            ]);

            $file = null;

            if (!empty($data['file_id'])) {
                $file = File::find($data['file_id']);
            }

            if (!$file) {
                $file = File::firstOrCreate(
                    ['name' => $data['name']],
                    [
                        'user_id' => Auth::check() ? Auth::id() : null,
                    ]
                );
            }

            foreach ($data['sheets'] as $sheetData) {
                $sheet = Sheet::updateOrCreate(
                    ['file_id' => $file->id, 'name' => $sheetData['name']],
                    ['order' => $sheetData['order'] ?? 0]
                );

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

            return response()->json([
                'message' => 'Sheets and rows saved successfully.',
                'file_id' => $file->id
            ], 200);
        } catch (\Exception $e) {
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

    public function getSheets()
    {
        $sheets = Sheet::with('rows')->get()->map(function ($sheet) {
            $rows = $sheet->rows->map(function ($row) {
                return array_map(function ($value) {
                    return ['v' => $value];
                }, json_decode($row->sheet_data, true));
            })->toArray();

            return [
                'name' => $sheet->name,
                'data' => $rows,
                'config' => [
                    'rowlen' => array_fill(0, count($rows), 30),
                    'columnlen' => array_fill(0, count($rows[0] ?? []), 200),
                ],
                'order' => $sheet->order,
            ];
        })->sortBy('order')->values()->toArray();

        return response()->json($sheets);
    }

    public function getSheetsByFile($id)
    {
        $file = File::with('sheets')->findOrFail($id);

        $sheets = $file->sheets->map(function ($sheet) {
            return [
                'name' => $sheet->name,
                'data' => json_decode($sheet->data, true),
                'config' => json_decode($sheet->config, true),
                'order' => $sheet->order,
            ];
        });

        return response()->json($sheets);
    }

    public function deleteSheet($id)
{
    try {
        $sheet = Sheet::findOrFail($id);

        
        SheetRow::where('sheet_id', $sheet->id)->delete();

        
        $sheet->delete();

       
     return response()->json(['message' => 'Sheet deleted successfully.'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Failed to delete sheet: ' . $e->getMessage()], 500);
    }
}

}