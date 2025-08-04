<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sheet extends Model
{
    protected $fillable = ['file_id', 'name', 'order'];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function rows()
    {
        return $this->hasMany(SheetRow::class);
    }
}