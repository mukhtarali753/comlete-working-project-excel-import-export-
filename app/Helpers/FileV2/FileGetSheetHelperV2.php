<?php

namespace App\Helpers\FileV2;

use App\Models\File;
use App\Helpers\getsheet;

class FileGetSheetHelperV2
{
    public static function handle(File $file)
    {
        $data = getsheet::getsheet($file);
        return view('fileV2.excel', $data);
    }
}



