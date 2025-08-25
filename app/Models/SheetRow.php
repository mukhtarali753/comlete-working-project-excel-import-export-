<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheetRow extends Model
{
    protected $fillable = ['sheet_id', 'sheet_data', 'cell_formatting'];

    protected $casts = [
        'sheet_data' => 'array',
        'cell_formatting' => 'array',
    ];

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }
}