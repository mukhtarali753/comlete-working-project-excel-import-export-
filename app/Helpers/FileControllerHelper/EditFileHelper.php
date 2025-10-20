<?php

namespace App\Helpers\FileControllerHelper;

use App\Models\File;

class EditFileHelper
{
    public static function handle(File $file)
    {
        return response()->json($file);
    }
}


























