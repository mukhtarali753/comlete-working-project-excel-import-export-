<?php
namespace App\Http\Requests\SheetV2;

use Illuminate\Foundation\Http\FormRequest;

class SheetVersionRestoreRequestV2 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'version_number' => 'required|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'version_number.required' => 'Version number is required.',
            'version_number.integer' => 'Version number must be a valid integer.',
            'version_number.min' => 'Version number must be at least 1.',
        ];
    }
}















