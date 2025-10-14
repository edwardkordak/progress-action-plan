<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DataSubmissionDetail extends Model
{
    protected $fillable = ['data_submission_id', 'job_category_id', 'item_id', 'volume', 'satuan_id', 'keterangan'];
    public function submission()
    {
        return $this->belongsTo(DataSubmission::class, 'data_submission_id');
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function satuan()
    {
        return $this->belongsTo(Unit::class, 'satuan_id');
    }
    public function jobCategory()
    {
        return $this->belongsTo(\App\Models\JobCategory::class, 'job_category_id');
    }

}
