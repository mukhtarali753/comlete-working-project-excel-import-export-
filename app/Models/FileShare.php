<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileShare extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'type' => 'string'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

   
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function sharedBy()
    {
        return $this->belongsTo(User::class, 'shared_by');
    }

    public function isViewer()
    {
        return $this->type === 'viewer';
    }

   
    public function isEditor()
    {
        return $this->type === 'editor';
    }
}
