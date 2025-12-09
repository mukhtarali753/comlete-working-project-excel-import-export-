<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\StageController;
use Illuminate\Http\Request;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\ThemeBlockController;
use app\http\Controllers\ShowController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ShopController;
use App\Models\Stage;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ShopControlle;
use App\Http\Controllers\SheetController;
use App\Http\Controllers\ExcelImportController;


use Maatwebsite\Excel\Facades\Excel;


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

// Sheet history removed

Route::get('/dashboard', [ThemeController::class, 'index'])->middleware(['auth'])->name('dashboard');









// Route::get('/dashboard', [ThemeController::class, 'show'])->name('dashboard');

// Theme routes
Route::get('/themes/index', [ThemeController::class, 'index'])->middleware(['auth',])->name('themes.index');
Route::get('/themes/create/{id?}', [ThemeController::class, 'create'])->name('themes.create');
Route::post('/themes', [ThemeController::class, 'store'])->name('themes.store');
Route::get('/themes/show/{id}', [ThemeController::class, 'show'])->name('themes.show');
Route::get('/website/show/{id}', [ThemeController::class, 'showTheme'])->name('website.show');
Route::get('/themes/{id}/edit', [ThemeController::class, 'edit'])->name('themes.edit');
Route::put('/themes/{id}', [ThemeController::class, 'update'])->name('themes.update');
Route::delete('/themes/{id}', [ThemeController::class, 'destroy'])->name('themes.destroy');


Route::get('/themes/{themeId}/blocks/create', [ThemeBlockController::class, 'create'])->name('theme_blocks.create');
Route::get('/blocks/{id}/edit', [ThemeBlockController::class, 'editBlock'])->name('theme_blocks.edit');


Route::get('/blocks/show/{id}', [ThemeController::class, 'showSingleBlock'])->name('theme_blocks.show');


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
Route::get('/board/edit{id}', [BoardController::class, 'edit'])->name('board.edit');
Route::put('/board/update/{id}', [BoardController::class, 'update'])->name('board.update');


//board stages
Route::get('/stage/{id}/edit', [StageController::class, 'edit'])->name('layout.edit');
Route::put('/stage/{id}', [StageController::class, 'update'])->name('stage.update');
Route::get('/update-stage-row', [StageController::class, 'getStageRow'])
    ->name('update.stage.row');







Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/', [LeadController::class, 'index'])->name('board');
    Route::get('/create', [LeadController::class, 'create'])->name('create');
    Route::post('/', [LeadController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LeadController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LeadController::class, 'update'])->name('update');
    Route::delete('/{id}', [LeadController::class, 'destroy'])->name('delete');
    Route::post('/{id}/update-stage', [LeadController::class, 'updateStage'])->name('update-stage');
});


Route::prefix('leads')->name('leads.')->group(function () {
    Route::get('/{board?}', [LeadController::class, 'index'])->name('index');
                
    Route::put('/{id}', [LeadController::class, 'update'])->name('update');
    Route::post('/update-stage', [LeadController::class, 'updateStage'])->name('update-stage'); // Move Lead to another stage
    Route::delete('/{id}', [LeadController::class, 'destroy'])->name('destroy');
});


Route::prefix('excel')->controller(ExcelController::class)->group(function () {


    Route::get('/preview-table', 'previewAsTable')->name('excel.preview.table');
    Route::get('/preview-fixed', 'previewAsEmbed')->name('excel.preview.fixed');
});

//student table route

// sleep(7);
Route::prefix('students')->controller(StudentController::class)->name('students.')->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->name('edit');
    Route::put('/{id}', 'update')->name('update');
    Route::delete('/{id}', 'destroy')->name('destroy');
    Route::get('/preview', 'preview')->name('preview');
    Route::post('/preview', 'savePreview')->name('savePreview');
    Route::get('/download', 'download')->name('download');
});

Route::resource('shops', ShopController::class);
Route::get('shops/export', [ShopController::class, 'export'])->name('shops.export');
Route::get('shops/preview', [ShopController::class, 'preview'])->name('shops.preview');
Route::post('shops/import', [ShopController::class, 'import'])->name('shops.import');






Route::prefix('businesses')->middleware(['auth'])->controller(FileController::class)->group(function () {
    Route::get('/preview', 'preview')->name('file.preview');
    Route::get('/{file}/edit-sheet', 'getsheet')->name('businesses.edit.sheet');
    Route::get('/export', 'export')->name('businesses.export');
    Route::post('/import', 'import')->name('businesses.import');
    Route::post('/update-inline', 'updateInline')->name('businesses.update.inline');
    Route::get('/excel-preview', 'excelPreview')->name('businesses.preview.excel');
    Route::post('/', 'store')->name('businesses.store');
    Route::post('/{file}/update', 'update')->name('businesses.update');
    Route::get('/{file}/edit', 'edit')->name('businesses.edit');
    Route::delete('/{file}', 'destroy')->name('businesses.destroy');
});

Route::controller(SheetController::class)->group(function () {
    Route::get('/excel-preview/{fileId?}', 'showSheets')->name('excel.preview');
    Route::post('/save-sheets', 'saveSheets')->name('sheets.save');
    Route::post('/import-excel', 'importExcel')->name('sheets.import');
    Route::get('/sheets/{file}', 'show')->name('sheets.show');
    Route::get('/files', 'listFiles')->name('files.list');
    Route::get('/get-sheets', 'getSheets')->name('sheets.get');
    Route::get('/sheet/{sheet}/data', 'getSheetData')->name('sheet.data');
    Route::get('/files/{id}/sheets', 'getSheetsByFile');
    Route::delete('/sheets/{id}', 'deleteSheet')->name('sheets.delete');
    Route::get('/export/{file}/{type}', 'export')->name('sheets.export');
    Route::get('/test-import', 'testImport')->name('sheets.test'); 
    
    // Version history routes
    Route::get('/row/{rowId}/versions', 'getRowVersionHistory')->name('sheets.row.versions');
    Route::get('/sheet/{sheetId}/versions', 'getSheetVersionHistory')->name('sheets.sheet.versions');
    Route::get('/sheet/{sheetId}/debug', 'debugSheetVersions')->name('sheets.sheet.debug');
    Route::post('/row/{rowId}/restore/{versionNumber}', 'restoreRowVersion')->name('sheets.row.restore');
    Route::post('/sheet/{sheetId}/restore/{versionNumber}', 'restoreSheetVersion')->name('sheets.sheet.restore');
});



// Excel Import Routes
Route::prefix('excel-import')->name('excel.import.')->middleware(['auth'])->group(function () {
    Route::get('/', [ExcelImportController::class, 'index'])->name('index');
    Route::post('/preview', [ExcelImportController::class, 'preview'])->name('preview');
    Route::post('/process', [ExcelImportController::class, 'import'])->name('process');
    Route::get('/show/{file}', [ExcelImportController::class, 'show'])->name('show');
    Route::get('/download/{file}/{type?}', [ExcelImportController::class, 'download'])->name('download');
});





require __DIR__ . '/auth.php';
require __DIR__ . '/fileV2.php';
require __DIR__ . '/sheetV2.php';


