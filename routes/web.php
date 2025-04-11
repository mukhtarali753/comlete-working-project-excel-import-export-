<?php

use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ThemeBlockController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [ThemeController::class, 'index'])->middleware(['auth'])->name('dashboard');









// Route::get('/dashboard', [ThemeController::class, 'index'])->name('dashboard');

// Theme routes
Route::get('/themes/index', [ThemeController::class, 'index'])->middleware(['auth'])->name('themes.index');
Route::get('/themes/create/{id?}', [ThemeController::class, 'create'])->name('themes.create');
Route::post('/themes', [ThemeController::class, 'store'])->name('themes.store');
Route::get('/themes/{id}', [ThemeController::class, 'show'])->name('themes.show');
Route::get('/themes/{id}/edit', [ThemeController::class, 'edit'])->name('themes.edit');
Route::put('/themes/{id}', [ThemeController::class, 'update'])->name('themes.update');
Route::delete('/themes/{id}', [ThemeController::class, 'destroy'])->name('themes.destroy');

// Theme Block routes

// Route::post('/themes/{themeId}/blocks/store', [ThemeBlockController::class, 'storeBlock'])->name('theme_blocks.store');

Route::get('/themes/{themeId}/blocks/create', [ThemeBlockController::class, 'create'])->name('theme_blocks.create');
Route::get('/blocks/{id}/edit', [ThemeBlockController::class, 'editBlock'])->name('theme_blocks.edit');
// Route::post('/blocks', [ThemeController::class, 'storeBlock'])->name('theme_blocks.store');
// Route::post('/blocks' , [ThemeBlockController::class,'store'])->name('theme_blocks.store');

Route::get('/blocks/{id}/edit', [ThemeBlockController::class, 'editBlock'])->name('theme_blocks.edit');
Route::put('/blocks/{id}', [ThemeController::class, 'updateBlock'])->name('theme_blocks.update');
Route::delete('/blocks/{id}', [ThemeController::class, 'destroyBlock'])->name('theme_blocks.destroy');


Route::post('/theme/{themeId}/blocks', [ThemeController::class, 'storeBlock'])->name('theme_blocks.store');
Route::get('/blocks/{id}/edit', [ThemeController::class, 'editBlock'])->name('theme_blocks.edit');
Route::post('/blocks/{id}', [ThemeController::class, 'updateBlock'])->name('theme_blocks.update');




// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');


require __DIR__.'/auth.php';



