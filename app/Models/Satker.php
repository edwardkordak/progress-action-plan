<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satker extends Model
{
    protected $fillable = ['name'];
    public function ppks()
    {
        return $this->hasMany(Ppk::class);
    }
    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
