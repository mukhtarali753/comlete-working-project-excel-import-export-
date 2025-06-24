<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubTheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_id',
        'title',
        'description',
        'body',
    ];

    public function blocks()
    {
        return $this->hasMany(SubThemeBlock::class, 'sub_theme_id');
    }
    public function theme(){
        return $this->belongsTo(Theme::class,'theme_id');
    }

     
}
