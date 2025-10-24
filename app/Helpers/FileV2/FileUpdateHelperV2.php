<?php

namespace App\Helpers\FileV2;

use App\Models\File;

class FileUpdateHelperV2
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























