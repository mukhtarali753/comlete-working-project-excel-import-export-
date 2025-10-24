<?php

namespace App\Helpers\FileControllerHelper;

use App\Models\File;

class UpdateFileHelper
{
    public static function handle(File $file, array $data)
    {
        $file->update($data);

        return response()->json([
            'file' => $file,
            'message' => 'File updated successfully!',
        ]);
    }
}


































