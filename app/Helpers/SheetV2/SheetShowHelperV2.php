<?php

namespace App\Helpers\SheetV2;

class SheetShowHelperV2
{
   
    public static function transformSheet($sheet): array
    {
       
        if (!empty($sheet->data) || !empty($sheet->celldata) || !empty($sheet->config)) {
            
            return [
                'id' => $sheet->id,
                'name' => $sheet->name,
                'order' => $sheet->order,
                'data' => is_string($sheet->data) ? (json_decode($sheet->data, true) ?: []) : ($sheet->data ?: []),
                'config' => is_string($sheet->config) ? (json_decode($sheet->config, true) ?: ['rowlen' => [], 'columnlen' => []]) : ($sheet->config ?: ['rowlen' => [], 'columnlen' => []]),
                'celldata' => is_string($sheet->celldata) ? (json_decode($sheet->celldata, true) ?: []) : ($sheet->celldata ?: []),
            ];
        }

  
        return self::processLegacySheetData($sheet);
    }

    
    private static function processLegacySheetData($sheet): array
    {
        $rows = $sheet->rows->map(function ($row) {
            return self::processLegacyRow($row);
        })->toArray();

        $maxCols = self::calculateMaxColumns($rows);

        return [
            'id' => $sheet->id,
            'name' => $sheet->name,
            'order' => $sheet->order,
            'data' => $rows,
            'config' => [
                'rowlen' => array_fill(0, count($rows), 30),
                'columnlen' => array_fill(0, $maxCols, 200),
            ],
            'celldata' => [],
        ];
    }

  
    private static function processLegacyRow($row): array
    {
        $values = self::decodeJsonField($row->sheet_data, []);
        $formats = self::decodeJsonField($row->cell_formatting, []);

        $isAssociative = array_keys($values) !== range(0, count($values) - 1);

        if ($isAssociative) {
            return self::processAssociativeRow($values, $formats, $row->id);
        }

        return self::processIndexedRow($values, $formats, $row->id);
    }

   
    private static function processAssociativeRow(array $values, array $formats, $rowId): array
    {
        $colIndices = array_map('intval', array_unique(array_merge(array_keys($values), array_keys($formats))));
        $maxCol = empty($colIndices) ? -1 : max($colIndices);

        $cells = [];
        for ($i = 0; $i <= $maxCol; $i++) {
            $cell = ['v' => $values[(string)$i] ?? ''];

            if (isset($formats[(string)$i]) && is_array($formats[(string)$i])) {
                $cell = array_merge($cell, $formats[(string)$i]);
            }

            $cells[] = $cell;
        }

        return self::attachRowId($cells, $rowId);
    }

   
    private static function processIndexedRow(array $values, array $formats, $rowId): array
    {
        $cells = [];
        $count = count($values);

        for ($i = 0; $i < $count; $i++) {
            $cell = ['v' => $values[$i] ?? ''];

            if (isset($formats[$i]) && is_array($formats[$i])) {
                $cell = array_merge($cell, $formats[$i]);
            }

            $cells[] = $cell;
        }

        return self::attachRowId($cells, $rowId);
    }

  
    private static function attachRowId(array $cells, $rowId): array
    {
        if (empty($cells)) {
            return [['v' => '', 'rowId' => $rowId]];
        }

        $cells[0] = array_merge($cells[0], ['rowId' => $rowId]);
        return $cells;
    }

    private static function calculateMaxColumns(array $data): int
    {
        $maxCols = 0;
        foreach ($data as $row) {
            $maxCols = max($maxCols, is_array($row) ? count($row) : 0);
        }
        return $maxCols;
    }

    private static function decodeJsonField($field, $default = []): array
    {
        if (is_string($field)) {
            $decoded = json_decode($field, true);
            return is_array($decoded) ? $decoded : $default;
        }

        return is_array($field) ? $field : $default;
    }
}

