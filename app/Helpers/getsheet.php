<?php

namespace App\Helpers;

use App\Models\File;
use App\Models\Sheet;

class getsheet
{
    /**
     * Complete editSheet functionality moved from controller
     */
    public static function getsheet(File $file)
    {
        $sheets = $file->sheets()
            ->orderBy('order')
            ->get()
            ->map(function ($sheet) {
                return self::processSheetForEditing($sheet);
            })
            ->toArray();

        return [
            'file' => $file,
            'sheets' => $sheets
        ];
    }

    /**
     * Process sheet data for editing
     */
    private static function processSheetForEditing(Sheet $sheet): array
    {
        if (!empty($sheet->data) || !empty($sheet->celldata) || !empty($sheet->config)) {
            return self::processJsonSheetData($sheet);
        }

        return self::processLegacySheetData($sheet);
    }

    /**
     * Process sheet data stored in JSON format
     */
    private static function processJsonSheetData(Sheet $sheet): array
    {
        $decodedData = self::decodeJsonField($sheet->data, []);
        $decodedConfig = self::decodeJsonField($sheet->config, ['rowlen' => [], 'columnlen' => []]);
        $decodedCelldata = self::decodeJsonField($sheet->celldata, []);

        $normalizedData = self::normalizeDataStructure($decodedData);
        $normalizedConfig = self::normalizeConfigStructure($normalizedData, $decodedConfig);

        return [
            'id' => $sheet->id,
            'name' => $sheet->name,
            'data' => $normalizedData,
            'config' => $normalizedConfig,
            'celldata' => $decodedCelldata,
            'order' => $sheet->order,
        ];
    }

    /**
     * Process legacy sheet data stored in rows
     */
    private static function processLegacySheetData(Sheet $sheet): array
    {
        $rows = $sheet->rows->map(function ($row) {
            return self::processLegacyRow($row);
        })->toArray();

        $maxCols = self::calculateMaxColumns($rows);

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
    }

    /**
     * Process a single legacy row
     */
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

    /**
     * Process associative (sparse) row data
     */
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

    /**
     * Process indexed (dense) row data
     */
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

    /**
     * Attach row ID to the first cell
     */
    private static function attachRowId(array $cells, $rowId): array
    {
        if (empty($cells)) {
            return [['v' => '', 'rowId' => $rowId]];
        }

        $cells[0] = array_merge($cells[0], ['rowId' => $rowId]);
        return $cells;
    }

    /**
     * Normalize data structure
     */
    private static function normalizeDataStructure(array $data): array
    {
        return array_map(function ($row) {
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
        }, $data);
    }

    /**
     * Normalize config structure
     */
    private static function normalizeConfigStructure(array $data, array $config): array
    {
        if (!is_array($config)) {
            $config = ['rowlen' => [], 'columnlen' => []];
        }

        $maxCols = self::calculateMaxColumns($data);

        if (empty($config['columnlen'])) {
            $config['columnlen'] = array_fill(0, $maxCols, 200);
        }

        if (empty($config['rowlen'])) {
            $config['rowlen'] = array_fill(0, count($data), 30);
        }

        return $config;
    }

    /**
     * Calculate maximum columns in data
     */
    private static function calculateMaxColumns(array $data): int
    {
        $maxCols = 0;
        foreach ($data as $row) {
            $maxCols = max($maxCols, is_array($row) ? count($row) : 0);
        }
        return $maxCols;
    }

    /**
     * Decode JSON field with fallback
     */
    private static function decodeJsonField($field, $default = []): array
    {
        if (is_string($field)) {
            $decoded = json_decode($field, true);
            return is_array($decoded) ? $decoded : $default;
        }

        return is_array($field) ? $field : $default;
    }
}
