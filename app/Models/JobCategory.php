<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/JobCategory.php
class JobCategory extends Model
{
    protected $fillable = ['code', 'name', 'sort_order'];
    
    public function items()
    {
        return $this->hasMany(Item::class, 'job_category_id');
    }
}
