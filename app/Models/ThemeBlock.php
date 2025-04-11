<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeBlock extends Model
{
    use HasFactory;

    protected $fillable = ['theme_id', 'title', 'description'];
    

    
    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }
}
