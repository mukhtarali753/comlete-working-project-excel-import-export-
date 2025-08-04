<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['name', 'type', 'user_id'];

    public function sheets()
    {
        return $this->hasMany(Sheet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}