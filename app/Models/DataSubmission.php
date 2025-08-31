<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/DataSubmission.php
class DataSubmission extends Model
{
    protected $fillable = ['satker_id', 'ppk_id', 'package_id', 'nama', 'jabatan', 'lokasi', 'tanggal'];
    public function details()
    {
        return $this->hasMany(DataSubmissionDetail::class);
    }
}
