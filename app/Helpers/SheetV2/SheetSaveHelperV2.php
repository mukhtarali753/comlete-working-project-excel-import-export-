<?php

namespace App\Helpers\SheetV2;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use App\Models\SheetRowVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SheetSaveHelperV2
{
    public static function handle(array $data)
    {
        // ---------- fast mode (simple upsert) ----------
        if (!empty($data['simple_upsert']) && (bool)$data['simple_upsert'] === true) {
            return DB::transaction(function () use ($data) {
                $file = $data['file_id']
                    ? File::findOrFail($data['file_id'])
                    : File::create(['name' => $data['name'], 'user_id' => Auth::id()]);

                $saved = [];
                foreach ($data['sheets'] as $s) {
                    $payload = [
                        'file_id'   => $file->id,
                        'name'      => $s['name'],
                        'order'     => $s['order'] ?? 0,
                        'data'      => json_encode($s['data'] ?? []),
                        'config'    => json_encode($s['config'] ?? ['rowlen' => [], 'columnlen' => []]),
                        'celldata'  => json_encode($s['celldata'] ?? []),
                        'is_current'=> 1,
                    ];

                    if (!empty($s['id'])) {
                        $sheet = Sheet::where('id', $s['id'])->where('file_id', $file->id)->first();
                        if ($sheet) {
                            $sheet->update($payload);
                        } else {
                            $sheet = Sheet::where('file_id', $file->id)->where('name', $s['name'])->first();
                            $sheet ? $sheet->update($payload) : $sheet = Sheet::create($payload);
                        }
                    } else {
                        $sheet = Sheet::where('file_id', $file->id)->where('name', $s['name'])->first();
                        $sheet ? $sheet->update($payload) : $sheet = Sheet::create($payload);
                    }

                    $baseName = self::getBaseName($payload['name']);
                    Sheet::where('file_id', $file->id)
                        ->where(function ($q) use ($baseName) {
                            $q->where('name', $baseName)
                              ->orWhere('name', 'LIKE', $baseName . '\\_v%');
                        })
                        ->where('id', '!=', $sheet->id)
                        ->update(['is_current' => 0]);

                    $saved[] = SheetShowHelperV2::transformSheet($sheet);
                }

                usort($saved, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));
                return response()->json(['file_id' => $file->id, 'sheets' => $saved]);
            });
        }

