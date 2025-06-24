<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function blocks()
    {
        return $this->hasMany(ThemeBlock::class);
    }

    public function subthemes()
    {
        return $this->hasMany(Theme::class, 'parent_id');
    }
}
