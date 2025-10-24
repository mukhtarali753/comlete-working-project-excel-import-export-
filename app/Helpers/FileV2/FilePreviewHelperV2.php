<?php

namespace App\Helpers\FileV2;

use App\Models\File;

class FilePreviewHelperV2
{
    public static function preview()
    {
        $files = File::all();
        return view('fileV2.preview', compact('files'));
    }

    public static function excelPreview()
    {
        // dd('test');
        $files = File::all();
        return view('fileV2.preview', compact('files'));
    }
}























