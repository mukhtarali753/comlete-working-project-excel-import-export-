<?php
namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessController extends Controller
{
    public function preview()
    {

        $files = File::all();
       
        return view('file.preview', compact('files'));
    }

    public function editSheet(File $business)
    {
        // dd($business);
        $sheets = $business->sheets()->orderBy('order')->get()->map(function ($sheet) {
            // Prefer persisted Luckysheet structure if available (data or celldata or config)
            if (!empty($sheet->data) || !empty($sheet->celldata) || !empty($sheet->config)) {
                $decodedData = is_string($sheet->data) ? json_decode($sheet->data, true) : (is_array($sheet->data) ? $sheet->data : []);
                $decodedConfig = is_string($sheet->config) ? json_decode($sheet->config, true) : (is_array($sheet->config) ? $sheet->config : ['rowlen' => [], 'columnlen' => []]);
                $decodedCelldata = is_string($sheet->celldata) ? json_decode($sheet->celldata, true) : (is_array($sheet->celldata) ? $sheet->celldata : []);

                // Normalize to Luckysheet cell objects { v: value }
                if (is_array($decodedData)) {
                    $decodedData = array_map(function ($row) {
                        if (!is_array($row)) return [];
                        return array_map(function ($cell) {
                            if (is_array($cell) && array_key_exists('v', $cell)) {
                                return $cell;
                            } elseif (is_array($cell)) {
                                return ['v' => ''];
                            } else {
                                return ['v' => (string)$cell];
                            }
                        }, $row);
                    }, $decodedData);
                } else {
                    $decodedData = [];
                }

                // Ensure config sizes
                if (!is_array($decodedConfig)) {
                    $decodedConfig = ['rowlen' => [], 'columnlen' => []];
                }
                if (!is_array($decodedCelldata)) {
                    $decodedCelldata = [];
                }
                
                $maxCols = 0;
                foreach ($decodedData as $row) { $maxCols = max($maxCols, is_array($row) ? count($row) : 0); }
                if (empty($decodedConfig['columnlen'])) { $decodedConfig['columnlen'] = array_fill(0, $maxCols, 200); }
                if (empty($decodedConfig['rowlen'])) { $decodedConfig['rowlen'] = array_fill(0, count($decodedData), 30); }

                return [
                    'id' => $sheet->id,
                    'name' => $sheet->name,
                    'data' => $decodedData,
                    'config' => $decodedConfig,
                    'celldata' => $decodedCelldata,
                    'order' => $sheet->order,
                ];
            }

            // Fallback to legacy or sparse rows storage
            $rows = $sheet->rows->map(function ($row) {
                $values = is_array($row->sheet_data)
                    ? $row->sheet_data
                    : (is_string($row->sheet_data) ? (json_decode($row->sheet_data, true) ?: []) : []);
                $formats = is_array($row->cell_formatting)
                    ? $row->cell_formatting
                    : (is_string($row->cell_formatting) ? (json_decode($row->cell_formatting, true) ?: []) : []);

                // If associative (sparse), reconstruct contiguous row from max col index
                $isAssoc = array_keys($values) !== range(0, count($values) - 1);
                if ($isAssoc) {
                    $colIndices = array_map('intval', array_unique(array_merge(array_keys($values), array_keys($formats))));
                    $maxCol = empty($colIndices) ? -1 : max($colIndices);
                    $cells = [];
                    for ($i = 0; $i <= $maxCol; $i++) {
                        $cell = ['v' => isset($values[(string)$i]) ? $values[(string)$i] : ''];
                        if (isset($formats[(string)$i]) && is_array($formats[(string)$i])) {
                            foreach ($formats[(string)$i] as $k => $v) { $cell[$k] = $v; }
                        }
                        $cells[] = $cell;
                    }
                    // Attach the row id on the first cell to keep linkage with DB row
                    if (!empty($cells)) {
                        $cells[0] = array_merge($cells[0], ['rowId' => $row->id]);
                    } else {
                        $cells[] = ['v' => '', 'rowId' => $row->id];
                    }
                    return $cells;
                }

                // Otherwise, dense array of values; still merge any formatting by index if present
                $cells = [];
                $count = count($values);
                for ($i = 0; $i < $count; $i++) {
                    $cell = ['v' => $values[$i] ?? ''];
                    if (isset($formats[$i]) && is_array($formats[$i])) {
                        foreach ($formats[$i] as $k => $v) { $cell[$k] = $v; }
                    }
                    $cells[] = $cell;
                }
                // Attach the row id on the first cell to keep linkage with DB row
                if (!empty($cells)) {
                    $cells[0] = array_merge($cells[0], ['rowId' => $row->id]);
                } else {
                    $cells[] = ['v' => '', 'rowId' => $row->id];
                }
                return $cells;
            })->toArray();

            // Compute column count from widest row
            $maxCols = 0; foreach ($rows as $row) { $maxCols = max($maxCols, is_array($row) ? count($row) : 0); }
            return [
                'id' => $sheet->id,
                'name' => $sheet->name,
                'data' => $rows,
                'config' => [
                    'rowlen' => array_fill(0, count($rows), 30),
                    'columnlen' => array_fill(0, $maxCols, 200),
                ],
                'celldata' => [],
                'order' => $sheet->order,
            ];
        })->toArray();

        return view('file.excel', [
            'file' => $business,
            'sheets' => $sheets
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $file = File::create([
            'name' => $validated['name'],
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'file' => $file,
            'message' => 'File created successfully!',
        ]);
    }

    public function update(Request $request, File $business)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $business->update($validated);

        return response()->json([
            'file' => $business,
            'message' => 'File updated successfully!',
        ]);
    }

    public function edit(File $business)
    {
        return response()->json($business);
    }

    public function destroy(File $business)
    {
        $business->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }

    public function excelPreview()
    {
        $files = File::all(); // Add this line to fix the undefined variable error
        return view('file.preview', compact('files'));
    }
}
        