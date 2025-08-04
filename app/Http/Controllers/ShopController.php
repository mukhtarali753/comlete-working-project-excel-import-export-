<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Exports\ShopsExport;
use App\Imports\ShopsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ShopController extends Controller
{
    // Display all shops
    public function index()
    {
        $shops = Shop::latest()->paginate(10);
        return view('shops.index', compact('shops'));
    }

    // Show create form
    public function create()
    {
        return view('shops.create');
    }

    // Store new shop
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'address' => 'required',
            'contact_email' => 'required|email',
            'phone' => 'required',
            'is_active' => 'boolean'
        ]);

        Shop::create($validated);

        return redirect()->route('shops.index')
            ->with('success', 'Shop created successfully.');
    }

    // Show single shop
    public function show(Shop $shop)
    {
        return view('shops.show', compact('shop'));
    }

    // Show edit form
    public function edit(Shop $shop)
    {
        return view('shops.edit', compact('shop'));
    }

    // Update shop
    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'address' => 'required',
            'contact_email' => 'required|email',
            'phone' => 'required',
            'is_active' => 'boolean'
        ]);

        $shop->update($validated);

        return redirect()->route('shops.index')
            ->with('success', 'Shop updated successfully.');
    }

    // Delete shop
    public function destroy(Shop $shop)
    {
        $shop->delete();

        return redirect()->route('shops.index')
            ->with('success', 'Shop deleted successfully.');
    }

    // Export to Excel
    public function export() 
    {
        return Excel::download(new ShopsExport, 'shops.xlsx');
    }

    // Preview Excel data
    public function preview()
    {
        $shops = Shop::all();
        return view('shops.preview', compact('shops'));
    }

    // Import from Excel
//     public function import(Request $request) 
//     {
//         $request->validate([
//             'file' => 'required|mimes:xlsx,xls'
//         ]);

//         Excel::import(new ShopsImport, $request->file('file'));

//         return back()->with('success', 'Shops imported successfully.');
//     }
}