        // ---------- versioned save ----------
        return DB::transaction(function () use ($data) {
            $isNewFileCreation = empty($data['file_id']);
            $file = $isNewFileCreation
                ? File::create(['name' => $data['name'], 'user_id' => Auth::check() ? Auth::id() : null])
                : File::findOrFail($data['file_id']);

            foreach ($data['sheets'] ?? [] as $index => $sheetData) {
                $rules = Sheet::getBasicValidationRules();
                $rules['data'] = 'required|string';
                $validator = Validator::make($sheetData, $rules);
                if ($validator->fails()) {
                    throw new \Exception('Validation failed for sheet: ' . $validator->errors()->first());
                }

                if ($isNewFileCreation) {
                    self::createNewSheet($file, $sheetData, $index);
                }
            }

            if (!$isNewFileCreation) {
                self::versionedUpdate($file, $data);
            }

            if ($isNewFileCreation) {
                Sheet::where('file_id', $file->id)->whereDoesntHave('rows')->delete();
            }

            return response()->json([
                'message' => 'File snapshot saved successfully.',
                'file_id' => $file->id,
                'sheets'  => $file->sheets()->where('is_current', 1)->orderBy('order')->with('rows')->get()
                    ->map(fn($s) => SheetShowHelperV2::transformSheet($s))->values(),
            ], 200);
        });
    }

    /* ----------------------------------------------------------- */
    private static function createNewSheet(File $file, array $sheetData, int $index): void
    {
        $baseName = $sheetData['name'] ?: ('Sheet' . ($index + 1));
        $uniqueName = $baseName;
        $counter = 1;
        while (Sheet::where('file_id', $file->id)->where('name', $uniqueName)->exists()) {
            $uniqueName = $baseName . ' (' . $counter . ')';
            $counter++;
        }

        $sheet = Sheet::create([
            'file_id'   => $file->id,
            'name'      => $uniqueName,
            'order'     => $sheetData['order'] ?? $index,
            'version'   => 1,
            'is_current'=> 1,
        ]);

        $rows2D = json_decode($sheetData['data'], true) ?: [];
        $insertRows = [];
        foreach ($rows2D as $rowIndex => $row) {
            if (!is_array($row)) {
                continue;
            }
            $cleanRow = [];
            $hasContent = false;
            foreach ($row as $cell) {
                $value = is_array($cell) && array_key_exists('v', $cell) ? trim((string)$cell['v']) : (is_string($cell) ? trim($cell) : '');
                $cleanRow[] = $value;
                if ($value !== '') {
                    $hasContent = true;
                }
            }
            if ($hasContent || $rowIndex === 0) {
                $insertRows[] = [
                    'sheet_id'   => $sheet->id,
                    'sheet_data' => json_encode($cleanRow),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        if (!empty($insertRows)) {
            SheetRow::insert($insertRows);
        }
    }

    /* ----------------------------------------------------------- */
    private static function versionedUpdate(File $file, array $data): void
    {
        $enableHistory = $data['enable_version_history'] ?? false;
        $nextVer = Sheet::where('file_id', $file->id)->max('version') + 1 ?: 1;

        $current = Sheet::where('file_id', $file->id)->where('is_current', 1)->get()->keyBy(fn($s) => self::getBaseName($s->name));
        $incoming = collect($data['sheets'])->keyBy(fn($s) => self::getBaseName($s['name']));

        Sheet::where('file_id', $file->id)->update(['is_current' => 0]);

        foreach ($incoming as $baseName => $in) {
            $old = $current->get($baseName);

            if ($enableHistory && $old) {
                self::createVersionHistoryForSheet($old);
            }

            $newSheet = Sheet::create([
                'file_id'   => $file->id,
                'name'      => self::generateUniqueVersionedName($file->id, $baseName, $nextVer),
                'order'     => $in['order'] ?? $old->order ?? 0,
                'data'      => json_encode($in['data']  ?? $old->data  ?? []),
                'config'    => json_encode($in['config']?? $old->config ?? []),
                'celldata'  => json_encode($in['celldata']??$old->celldata??[]),
                'version'   => $nextVer,
                'is_current'=> 1,
            ]);

            if (isset($in['data'])) {
                SheetRow::where('sheet_id', $newSheet->id)->delete();
                $rows = json_decode($in['data'], true) ?: [];
                $chunks = array_chunk($rows, 50, true);
                foreach ($chunks as $idx => $chunk) {
                    self::processRowChunk($newSheet, $chunk, $idx * 50);
                }
                if (!empty($in['rowUpdates'])) {
                    self::applyRowUpdates($newSheet, $in['rowUpdates'], $enableHistory);
                }
            } else {
                self::copyRows($old, $newSheet);
            }
        }
    }

    /* ----------------------------------------------------------- */
    private static function processRowChunk(Sheet $sheet, array $rows, int $start): void
    {
        $insert = [];
        foreach ($rows as $rIdx => $row) {
            if (!is_array($row)) {
                continue;
            }
            $clean = [];
            $fmt = [];
            $empty = true;
            foreach ($row as $cIdx => $cell) {
                $v = is_array($cell) && array_key_exists('v', $cell) ? trim((string)$cell['v']) : '';
                if ($v !== '') {
                    $clean[(string)$cIdx] = $v;
                    $empty = false;
                }
                if (is_array($cell)) {
                    $f = [];
                    foreach (['ct', 'bg', 'fc', 'bl', 'it', 'un', 'ff', 'fs', 'ht', 'vt', 'tb', 'tr'] as $k) {
                        if (isset($cell[$k])) {
                            $f[$k] = $cell[$k];
                        }
                    }
                    if ($f) {
                        $fmt[(string)$cIdx] = $f;
                    }
                }
            }
            if ($empty && $start + $rIdx !== 0) {
                continue;
            }
            $insert[] = [
                'sheet_id'        => $sheet->id,
                'sheet_data'      => json_encode($clean),
                'cell_formatting' => $fmt ? json_encode($fmt) : null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }
        if ($insert) {
            SheetRow::insert($insert);
        }
    }

    /* ----------------------------------------------------------- */
    private static function applyRowUpdates(Sheet $sheet, array $updates, bool $history): void
    {
        foreach ($updates as $u) {
            $rowId = $u['rowId'] ?? null;
            $data  = $u['data']   ?? [];
            if (!is_array($data)) {
                continue;
            }
            $clean = [];
            $fmt   = [];
            foreach ($data as $cIdx => $cell) {
                $v = is_array($cell) && array_key_exists('v', $cell) ? trim((string)$cell['v']) : (is_string($cell) ? trim($cell) : '');
                if ($v !== '') {
                    $clean[(string)$cIdx] = $v;
                }
                if (is_array($cell)) {
                    $f = [];
                    foreach (['ct', 'bg', 'fc', 'bl', 'it', 'un', 'ff', 'fs', 'ht', 'vt', 'tb', 'tr'] as $k) {
                        if (isset($cell[$k])) {
                            $f[$k] = $cell[$k];
                        }
                    }
                    if ($f) {
                        $fmt[(string)$cIdx] = $f;
                    }
                }
            }

            if ($rowId) {
                $row = SheetRow::where('sheet_id', $sheet->id)->where('id', $rowId)->first();
                if ($row) {
                    if ($history) {
                        SheetRowVersion::create([
                            'sheet_row_id'   => $row->id,
                            'sheet_id'       => $sheet->id,
                            'sheet_data'     => is_string($row->sheet_data) ? $row->sheet_data : json_encode($row->sheet_data),
                            'cell_formatting'=> $row->cell_formatting ? (is_string($row->cell_formatting) ? $row->cell_formatting : json_encode($row->cell_formatting)) : null,
                            'version_number' => $sheet->version ?? 1,
                            'created_at'     => $row->updated_at ?? now(),
                        ]);
                    }
                    $row->update([
                        'sheet_data'      => json_encode($clean),
                        'cell_formatting' => $fmt ? json_encode($fmt) : null,
                    ]);
                    continue;
                }
            }
            SheetRow::create([
                'sheet_id'        => $sheet->id,
                'sheet_data'      => json_encode($clean),
                'cell_formatting' => $fmt ? json_encode($fmt) : null,
            ]);
        }
    }

    /* ----------------------------------------------------------- */
    private static function copyRows(?Sheet $old, Sheet $newSheet): void
    {
        if (!$old) {
            return;
        }
        $rows = SheetRow::where('sheet_id', $old->id)->get(['sheet_data', 'cell_formatting']);
        if ($rows->isEmpty()) {
            return;
        }
        $bulk = [];
        foreach ($rows as $r) {
            $bulk[] = [
                'sheet_id'        => $newSheet->id,
                'sheet_data'      => is_string($r->sheet_data)   ? $r->sheet_data   : json_encode($r->sheet_data   ?? []),
                'cell_formatting' => is_string($r->cell_formatting) ? $r->cell_formatting : json_encode($r->cell_formatting ?? null),
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }
        SheetRow::insert($bulk);
    }

    /* ----------------------------------------------------------- */
    private static function createVersionHistoryForSheet(Sheet $sheet): void
    {
        $rows = $sheet->rows()->get(['id', 'sheet_data', 'cell_formatting', 'updated_at']);
        if ($rows->isEmpty()) {
            return;
        }
        $next = (SheetRowVersion::where('sheet_id', $sheet->id)->max('version_number') ?: 0) + 1;
        $bulk = [];
        foreach ($rows as $r) {
            $bulk[] = [
                'sheet_row_id'   => $r->id,
                'sheet_id'       => $sheet->id,
                'sheet_data'     => is_string($r->sheet_data) ? $r->sheet_data : json_encode($r->sheet_data),
                'cell_formatting'=> $r->cell_formatting ? (is_string($r->cell_formatting) ? $r->cell_formatting : json_encode($r->cell_formatting)) : null,
                'version_number' => $next,
                'created_at'     => $r->updated_at,
            ];
        }
        SheetRowVersion::insert($bulk);
    }

    /* ----------------------------------------------------------- */
    private static function getBaseName(string $name): string
    {
        return preg_match('/^(.*)_v\d+$/', $name, $m) ? $m[1] : $name;
    }

    private static function generateVersionedName(string $baseName, int $version): string
    {
        return $version <= 1 ? $baseName : $baseName . '_v' . $version;
    }

    private static function generateUniqueVersionedName(int $fileId, string $baseName, int $startVersion): string
    {
        $version = max(1, $startVersion);
        do {
            $candidate = self::generateVersionedName($baseName, $version);
            if (!Sheet::where('file_id', $fileId)->where('name', $candidate)->exists()) {
                return $candidate;
            }
            $version++;
        } while (true);
    }
}