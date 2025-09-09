<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheetRowVersion extends Model
{
    protected $fillable = [
        'sheet_row_id',
        'sheet_id',
        'sheet_data',
        'cell_formatting',
        'version_number'
    ];

    protected $casts = [
        'sheet_data' => 'json',
        'cell_formatting' => 'json',
        'version_number' => 'integer',
        'sheet_row_id' => 'integer',
    ];

    public function sheetRow()
    {
        return $this->belongsTo(SheetRow::class);
    }

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }
}
