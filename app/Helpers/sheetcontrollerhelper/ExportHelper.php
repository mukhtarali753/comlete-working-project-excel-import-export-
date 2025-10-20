<?php

namespace App\Helpers\SheetControllerHelper;

use App\Models\File;

class ExportHelper
{
    public static function handle(File $file, string $type = 'xlsx')
    {
        try {
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
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }
}

