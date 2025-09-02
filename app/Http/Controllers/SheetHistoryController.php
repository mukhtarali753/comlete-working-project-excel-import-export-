<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SheetHistoryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:files,id',
            'sheet_id' => 'required|exists:sheets,id',
        ]);

        $histories = SheetHistory::with('user:id,name')
            ->where('file_id', $request->file_id)
            ->where('sheet_id', $request->sheet_id)
            ->orderByDesc('version_number')
            ->get(['id','file_id','sheet_id','version_number','is_current','user_id','created_at']);

        return response()->json(['histories' => $histories]);
    }

    public function show(SheetHistory $history)
    {
        return response()->json([
            'history' => [
                'id' => $history->id,
                'file_id' => $history->file_id,
                'sheet_id' => $history->sheet_id,
                'version_number' => $history->version_number,
                'is_current' => $history->is_current,
                'data' => $history->data,
                'user_id' => $history->user_id,
                'created_at' => $history->created_at,
            ]
        ]);
    }

    public function restore(Request $request)
    {
        $data = $request->validate([
            'history_id' => 'required|exists:sheet_histories,id',
        ]);

        $history = SheetHistory::findOrFail($data['history_id']);

        DB::transaction(function () use ($history) {
            // Next version number
            $latest = SheetHistory::where('file_id', $history->file_id)
                ->where('sheet_id', $history->sheet_id)
                ->orderByDesc('version_number')
                ->first();
            $nextVersion = $latest ? $latest->version_number + 1 : 1;

            // Mark previous current as false
            SheetHistory::where('file_id', $history->file_id)
                ->where('sheet_id', $history->sheet_id)
                ->where('is_current', true)
                ->update(['is_current' => false]);

            // Insert new current as a copy of selected version
            SheetHistory::create([
                'file_id' => $history->file_id,
                'sheet_id' => $history->sheet_id,
                'version_number' => $nextVersion,
                'is_current' => true,
                'data' => $history->data,
                'user_id' => Auth::check() ? Auth::id() : null,
            ]);
        });

        return response()->json(['message' => 'Version restored as new current.']);
    }
}


