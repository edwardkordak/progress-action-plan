<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataTargetDetail extends Model
{
     protected $fillable = ['data_target_id', 'job_category_id', 'item_id', 'volume', 'keterangan'];

     public function item()
     {
          return $this->belongsTo(Item::class);
     }

     public function target()
     {
          return $this->belongsTo(DataTarget::class, 'data_target_id');
     }
}
