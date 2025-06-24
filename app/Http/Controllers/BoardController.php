<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Stage;

use Illuminate\Http\Request;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showBoard()
    {
        $boards = Board::with('stages')->get();

        return view('board.index', compact('boards'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     return view('layout.create');
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'name' => 'required|string|max:30',
            'stage_name' => 'required|array|min:1',
            'stage_name.*' => 'required|string|max:30',
            'description' => 'required|array|min:1',
            'description.*' => 'required|string|max:255',
            'emails' => 'required|array|min:1',
            'emails.*' => 'required|email|max:100',
        ]);
         
        // dd($request);



        $board = Board::create([
            'name' => $request->name,
        ]);


        foreach ($request->stage_name as $index => $stageName) {
            Stage::create([
                'name' => $stageName,
                'description' => $request->description[$index],
                'email' => $request->emails[$index],
                'board_id' => $board->id,
            ]);
        }

        // return redirect()->route('showboard')->with('success', 'Board and stages saved!');
        return redirect()->back()->with('success', 'board created');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        // return view('layout.board');

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // dd($id);
        $board = Board::with('stages')->find($id);
// dd($board);
        return view('layout.edit', compact('board'));
    }






    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // dd($id);
        $boards = Board::find($id)->delete();
        // $Boards->delete();
        return redirect()->route('showboard')->with('success', 'Board deleted!');
    }
}
