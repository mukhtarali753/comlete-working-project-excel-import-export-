<?php

namespace App\Helpers\FileControllerHelper;

use App\Models\File;

class PreviewHelper
{
    public static function preview()
    {
        $files = File::all();
        return view('file.preview', compact('files'));
    }

    public static function excelPreview()
    {
        $files = File::all();
        return view('file.preview', compact('files'));
    }
}


























