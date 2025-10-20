<?php
namespace App\Http\Requests\SheetV2;

use Illuminate\Foundation\Http\FormRequest;

class SheetExportRequestV2 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'nullable|string|in:xlsx,xls,csv',
            'sheets' => 'nullable|array',
            'sheets.*' => 'exists:sheets,id',
        ];
    }
}















