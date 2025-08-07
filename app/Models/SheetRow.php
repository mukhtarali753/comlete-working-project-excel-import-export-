<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheetRow extends Model
{
    protected $fillable = ['sheet_id', 'sheet_data'];

    // protected $casts = ['sheet_data'];

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }
}