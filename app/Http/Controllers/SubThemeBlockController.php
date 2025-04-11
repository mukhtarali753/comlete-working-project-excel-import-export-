<?php

namespace App\Http\Controllers;

use App\Models\SubTheme;
use App\Models\SubThemeBlock;
use Illuminate\Http\Request;

class SubThemeBlockController extends Controller
{
    public function index()
    {
        $subThemeBlocks = SubThemeBlock::all();
        return view('subthemeblock.index', compact('subThemeBlocks'));
    }

    public function create($subThemeId)
    {
        // dd($subThemeId);
        return view('subthemeblock.create', compact('subThemeId'));  
    }

    public function store(Request $request)
    {
        
        $data = $request->validate([
            'sub_theme_id' => 'required|exists:sub_themes,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        

        SubThemeBlock::create($data);
       

        return redirect()->route('subthemes.show', $data['sub_theme_id'])->with('success', 'Sub-theme block created successfully.');
    }

    public function show(SubThemeBlock $subThemeBlock)
    {        
        return redirect()->route('subthemes.show', $subThemeBlock->sub_theme_id);
    }

    public function edit(SubThemeBlock $subThemeBlock)
    {
       
        
        return view('subthemeblock.edit', compact('subThemeBlock'));
    }

    // public function edit(SubTheme $subTheme, SubThemeBlock $block)
    // {
        
    //     return view('subthemeblocks.edit', compact('subTheme', 'block'));
    // }

    public function update(Request $request, $id)
    {
        $subThemeBlock = SubThemeBlock::findOrFail($id);
        // dd($request);
        $data = $request->validate([
            
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subThemeBlock->update($data);

        

        return redirect()->route('subthemes.show', $subThemeBlock->sub_theme_id)->with('success', 'Sub-theme block updated successfully.');
    }

    public function destroy(SubThemeBlock $subThemeBlock)
    {

        // dd($subThemeBlock);
       
        
        $subThemeBlock->delete();

        return redirect()->route('subthemes.show', $subThemeBlock->sub_theme_id)->with('success', 'Sub-theme block deleted successfully.');
    }
}

