<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;
    
    protected $guarded = [];


    public function stage(){
        return $this->belongsTo(Stage::class,'stage_id');
    }
    public function board()  {
        return $this->belongsTo(Board::class,'board_id');        
    }


    
}
