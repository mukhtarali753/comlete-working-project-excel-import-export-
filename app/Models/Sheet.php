<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Sheet extends Model
{
    protected $fillable = ['file_id', 'name', 'order', 'data', 'config', 'celldata', 'version', 'is_current'];

    protected $casts = [
        'data' => 'json',
        'config' => 'json',
        'celldata' => 'json',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function rows()
    {
        return $this->hasMany(SheetRow::class);
        // return $this->hasMany(SheetRow::class)->where('version', 0);
    }

   
    public static function getValidationRules($fileId, $excludeId = null)
    {
        $rules = [
            'file_id' => 'required|exists:files,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sheets')->where(function ($query) use ($fileId) {
                    return $query->where('file_id', $fileId);
                })->ignore($excludeId),
            ],
            'order' => 'nullable|integer|min:0',
        ];

        return $rules;
    }

    /**
     * Get basic validation rules for sheet data (without file_id)
     */
    public static function getBasicValidationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
            'id' => 'nullable|exists:sheets,id',
        ];
    }
}