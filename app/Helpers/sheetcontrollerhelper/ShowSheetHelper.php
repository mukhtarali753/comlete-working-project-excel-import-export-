<?php

namespace App\Helpers\SheetControllerHelper;

class ShowSheetHelper
{
    /**
     * Transform a sheet and its rows into structured JSON-ready data
     */
    public static function transformSheet($sheet): array
    {
        $rows = $sheet->rows->map(function ($row) {
            // Decode row values & formats
            $values = is_array($row->sheet_data)
                ? $row->sheet_data
                : (is_string($row->sheet_data) ? json_decode($row->sheet_data, true) : []);

            $formats = is_array($row->cell_formatting)
                ? $row->cell_formatting
                : (is_string($row->cell_formatting) ? json_decode($row->cell_formatting, true) : []);

            $values = is_array($values) ? $values : [];
            $formats = is_array($formats) ? $formats : [];

            // Determine maximum column index
            $colIndices = array_map('intval', array_unique(array_merge(array_keys($values), array_keys($formats))));
            $maxCol = empty($colIndices) ? -1 : max($colIndices);

            $cells = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $cell = ['v' => $values[(string) $i] ?? ''];
                if (isset($formats[(string) $i]) && is_array($formats[(string) $i])) {
                    foreach ($formats[(string) $i] as $k => $v) {
                        $cell[$k] = $v;
                    }
                }
                $cells[] = $cell;
            }
            return $cells;
        })->toArray();

        $rowIds = $sheet->rows->pluck('id')->toArray();

        return [
            'id'    => $sheet->id,
            'name'  => $sheet->name,
            'data'  => $rows,
            'rowIds'=> $rowIds,
            'config'=> [
                'rowlen'    => array_fill(0, count($rows), 30),
                'columnlen' => array_fill(0, count($rows[0] ?? []), 200),
            ],
            'order' => $sheet->order,
        ];
    }
}
