<?php
namespace App\Http\Requests\SheetV2;

use Illuminate\Foundation\Http\FormRequest;

class SaveSheetsRequestV2 extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'sheets' => 'required|array',
            'sheets.*.name' => 'required|string|max:255',
            'sheets.*.data' => 'required|string',
            'sheets.*.order' => 'nullable|integer|min:0',
            'sheets.*.id' => 'nullable|exists:sheets,id',
            'sheets.*.rowUpdates' => 'nullable|array',
            'sheets.*.rowUpdates.*.rowIndex' => 'nullable|integer|min:0',
            'sheets.*.rowUpdates.*.rowId' => 'nullable|integer',
            'sheets.*.rowUpdates.*.data' => 'nullable|array',
            'sheets.*.rowUpdates.*.modified' => 'nullable|boolean',
            'file_id' => 'nullable|exists:files,id',
            'enable_version_history' => 'nullable|boolean',
            'simple_upsert' => 'nullable|boolean',
        ];
    }
}















