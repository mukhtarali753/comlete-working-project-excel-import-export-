<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\StageController;
use Illuminate\Http\Request;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ThemeBlockController;
use app\http\Controllers\ShowController;
use App\Models\Stage;


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

Route::get('/dashboard', [ThemeController::class, 'index'])->middleware(['auth' ])->name('dashboard');









// Route::get('/dashboard', [ThemeController::class, 'show'])->name('dashboard');

// Theme routes
Route::get('/themes/index', [ThemeController::class, 'index'])->middleware(['auth', ])->name('themes.index');
Route::get('/themes/create/{id?}', [ThemeController::class, 'create'])->name('themes.create');
Route::post('/themes', [ThemeController::class, 'store'])->name('themes.store');
Route::get('/themes/show/{id}', [ThemeController::class, 'show'])->name('themes.show');
Route::get('/website/show/{id}', [ThemeController::class, 'showTheme'])->name('website.show');
Route::get('/themes/{id}/edit', [ThemeController::class, 'edit'])->name('themes.edit');
Route::put('/themes/{id}', [ThemeController::class, 'update'])->name('themes.update');
Route::delete('/themes/{id}', [ThemeController::class, 'destroy'])->name('themes.destroy');

// Theme Block routes

// Route::post('/themes/{themeId}/blocks/store', [ThemeBlockController::class, 'storeBlock'])->name('theme_blocks.store');

Route::get('/themes/{themeId}/blocks/create', [ThemeBlockController::class, 'create'])->name('theme_blocks.create');
Route::get('/blocks/{id}/edit', [ThemeBlockController::class, 'editBlock'])->name('theme_blocks.edit');


Route::get('/blocks/show/{id}', [ThemeController::class, 'showSingleBlock'])->name('theme_blocks.show');

// Route::post('/blocks', [ThemeController::class, 'storeBlock'])->name('theme_blocks.store');
// Route::post('/blocks' , [ThemeBlockController::class,'store'])->name('theme_blocks.store');

Route::get('/blocks/{id}/edit', [ThemeBlockController::class, 'editBlock'])->name('theme_blocks.edit');
Route::put('/blocks/{id}', [ThemeController::class, 'updateBlock'])->name('theme_blocks.update');
Route::delete('/blocks/{id}', [ThemeController::class, 'destroyBlock'])->name('theme_blocks.destroy');


Route::post('/theme/{themeId}/blocks', [ThemeController::class, 'storeBlock'])->name('theme_blocks.store');
Route::get('/blocks/{id}/edit', [ThemeController::class, 'editBlock'])->name('theme_blocks.edit');
Route::post('/blocks/{id}', [ThemeController::class, 'updateBlock'])->name('theme_blocks.update');











//board
Route::get('/showboard', [BoardController::class, 'showBoard'])->name('showboard');
Route::get('/create', [BoardController::class, 'create'])->name('create');

Route::post('/board/store', [BoardController::class, 'store'])->name('board.store');

Route::delete('/board/delete/{id}', [BoardController::class, 'destroy'])->name('board.destroy');
Route::get('/board/edit{id}',[BoardController::class,'edit'])->name('board.edit');
Route::put('/board/update/{id}',[BoardController::class,'update'])->name('board.update');


//board stages
Route::get('/stage/{id}/edit', [StageController::class, 'edit'])->name('layout.edit');
Route::put('/stage/{id}', [StageController::class, 'update'])->name('stage.update');
Route::get('/update-stage-row', [StageController::class, 'getStageRow'])
->name('update.stage.row');




// Leads routes
// Route::get('/leads', [LeadController::class, 'index'])->name('leads.board');
// Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
// Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
// Route::get('/leads/{id}/edit', [LeadController::class, 'edit'])->name('leads.edit');
// Route::put('/leads/{id}', [LeadController::class, 'update'])->name('leads.update');
// Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.delete');
// Route::post('/leads/{id}/update-stage', [LeadController::class, 'updateStage'])->name('leads.update-stage');




Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/', [LeadController::class, 'index'])->name('board');
    Route::get('/create', [LeadController::class, 'create'])->name('create');
    Route::post('/', [LeadController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LeadController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LeadController::class, 'update'])->name('update');
    Route::delete('/{id}', [LeadController::class, 'destroy'])->name('delete');
    Route::post('/{id}/update-stage', [LeadController::class, 'updateStage'])->name('update-stage');
});










// Route::get('/leads/{board?}', [LeadController::class, 'index'])->name('leads.index');
// // Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
// // Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
// // Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
// Route::put('/leads/{id}', [LeadController::class, 'update'])->name('leads.update');
// // Route::post('/leads/{lead}/move', [LeadController::class, 'move'])->name('leads.move');
// Route::post('/leads/update-stage', [LeadController::class, 'updateStage'])->name('leads.update-stage');
// Route::delete('/leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');



Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/{board?}', [LeadController::class, 'index'])->name('index');              
    // Route::get('/create', [LeadController::class, 'create'])->name('create');             
    // Route::post('/', [LeadController::class, 'store'])->name('store');                    
    // Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');            

    Route::put('/{id}', [LeadController::class, 'update'])->name('update');                  
    Route::post('/update-stage', [LeadController::class, 'updateStage'])->name('update-stage'); // Move Lead to another stage
    Route::delete('/{id}', [LeadController::class, 'destroy'])->name('destroy');             
             
});




require __DIR__.'/auth.php';



           



























