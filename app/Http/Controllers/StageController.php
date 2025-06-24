<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Stage;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stage  $stage
     * @return \Illuminate\Http\Response
     */
    public function show(Stage $stage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stage  $stage
     * @return \Illuminate\Http\Response
     */
//     public function edit($id)
// {
//     $stage = Stage::findOrFail($id);
//     return view('layout.edit', compact('stage'));
// }

 


public function update(Request $request, $boardId)
{


  
    $validated = $request->validate([
        'board_name' => 'required|string|max:255',
        'stages.*.id' => 'sometimes|nullable|integer|exists:stages,id',
        'stages.*.name' => 'required|string|max:255',
        'stages.*.description' => 'required|string|max:255', 
        'stages.*.email' => 'required|email',
    ]);

    $board = Board::with('stages')->findOrFail($boardId);
    $board->name = $validated['board_name'];
    $board->save();

    $existingStageIds = [];

    foreach ($validated['stages'] as $stageData) {
        if (!empty($stageData['id'])) {
            
            $stage = $board->stages()->find($stageData['id']);
            if ($stage) {
                $stage->update($stageData);
                $existingStageIds[] = $stage->id;
            }
        } else {
           
            $newStage = $board->stages()->create($stageData);
            $existingStageIds[] = $newStage->id;
        }
    }

    $board->stages()
        ->whereNotIn('id', $existingStageIds)
        ->delete();

    return redirect()->back()->with('success', 'Board and stages updated.');
}



public function getStageRow(Request $request)
{
    $index = $request->input('index');
    return view('layout.update-stage-row', [
        'index' => $index,
        'stage' => null 
    ]);
}



    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stage  $stage
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stage $stage)
    {
        //
    }
}
