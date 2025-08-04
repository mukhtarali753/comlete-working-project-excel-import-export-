<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StudentsExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Student::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Date of Birth',
            'Age',
            'Address',
            'Course',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply conditional formatting to the "Age" column (E)
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_CELLIS);
        $conditional->setOperatorType(Conditional::OPERATOR_LESSTHAN);
        $conditional->addCondition('18');
        $conditional->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000'); // Red background

        // Apply conditional formatting to the "Age" column (E2:E<last_row>)
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('E2:E' . $lastRow)->setConditionalStyles([$conditional]);

        // Optional: Style the header row
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F0F0']]],
        ];
    }
}