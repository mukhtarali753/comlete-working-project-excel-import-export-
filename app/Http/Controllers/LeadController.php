<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Board;
use App\Models\Stage;
use Illuminate\Http\Request;

class LeadController extends Controller
{
// public function index($boardId = null)
// {
//     // dd($boardId->all());
//     if ($boardId) {
//         $board = Board::with(['stages.leads' => function($query) {
//             $query->orderBy('menu_order');
//         }])->findOrFail($boardId);
//         //  dd($board);

//         return view('leads.board-view', [
//             'currentBoard' => $board,
//             'boards' => Board::with(['stages' => function ($query) {
//                 $query->withCount('leads');
//             }])->get(),
//             'stages' => $board->stages()->orderBy('created_at')->get()
            
//         ]);
//     }

//     return view('leads.index', [
//         'leads' => Lead::with(['board', 'stage'])->get(),
//         'boards' => Board::with(['stages' => function ($query) {
//             $query->withCount('leads');
//         }])->get()
//     ]);
// }


  

// public function create()
// {
//     $boards = Board::with('stages.leads')->get();

//     // dd($boards);
//     $selectedBoard = $boards->firstWhere('name', 'khan');

//     return view('leads.create', [
//         'boards' => $boards,
//         'selectedBoard' => $selectedBoard
//     ]);
// }
public function create()
{
    
    $boards = Board::with('stages.leads')->get();
    // dd($boards);
    return view('leads.create', [
        'boards' => $boards
    ]);
    
}

    public function store(Request $request)
    {
        // dd($request->all());
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'board_id' => 'required|exists:boards,id',
            'stage_id' => 'required|exists:stages,id'
        ]);

      
        $validated['menu_order'] = Lead::where('stage_id', $validated['stage_id'])
                                     ->max('menu_order') + 1 ?? 0;

        Lead::create($validated);
        session(['selected_board_id' => $request->board_id]);

        

        return redirect()->route('leads.create')->with('success', 'Lead created successfully!');
       
      
                        

                         

        
                       
    }





   public function edit(Lead $lead)
{
    return view('leads.edit', [
        'lead' => $lead,
        'boards' => Board::all(),
        'stages' => Stage::with('board')->get()
    ]);
}

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'board_id' => 'required|exists:boards,id',
            'stage_id' => 'required|exists:stages,id'
        ]);
        $lead=Lead::findOrFail($id);
        $lead->update($validated);

        return redirect()->route('leads.create', ['board' => $validated['board_id']])
                         ->with('success', 'Lead updated successfully!');
    }

    // public function destroy($id )
    // {
    //     // dd($id);
    //     // $boardId = $lead->board_id;
    //     $lead = Lead::findOrFail($id);
    //     $lead->delete();
        

    //     return redirect()->route('leads.create')->with('success', 'Lead deleted successfully!');
    //     // 
        
                         
    // }

   
    public function destroy($id, Request $request)
{
    $lead = Lead::findOrFail($id);

    
    $boardId = $request->input('board_id');

   
    session(['selected_board_id' => $boardId]);

    
    $lead->delete();

    
    return redirect()->back()->with('success', 'Lead deleted successfully!');
}







    public function updateStage(Request $request)
{
    $lead = Lead::findOrFail($request->lead_id);
    $lead->stage_id = $request->stage_id;
    
    
    $lead->save();
    

    return response()->json(['success' => true]);
}

    
}