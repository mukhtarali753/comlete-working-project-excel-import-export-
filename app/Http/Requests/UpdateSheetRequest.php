<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSheetRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'data' => 'required|string',
            'config' => 'nullable|string',
            'celldata' => 'nullable|string',
            'enable_version_history' => 'nullable|boolean',
        ];
    }
}



