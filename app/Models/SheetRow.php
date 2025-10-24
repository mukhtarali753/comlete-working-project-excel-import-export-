<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheetRow extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sheet_data' => 'json',
        'cell_formatting' => 'json',
        'version' => 'integer',
    ];

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }

    public function versions()
    {
        return $this->hasMany(SheetRowVersion::class);
    }
}