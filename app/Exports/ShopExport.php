<?php

namespace App\Exports;

use App\Models\Shop;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShopsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Shop::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Shop Name',
            'Address',
            'Contact Email',
            'Phone',
            'Is Active'
        ];
    }

    public function map($shop): array
    {
        return [
            $shop->id,
            $shop->name,
            $shop->address,
            $shop->contact_email,
            $shop->phone,
            $shop->is_active ? 'Yes' : 'No'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Set column widths
            'A' => ['width' => 10],
            'B' => ['width' => 25],
            'C' => ['width' => 30],
            'D' => ['width' => 25],
            'E' => ['width' => 20],
            'F' => ['width' => 15],
        ];
    }
}