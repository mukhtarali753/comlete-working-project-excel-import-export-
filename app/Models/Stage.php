<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $guarded=[];
   


    public function Leads(){
        return $this->hasMany(Lead::class,'stage_id');
    }
    public function board(){
        return $this->belongsTo(Board::class,'board_id');
    }
}
