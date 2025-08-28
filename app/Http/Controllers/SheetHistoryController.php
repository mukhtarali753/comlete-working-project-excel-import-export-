<?php

namespace App\Http\Controllers;

use App\Models\SheetHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SheetHistoryController extends Controller
{
    public function storeHistory(Request $request)
    {
        Log::info('SheetHistory store request received', [
            'content_type' => $request->header('Content-Type'),
            'file_id' => $request->input('file_id'),
            'changes_count' => is_array($request->input('changes')) ? count($request->input('changes')) : null,
            'db' => DB::connection()->getDatabaseName(),
        ]);

        $validated = $request->validate([
            'file_id' => 'required|integer',
            'changes' => 'required|array',
            'changes.*.cell' => 'nullable|string|max:16',
            'changes.*.change_type' => 'required|string|max:64',
            'changes.*.old_value' => 'nullable',
            'changes.*.new_value' => 'nullable',
        ]);

        $userId = Auth::id();

        $records = [];
        foreach ($validated['changes'] as $change) {
            $records[] = [
                'file_id' => $validated['file_id'],
                'cell' => $change['cell'] ?? null,
                'change_type' => $change['change_type'],
                'old_value' => is_scalar($change['old_value'] ?? null) ? (string)($change['old_value']) : json_encode($change['old_value']),
                'new_value' => is_scalar($change['new_value'] ?? null) ? (string)($change['new_value']) : json_encode($change['new_value']),
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        try {
            if (!empty($records)) {
                SheetHistory::insert($records);
            }
            Log::info('SheetHistory stored', [
                'file_id' => $validated['file_id'],
                'count' => count($records)
            ]);
        } catch (\Throwable $e) {
            Log::error('SheetHistory store failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Failed to store history'], 500);
        }

        return response()->json(['status' => 'ok', 'inserted' => count($records)]);
    }

    public function getHistory($fileId)
    {
        $history = SheetHistory::where('file_id', $fileId)
            ->orderByDesc('created_at')
            ->limit(1000)
            ->get(['cell', 'change_type', 'old_value', 'new_value', 'created_at']);
        Log::info('SheetHistory getHistory', [
            'file_id' => $fileId,
            'count' => $history->count(),
            'db' => DB::connection()->getDatabaseName(),
        ]);
        return response()->json($history);
    }
}


