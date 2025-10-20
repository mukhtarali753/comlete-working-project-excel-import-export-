<?php

use App\Http\Controllers\SheetV2\SheetControllerV2;
use Illuminate\Support\Facades\Route;



Route::controller(SheetControllerV2::class)->prefix('sheetV2')->name('sheetV2.')->group(function () {
    Route::get('/excel-preview/{fileId?}', 'index')->name('excel-preview');
    Route::post('/save-sheets', 'saveSheets')->name('save');
    Route::post('/import-excel', 'importExcel')->name('import');
    Route::get('/files', 'listFiles')->name('files.list');
    Route::get('/get-sheets', 'getSheets')->name('get');
    Route::get('/sheet/{sheet}/data', 'getSheetData')->name('data');
    Route::get('/files/{id}/sheets', 'getSheetsByFile')->name('byFile');
    Route::delete('/sheets/{id}', 'deleteSheet')->name('delete');
    Route::get('/export/{file}/{type}', 'export')->name('export');
   
    
    // Version history routes
    Route::get('/row/{rowId}/versions', 'getRowVersionHistory')->name('row.versions');
    Route::get('/sheet/{sheetId}/versions', 'getSheetVersionHistory')->name('sheet.versions');
    Route::post('/row/{rowId}/restore/{versionNumber}', 'restoreRowVersion')->name('row.restore');
    Route::post('/sheet/{sheetId}/restore/{versionNumber}', 'restoreSheetVersion')->name('sheet.restore');
});


