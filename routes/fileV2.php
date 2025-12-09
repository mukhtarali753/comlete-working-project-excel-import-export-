<?php

use App\Http\Controllers\FileV2\FileControllerV2;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
    Route::resource('fileV2', FileControllerV2::class)
        ->only(['index', 'store', 'edit', 'update', 'destroy'])
        ->names('fileV2');
    
    // File sharing routes
    // Route::get('/fileV2/users', [FileControllerV2::class, 'getUsers'])->name('fileV2.getUsers');
    Route::get('/fileV2/{file}/shares', [FileControllerV2::class, 'getShares'])->name('fileV2.shares');
    Route::post('/fileV2/{file}/share', [FileControllerV2::class, 'shareFile'])->name('fileV2.share');
    Route::delete('/fileV2/shares/{share}', [FileControllerV2::class, 'removeShare'])->name('fileV2.removeShare');
});

    
  

