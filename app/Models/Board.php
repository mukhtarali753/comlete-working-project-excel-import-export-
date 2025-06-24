<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function stages()  {
        return $this->hasMany(Stage::class,'board_id');
    }
    public function leads()  {
        return $this->hasMany(Lead::class,'board_id');
    }
}
