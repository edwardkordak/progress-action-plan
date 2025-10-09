<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataTarget extends Model
{
    protected $fillable = ['satker_id', 'ppk_id', 'package_id', 'tanggal'];

    public function details()
    {
        return $this->hasMany(DataTargetDetail::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
