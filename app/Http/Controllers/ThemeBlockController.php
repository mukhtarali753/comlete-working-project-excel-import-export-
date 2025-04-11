<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use App\Models\ThemeBlock;


class ThemeBlockController extends Controller
{
    public function index()
    {
    }

    public function create($themeId)
    {
        return view('themes.create_theme_block', compact('themeId'));
    }

    // public function store(Request $request)
    // {
       
    
    // }
    public function storeBlock(Request $request, $themeId)
    
    {
        // dd($themeId);
        // $request->validate([
        //     'title' => 'required|string|max:255',
        //     'description' => 'nullable|string',
        // ]);

        // ThemeBlock::create([
        //     'theme_id' => $themeId,
        //     'title' => $request->title,
        //     'description' => $request->description,
        // ]);

        // return redirect()->route('themes.show', $themeId)->with('success', 'Block created successfully.');
    }


    public function show($id)
    {
    }

    public function edit($id)
    {
    }

    public function update(Request $request, $id)

    {

        


    }

    public function destroy($id)

    {

    }

    
}
