<?php
namespace App\Http\Requests\SheetV2;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSheetRequestV2 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'data' => 'nullable|string',
            'config' => 'nullable|string',
            'celldata' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ];
    }
}















