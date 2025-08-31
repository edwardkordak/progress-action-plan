<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Ppk.php
class Ppk extends Model
{
    protected $fillable = ['satker_id', 'name'];
    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }
    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
