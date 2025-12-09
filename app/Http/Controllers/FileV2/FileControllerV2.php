<?php

namespace App\Http\Controllers\FileV2;

use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Controller;
use App\Http\Requests\FileV2\FileRequestV2;
use App\Models\File;
use App\Models\FileShare;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\FileResource;
use Illuminate\Http\Request;

class FileControllerV2 extends Controller
{

    public function index()
    {
        $id = Auth::id();

        $files = File::where('user_id', $id)
            ->orWhereHas('shares', function ($query) use ($id) {
                $query->where('user_id', $id);
            })->orderBy('created_at', 'desc')
            ->with(['user:id,name,email', 'shares.user:id,name,email'])
            ->get();
        
        $users = app(UserController::class)->getuser();
        

        return view('fileV2.preview', compact('files', 'users'));
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
        if ($fileV2->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fileV2->delete();
        return response()->json(['message' => 'File deleted successfully!']);
    }




    public function getShares(File $file)
    {
        $shares = FileShare::where('file_id', $file->id)
            ->where('shared_by', Auth::id())
            ->with('user:id,name,email')
            ->get();

        return response()->json(['shares' => $shares]);
    }


    public function shareFile(Request $request, File $file)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:viewer,editor'
        ]);

        if ($request->user_id == Auth::id()) {
            return response()->json(['message' => 'You cannot share a file with yourself'], 400);
        }

        $targetUser = User::find($request->user_id);

        if (!$targetUser) {
            return response()->json(['message' => 'Target user not found'], 400);
        }

        $existingShare = FileShare::where('file_id', $file->id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingShare) {
            return response()->json(['message' => 'This file is already shared with this user'], 400);
        }

        FileShare::create([
            'file_id' => $file->id,
            'user_id' => $request->user_id,
            'type' => $request->type,
            'shared_by' => Auth::id()
        ]);

        return response()->json(['message' => 'File shared successfully']);
    }


    public function removeShare(FileShare $share)
    {
        if ($share->shared_by != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $share->delete();
        return response()->json(['message' => 'Share removed successfully']);
    }
}
