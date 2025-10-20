<?php

namespace App\Helpers\FileV2;

use App\Models\File;
use Illuminate\Support\Facades\Auth;

class FileStoreHelperV2
{
    public static function handle(array $data)
    {
       
        $data['user_id'] = Auth::id();
        $file = File::create($data);

        return response()->json([
            'file' => $file,
            'message' => 'File created successfully!',
        ]);
    }
}















