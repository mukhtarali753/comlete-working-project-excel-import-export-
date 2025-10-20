<?php

namespace App\Http\Controllers\SheetV2;
use App\Http\Requests\SheetV2\SaveSheetsRequestV2;
use App\Http\Requests\SheetV2\SheetImportRequestV2;
use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use App\Models\SheetRowVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\SheetV2\SheetSaveHelperV2;
use App\Helpers\SheetV2\SheetDeleteHelperV2;
use App\Helpers\SheetV2\SheetImportHelperV2;
use App\Helpers\SheetV2\SheetVersionRestoreHelperV2;
use App\Helpers\SheetV2\SheetVersionHistoryHelperV2;
use App\Helpers\SheetV2\SheetShowHelperV2;
use App\Http\Controllers\Controller;



class SheetControllerV2 extends Controller
{

    public function index($fileId = null)
    {
        if ($fileId) {
            $file = File::findOrFail($fileId);

            $sheets = $file->sheets()
                ->where('is_current', 1)
                ->orderBy('order')
                ->with('rows')
                ->get();

            $transformedSheets = [];
            foreach ($sheets as $sheet) {
                $transformedSheets[] = SheetShowHelperV2::transformSheet($sheet);
            }

            return view('sheetV2.excel', [
                'file' => $file,
                'sheets' => $transformedSheets,
            ]);
        }

        $files = File::all();
        return view('fileV2.preview', [
            'files' => $files,
        ]);
    }


    public function saveSheets(SaveSheetsRequestV2 $request)
    {
        return SheetSaveHelperV2::handle($request->validated());
    }




    public function deleteSheet($id)
    {
        $result = SheetDeleteHelperV2::deleteSheet(
            $id,
            fn($name) => $this->getBaseName($name),
            fn($fileId, $baseName, $version) => $this->generateUniqueVersionedName($fileId, $baseName, $version)
        );

        if ($result['success']) {
            return response()->json([
                'message' => 'Sheet removed in new file version.',
                'file_id' => $result['file_id'],
                'version' => $result['version'],
            ], 200);
        }

        return response()->json([
            'message' => 'Failed to delete sheet: ' . $result['error'],
        ], 500);
    }

    public function importExcel(SheetImportRequestV2 $request)
    {
        return SheetImportHelperV2::handle($request);
    }

    public function export(File $file, $type = 'xlsx')
    {
        $fileName = $file->name . '.' . $type;

        $sheets = $file->sheets()->orderBy('order')->get();

        $exportData = [];
        foreach ($sheets as $sheet) {
            $rows = $sheet->rows->map(function ($row) {
                return $row->sheet_data;
            })->toArray();

            $exportData[$sheet->name] = $rows;
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new class($exportData) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function sheets(): array
            {
                $sheets = [];
                foreach ($this->data as $sheetName => $rows) {
                    $sheets[$sheetName] = new class($rows) implements \Maatwebsite\Excel\Concerns\ToArray {
                        private $rows;

                        public function __construct($rows)
                        {
                            $this->rows = $rows;
                        }

                        public function array(array $array)
                        {
                            return $this->rows;
                        }
                    };
                }
                return $sheets;
            }
        }, $fileName);
    }


    public function restoreSheetVersion($sheetId, $versionNumber)
    {
        return SheetVersionRestoreHelperV2::handle($sheetId, $versionNumber);
    }

    public function getSheetVersionHistory($sheetId)
    {
        return SheetVersionHistoryHelperV2::handle($sheetId);
    }
}
