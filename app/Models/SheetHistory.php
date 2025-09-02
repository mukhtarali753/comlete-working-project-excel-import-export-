<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheetHistory extends Model
{
    protected $fillable = [
        'file_id',
        'sheet_id',
        'version_number',
        'is_current',
        'data',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
        'is_current' => 'boolean',
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function sheet()
    {
        return $this->belongsTo(Sheet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


