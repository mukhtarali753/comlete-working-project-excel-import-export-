<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSheetsRequest;
use App\Http\Requests\UpdateSheetRequest;
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
use App\Helpers\sheetcontrollerhelper\SaveSheet;
use App\Helpers\SheetControllerHelper\UpdateSheetHelper;
use App\Helpers\SheetControllerHelper\ShowSheetHelper;
use App\Helpers\SheetControllerHelper\DeleteSheetHelper;
use App\Helpers\SheetControllerHelper\ImportExcelHelper;
use App\Helpers\SheetControllerHelper\ExportHelper;
use App\Helpers\SheetControllerHelper\RestoreSheetVersionHelper;
use App\Helpers\SheetControllerHelper\RowVersionHistoryHelper;
use App\Helpers\SheetControllerHelper\DebugSheetVersionsHelper;
use App\Helpers\SheetControllerHelper\SheetVersionHistoryHelper;
use App\Helpers\SheetControllerHelper\RestoreRowVersionHelper;


class SheetController extends Controller
{
    public function index()
    {

        $files = File::all();
        return view('file.preview', compact('files'));
    }




    public function saveSheets(SaveSheetsRequest $request)
    {
        return SheetSaveHelperV2::handle($request->validated());
    }



    private function updateExistingSheet(Sheet $existingSheet, array $sheetData, bool $enableVersionHistory): Sheet
    {
        return UpdateSheetHelper::updateExistingSheet(
            $existingSheet,
            $sheetData,
            $enableVersionHistory,
            fn($name) => $this->getBaseName($name),
            fn($sheet) => $this->createVersionHistoryForSheet($sheet),
            fn($fileId, $baseName) => $this->getNextVersion($fileId, $baseName),
            fn($fileId, $baseName, $nextVersion) => $this->generateUniqueVersionedName($fileId, $baseName, $nextVersion)
        );
    }


    public function show(File $file)
    {
        $sheets = $file->sheets()->where('is_current', 1) ->orderBy('order')->get()
            
->map(fn($sheet) => ShowSheetHelper::transformSheet($sheet))
            ->toArray();
        return response()->json([
            'file' => $file,
            'sheets' => $sheets,
        ]);
    }

    public function listFiles()
    {
        $files = File::select('id', 'name')
            ->withCount('sheets')
            ->get();

        return response()->json(['files' => $files]);
    }

    public function getSheets(Request $request)
    {
        $fileId = $request->query('file_id');
        if (!$fileId) {
            return response()->json([]);
        }


        $sheets = Sheet::where('file_id', $fileId)
            ->where('is_current', 1)
            ->orderBy('order')
            ->get()
            ->map(fn($sheet) => $this->mapSheetForResponse($sheet))
            ->values();

        return response()->json($sheets);
    }

    public function getSheetsByFile($id)
    {
        $file = File::findOrFail($id);
        $sheets = $file->sheets()->where('is_current', 1)->orderBy('order')->get(['id', 'name', 'order']);
        return response()->json($sheets);
    }

    public function deleteSheet($id)
    {
        $result = DeleteSheetHelper::deleteSheet(
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

    public function importExcel(Request $request)
    {
        return ImportExcelHelper::handle($request);
    }

    
   

    public function export(File $file, $type = 'xlsx')
    {
        return ExportHelper::handle($file, $type);
    }

    
    public function restoreSheetVersion($sheetId, $versionNumber)
    {
        return RestoreSheetVersionHelper::handle($sheetId, $versionNumber);
    }

    
    public function getRowVersionHistory($rowId)
    {
        return RowVersionHistoryHelper::handle($rowId);
    }

   
    public function debugSheetVersions($sheetId)
    {
        return DebugSheetVersionsHelper::handle($sheetId);
    }

   
    public function getSheetVersionHistory($sheetId)
    {
        return SheetVersionHistoryHelper::handle($sheetId);
    }

    
    private function ensureSingleCurrentVersion($fileId, $sheetName, $excludeSheetId = null)
    {
        // Match lineage by base name prefix to handle versioned names like MySheet_v2
        $baseName = $this->getBaseName($sheetName);
        $query = $this->lineageQuery($fileId, $baseName);

        if ($excludeSheetId) {
            $query->where('id', '!=', $excludeSheetId);
        }

        $query->update(['is_current' => 0]);
    }

    /**
     * Build a lineage query that matches all versions of a base sheet name.
     */
    private function lineageQuery($fileId, $baseName)
    {
        return Sheet::where('file_id', $fileId)
            ->where(function ($q) use ($baseName) {
                $q->where('name', $baseName)
                    ->orWhere('name', 'LIKE', $baseName . '\\_v%');
            });
    }

    private function mapSheetForResponse(Sheet $sheet): array
    {
        return [
            'id' => $sheet->id,
            'name' => $sheet->name,
            'order' => $sheet->order,
            'data' => is_string($sheet->data) ? (json_decode($sheet->data, true) ?: []) : ($sheet->data ?: []),
            'config' => is_string($sheet->config) ? (json_decode($sheet->config, true) ?: ['rowlen' => [], 'columnlen' => []]) : ($sheet->config ?: ['rowlen' => [], 'columnlen' => []]),
            'celldata' => is_string($sheet->celldata) ? (json_decode($sheet->celldata, true) ?: []) : ($sheet->celldata ?: []),
        ];
    }

   
    private function getNextVersion($fileId, $baseName)
    {
        // 1) Start from version column
        $query = $this->lineageQuery($fileId, $baseName);
        $maxVersionByColumn = $query->max('version');

        // 2) Also consider suffixes in names like Base_vN for legacy rows with null version
        $names = $this->lineageQuery($fileId, $baseName)->pluck('name')->toArray();
        $maxVersionByName = 0;
        foreach ($names as $name) {
            if ($name === $baseName) {
                $maxVersionByName = max($maxVersionByName, 1);
                continue;
            }
            if (preg_match('/^' . preg_quote($baseName, '/') . '_v(\d+)$/', $name, $m)) {
                $maxVersionByName = max($maxVersionByName, (int)$m[1]);
            }
        }

        $maxVersion = max((int)($maxVersionByColumn ?? 0), (int)$maxVersionByName);
        if ($maxVersion < 1) {
            // No versions found at all
            return 1;
        }
        return $maxVersion + 1;
    }


    private function getBaseName($name)
    {
        if (preg_match('/^(.*)_v\\d+$/', $name, $matches)) {
            return $matches[1];
        }
        return $name;
    }


    private function generateVersionedName($baseName, $version)
    {
        if ((int)$version <= 1) {
            return $baseName; // first version keeps base name
        }
        return $baseName . '_v' . (int)$version;
    }


    private function generateUniqueVersionedName(int $fileId, string $baseName, int $startVersion): string
    {
        $version = max(1, (int)$startVersion);
        do {
            $candidate = $this->generateVersionedName($baseName, $version);
            $exists = Sheet::where('file_id', $fileId)->where('name', $candidate)->exists();
            if (!$exists) {
                return $candidate;
            }
            $version++;
        } while (true);
    }


    //   Restore a previous version

    public function restoreRowVersion($rowId, $versionNumber)
    {
        return RestoreRowVersionHelper::handle($rowId, $versionNumber);
    }
}
