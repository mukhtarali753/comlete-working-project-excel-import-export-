<?php

namespace App\Helpers\FileControllerHelper;

use App\Models\File;
use App\Helpers\getsheet;

class GetSheetHelper
{
    public static function handle(File $file)
    {
        $data = getsheet::getsheet($file);
        return view('file.excel', $data);
    }
}














