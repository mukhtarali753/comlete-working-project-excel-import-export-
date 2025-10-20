<?php
namespace App\Http\Controllers;

use App\Http\Requests\FileStoreRequest;
use App\Http\Requests\FileUpdateRequest;
use App\Models\File;
use App\Models\Sheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\getsheet;
use App\Helpers\FileControllerHelper\PreviewHelper;
use App\Helpers\FileControllerHelper\GetSheetHelper;
use App\Helpers\FileControllerHelper\StoreFileHelper;
use App\Helpers\FileControllerHelper\UpdateFileHelper;
use App\Helpers\FileControllerHelper\DestroyFileHelper;
use App\Helpers\FileControllerHelper\EditFileHelper;


class FileController extends Controller
{
    public function preview()
    {
        return PreviewHelper::preview();
    }
        

       

    public function getsheet(File $file)
    {
        return GetSheetHelper::handle($file);
    }
        


    public function store(FileStoreRequest $request)
    {
        $data = $request->validated();
        return StoreFileHelper::handle($data);
    }


    public function update(FileUpdateRequest $request, File $file)
    {
        return UpdateFileHelper::handle($file, $request->validated());
    }


    public function edit(File $file)
    {
        return EditFileHelper::handle($file);
    }

    
    
    public function destroy(File $file)
    {
        return DestroyFileHelper::handle($file);
    }

    
    public function excelPreview()
    {
        return PreviewHelper::excelPreview();
    }

    
}
        