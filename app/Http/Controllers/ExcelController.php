<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;

class ExcelController extends Controller
{
   

    public function previewAsTable()
    {
        $path = public_path('Book3.xlsx');

        if (!File::exists($path)) {
            return back()->with('error', 'Book3.xlsx not found in public folder.');
        }

        $data = Excel::toArray([], $path);
        $rows = $data[0] ?? [];
        //   dd($rows);

        return view('excel.preview-table', [
            'filename' => 'Book3.xlsx',
            'rows' => $rows


           
        ]);
        
    }

    public function previewAsEmbed()
    {
        $filePath = public_path('Book3.xlsx');

        if (!File::exists($filePath)) {
            return back()->with('error', 'Book3.xlsx not found in public folder.');
        }

        return view('excel.preview-fixed');
    }
}


