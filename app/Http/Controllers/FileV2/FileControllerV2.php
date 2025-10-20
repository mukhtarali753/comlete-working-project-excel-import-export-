<?php

namespace App\Http\Controllers\FileV2;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileV2\FileRequestV2;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FileResource;

class FileControllerV2 extends Controller
{
    public function index()
    {
        $file = FileResource::collection((File::all()));
        return view('fileV2.preview', ['files' => $file]);

    }

    public function store(FileRequestV2 $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $file = File::create($data);
        return new FileResource($file);

    }

    public function update(FileRequestV2 $request, File $fileV2)
    {
        $fileV2->update($request->validated());
        return new FileResource($fileV2);

    }

    public function edit(File $fileV2)
    {
        return response()->json([
            'id' => $fileV2->id,
            'name' => $fileV2->name,
        ]);
    }

    public function destroy(File $fileV2)
    {
        $fileV2->delete();
        return response()->json(['message' => 'File deleted successfully!']);
    }
}

    