<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = ['name', 'symbol'];
    public function getDisplayNameAttribute()
    {
        return $this->symbol ? "{$this->name} ({$this->symbol})" : $this->name;
    }
}
