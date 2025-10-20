<?php

namespace App\Http\Requests\FileV2;

use Illuminate\Foundation\Http\FormRequest;

class FileRequestV2 extends FormRequest
{
    public function authorize(): bool
    {
        
        return true;
    }

    public function rules(): array
    {
        // Support both implicit model binding (route model) and numeric id
        $routeFile = $this->route('fileV2') ?? $this->route('file');
        $fileId = is_object($routeFile) ? $routeFile->id : ($routeFile ?? null);

        return [
            'name' => 'required|string|max:255|unique:files,name,' . $fileId,
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'File name is required.',
            'name.unique' => 'This file name already exists.',
        ];
    }
}
