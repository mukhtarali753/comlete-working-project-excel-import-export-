<?php

use App\Http\Controllers\FileV2\FileControllerV2;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->group(function () {
    Route::resource('fileV2', FileControllerV2::class)
        ->only(['index', 'store', 'edit', 'update', 'destroy'])
        ->names('fileV2');
});
    
  

