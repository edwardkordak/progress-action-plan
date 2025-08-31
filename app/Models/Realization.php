<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Realization extends Model
{
    protected $fillable = [
        'packages_id',
        'bobot',
        'tanggal',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class, 'packages_id');
    }
}
