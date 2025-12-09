<?php

namespace App\Helpers\FileControllerHelper;

use App\Models\File;

class DestroyFileHelper
{
    public static function handle(File $file)
    {
        $file->delete();
        return response()->json(['message' => 'File deleted successfully']);
    }
}










































