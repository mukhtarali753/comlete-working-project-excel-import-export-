<?php

namespace App\Helpers\FileControllerHelper;

use App\Models\File;
use Illuminate\Support\Facades\Auth;

class StoreFileHelper
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


































