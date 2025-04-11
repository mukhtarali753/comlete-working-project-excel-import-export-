<?php

 namespace App\Http\Controllers;

use App\Models\SubTheme;
use Illuminate\Http\Request;

class SubThemeController extends Controller
{
    public function index()
    {
        
    }

    public function create($themeId)
    {   
        return view('SubTheme.create', compact('themeId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'theme_id' => 'required|exists:themes,id',
        ]);

        SubTheme::create($data);

        return redirect()->route('themes.show', $data['theme_id'])->with('success', 'Sub-theme created successfully.');
    }

    public function show($id)
    {
        
        $subTheme = SubTheme::with('blocks')->find($id);
        // dd($subTheme);
       return view('SubTheme.show', compact('subTheme'));
       
    }

    public function edit(SubTheme $subTheme)

    {
        
    }

    public function update(Request $request, SubTheme $subTheme)
    {
        
    }

    public function destroy(SubTheme $subTheme)
    {
    }
}
