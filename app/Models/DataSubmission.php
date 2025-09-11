<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataSubmission extends Model
{
    protected $fillable = ['satker_id', 'ppk_id', 'package_id', 'nama', 'penyedia_jasa', 'jabatan', 'lokasi', 'tanggal'];

    public function satker()
    {
        return $this->belongsTo(Satker::class);
    }
    public function ppk()
    {
        return $this->belongsTo(Ppk::class);
    }
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function details()
    {
        return $this->hasMany(DataSubmissionDetail::class);
    }
        public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
