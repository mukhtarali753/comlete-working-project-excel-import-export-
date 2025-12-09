<?php

namespace App\Helpers\FileV2;

use App\Models\File;

class FileEditHelperV2
{
    public static function handle(File $file)
    {
        return response()->json($file);
    }
}































