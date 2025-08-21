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
       
        return view('
        file.preview', compact('files'));
    }

    public function editSheet(File $business)
    {
        // dd($business);
        $sheets = $business->sheets()->orderBy('order')->get()->map(function ($sheet) {
            // Prefer persisted Luckysheet structure if available (data or celldata or config)
            if (!empty($sheet->data) || !empty($sheet->celldata) || !empty($sheet->config)) {
                $decodedData = is_string($sheet->data) ? json_decode($sheet->data, true) : ($sheet->data ?? []);
                $decodedConfig = is_string($sheet->config) ? json_decode($sheet->config, true) : ($sheet->config ?? ['rowlen' => [], 'columnlen' => []]);
                $decodedCelldata = is_string($sheet->celldata) ? json_decode($sheet->celldata, true) : ($sheet->celldata ?? []);
                return [
                    'id' => $sheet->id,
                    'name' => $sheet->name,
                    'data' => $decodedData,
                    'config' => $decodedConfig,
                    'celldata' => $decodedCelldata,
                    'order' => $sheet->order,
                ];
            }

            // Fallback to legacy rows storage
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
        