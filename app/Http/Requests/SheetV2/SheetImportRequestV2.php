<?php
namespace App\Http\Requests\SheetV2;

use Illuminate\Foundation\Http\FormRequest;

class SheetImportRequestV2 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'file_name' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'File must be an Excel file (.xlsx, .xls) or CSV file.',
        ];
    }
}


