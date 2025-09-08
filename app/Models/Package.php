<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Package.php
class Package extends Model
{
    protected $fillable = ['satker_id', 'ppk_id', 'nama_paket', 'penyedia_jasa', 'lokasi', 'price'];
    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }
    public function ppk()
    {
        return $this->belongsTo(Ppk::class);
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
