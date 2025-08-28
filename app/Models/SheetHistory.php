<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheetHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'cell',
        'change_type',
        'old_value',
        'new_value',
        'user_id',
    ];
}



