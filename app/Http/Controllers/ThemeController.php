<?php
namespace App\Http\Controllers;


use App\Models\Theme;
use App\Models\ThemeBlock;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index()
    {
        
        //  dd(Theme::where('parent_id', 0)->orwhere('parent_id', null)->with('subthemes')->with('subthemes')->get()->toArray());
        // $themes = Theme::where('parent_id', 0)->orwhere('parent_id', null)->get();
        // // dd($themes);
        // $id = 0;
        // return view('Themes.index', compact('themes','id'));


        // $themes = Theme::where('parent_id',0)->orWhere('parent_id',null)->get();
        // $id = 0;
        // return view('themes.index',compact('themes','id'));
        $themes = Theme::whereIn('parent_id', [0, null])->get();
       
        $id = 0;

        
        return view('themes.index', compact('themes', 'id'));

        



    }


    // public function index($parentId = null)
    // {
    //     // Fetch themes based on the parent ID. If parent ID is null, fetch root-level themes.
    //     $themes = Theme::where('parent_id', $parentId)->get();
    
    //     // If there are no themes, redirect to the main themes index page
    //     if (is_null($parentId)) {
    //         return view('themes.index', compact('themes', 'parentId'));
    //     } 
    
    //     // Otherwise, display the sub-themes for the given parent theme
    //     $parentTheme = Theme::findOrFail($parentId);
    //     return view('themes.index', compact('themes', 'parentTheme'));
    // }
    
    public function create($id = null)
        
    {

        // $parent_id = $id ? $id : 0;
        $parent_id = $id ?? 0;
        
        return view('themes.create',compact('parent_id'));

     

       
    }

    public function store(Request $request)
    {
        // dd($request->all());
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'sometimes',
        ]);

        Theme::create($request->all());

        if($request->parent_id == 0){
            return redirect()->route('themes.index')->with('success', 'Theme created successfully.');
        }else{
            return redirect()->route('themes.show', $request->parent_id)->with('success', 'Sub-theme created successfully')->with('success','');
        }

      

        
    }

    // public function show($id)
    // {
    //     $themes = Theme::where('parent_id', $id)->get();
    //     // dd($theme);
    //     // $theme = Theme::with('blocks')->findOrFail($id);
    //     // $subthemes = $theme->subtheme;
    //     // dd($subthemes);
    //     return view('themes.index', compact('themes','id'));
    // }

    public function show($id)
{
   
    $themes = Theme::where('parent_id',  $id)->get();


   
    $blocks = ThemeBlock::where('theme_id', $id)->get();
    
   
    return view('themes.index', compact('themes', 'blocks', 'id'));
}


    public function edit($id)
    {
        $themes = Theme::findOrFail($id);
        
        return view('themes.edit', compact('themes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $theme = Theme::findOrFail($id);
        $theme->update($request->all());

        return redirect()->route('themes.index')->with('success', 'Theme updated successfully.');
    }

    public function destroy($id)
    {
        $theme = Theme::findOrFail($id);
        $theme->delete();

        return redirect()->route('themes.index')->with('success', 'Theme deleted successfully.');
    }

    public function storeBlock(Request $request, $themeId)
    {

        
        // dd($request,$themeId);
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    
        ThemeBlock::create([
            'theme_id' => $themeId,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        // Themeblock::create($request->all());
    
        return redirect()->route('themes.show', $themeId)->with('success', 'Sub-theme block created successfully.');
    }

    public function editBlock($id)
    {
        $block = ThemeBlock::findOrFail($id);
        return view('themes.edit_block', compact('block'));
    }

    public function updateBlock(Request $request, $id)
    {
        // dd($request,$id);
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $block = ThemeBlock::findOrFail($id);
        $block->update($request->all());

        return redirect()->route('themes.show', $block->theme_id)->with('success', 'Sub-theme block updated successfully.');
    }

    public function destroyBlock($id)

    {

        // dd($id);
        $block = ThemeBlock::findOrFail($id);
        $themeId = $block->theme_id;
        $block->delete();

        return redirect()->route('themes.show', $themeId)->with('success', 'Sub-theme block deleted successfully.');
    }
}

