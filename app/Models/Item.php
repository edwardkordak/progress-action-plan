<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/Item.php
class Item extends Model
{
    protected $fillable = ['package_id', 'job_category_id', 'name','volume', 'price','default_unit_id'];
    
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }
    public function defaultUnit()
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }
}
