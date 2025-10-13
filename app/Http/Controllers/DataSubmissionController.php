<?php

namespace App\Http\Controllers;

use App\Models\Ppk;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Satker;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Models\DataSubmission;
use Illuminate\Support\Facades\DB;
use App\Models\DataSubmissionDetail;

// app/Http/Controllers/DataSubmissionController.php
class DataSubmissionController extends Controller
{
    public function create()
    {
        $satkers = Satker::orderBy('name')->get(['id', 'name']);
        $units   = Unit::orderBy('name')->get(['id', 'name', 'symbol']);
        $today   = now()->toDateString();
        return view('input-form', compact('satkers', 'units', 'today'));
    }

    public function store(Request $r)
    {
        // HEADER
        $data = $r->validate([
            'satker_id'  => 'required|exists:satkers,id',
            'ppk_id'     => 'required|exists:ppks,id',
            'package_id' => 'required|exists:packages,id',
            'nama'       => 'required|string|max:255',
            'jabatan'    => 'required|string|max:255',
            // cuman tambahan sementara
            'tanggal'    => 'required|date|before_or_equal:today',

            // DETAIL (banyak baris per kategori)
            'details'                        => 'required|array|min:1',
            'details.*.category_id'          => 'required|exists:job_categories,id',
            'details.*.rows'                 => 'required|array|min:1',
            'details.*.rows.*.item_id'       => 'required|exists:items,id',
            'details.*.rows.*.volume'        => 'nullable|numeric|min:0',
            'details.*.rows.*.satuan_id'     => 'nullable|exists:units,id',
            'details.*.rows.*.keterangan'    => 'nullable|string|max:10000',
        ]);

        // Validasi hirarki Satker → PPK → Paket
        $ppk = Ppk::where('id', $data['ppk_id'])->where('satker_id', $data['satker_id'])->firstOrFail();
        $pkg = Package::where('id', $data['package_id'])
            ->where('satker_id', $data['satker_id'])->where('ppk_id', $ppk->id)->firstOrFail();

        DB::transaction(function () use ($data, $pkg) {
            $submission = DataSubmission::create([
                'satker_id'  => $data['satker_id'],
                'ppk_id'     => $data['ppk_id'],
                'package_id' => $data['package_id'],
                'nama'       => $data['nama'],
                'tanggal'     => $data['tanggal'],
                'penyedia_jasa' => $pkg->penyedia_jasa ?? '—',
                'jabatan'    => $data['jabatan'],
                'lokasi'     => $pkg->lokasi ?? '—',
          
                // 'tanggal'    => now()->toDateString(), 
            ]);

            foreach ($data['details'] as $block) {
                $catId = $block['category_id'];
                foreach ($block['rows'] as $row) {
                    // pastikan item memang milik (package, category)
                    $item = Item::where('id', $row['item_id'])
                        ->where('package_id', $pkg->id)
                        ->where('job_category_id', $catId)
                        ->firstOrFail();

                    DataSubmissionDetail::create([
                        'data_submission_id' => $submission->id,
                        'job_category_id'    => $catId,
                        'item_id'            => $item->id,
                        'volume'             => $row['volume'] ?? null,
                        'satuan_id'          => $row['satuan_id'] ?? ($item->default_unit_id ?: null), // fallback ke default item
                        'keterangan'         => $row['keterangan'] ?? null,
                    ]);
                }
            }
        });

        return back()->with('status', 'Data berhasil disimpan.');
    }
}
