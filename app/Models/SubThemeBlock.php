<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubThemeBlock extends Model
{
    use HasFactory;
    protected $fillable = [
        'sub_theme_id',
        'title',
        'description',
    ];

   

    public function subTheme()
    {
        return $this->belongsTo(SubTheme::class, 'sub_theme_id');
    }
} 

