<?php

namespace App\Http\Controllers;

use App\Models\Ppk;
use App\Models\Item;
use App\Models\Package;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function ppks(Request $r)
    {
        $r->validate(['satker_id' => 'required|exists:satkers,id']);
        return Ppk::where('satker_id', $r->satker_id)->orderBy('name')->get(['id', 'name']);
    }
    public function packages(Request $r)
    {
        $r->validate([
            'satker_id' => 'required|exists:satkers,id',
            'ppk_id' => 'required|exists:ppks,id'
        ]);

        return Package::where('satker_id', $r->satker_id)->where('ppk_id', $r->ppk_id)
            ->orderBy('nama_paket')->get(['id', 'penyedia_jasa', 'nama_paket', 'lokasi']);
    }

    public function jobCategories(Request $r)
    {
        $packageId = $r->query('package_id');

        if ($packageId) {
            return JobCategory::whereHas('items', function ($q) use ($packageId) {
                $q->where('package_id', $packageId);
            })
                ->orderBy('sort_order')
                ->get(['id', 'code', 'name']);
        }

        return JobCategory::orderBy('sort_order')->get(['id', 'code', 'name']);
    }

    public function items(Request $r)
    {
        $r->validate([
            'package_id'      => 'required|exists:packages,id',
            'job_category_id' => 'required|exists:job_categories,id',
        ]);

        return Item::where('package_id', $r->package_id)
            ->where('job_category_id', $r->job_category_id)
            ->orderBy('name')
            ->get(['id', 'name', 'default_unit_id']);
    }
    public function packageShow(Package $package)
    {
        return ['id' => $package->id, 'nama_paket' => $package->nama_paket, 'lokasi' => $package->lokasi];
    }
}
