<?php

namespace Database\Seeders;

use App\Models\DataTarget;
use App\Models\DataTargetDetail;
use App\Models\Item;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataTargetSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/lanjutan.csv');
        $rows = array_map('str_getcsv', file($file));
        $header = array_shift($rows);

        // Mapping huruf → kategori
        $catMap = [
            'A' => 5,
            'B' => 6,
            'C' => 7,
            'D' => 8,
            'E' => 9,
            'F' => 3,
        ];

        // Mapping kolom kode → item name
        $itemMap = [
            'A1' => 'Galian Tanah dengan Alat di Bendung',
            'A2' => 'Beton Siklop 60% Beton fc 15 Mpa : 40% Batu Belah Semi mekanis',

            'B1' => 'Galian Tanah dengan Alat di Saluran Induk (1)',
            'B2' => 'Galian Tanah dengan Alat di Saluran Induk (2)',
            'B3' => 'Pasangan Batu Tipe N (1PC 4PP) semi mekanis di Sal Induk',
            'B4' => 'Pekerjaan Plesteran 1PC : 3PP di Saluran Induk',
            'B5' => 'Pembesian di Saluran Induk',
            'B6' => 'Beton Fc 20 Mpa Agregat Maks 19 mm Semi Mekanis di Sal Induk',

            'C1' => 'Galian Tanah dengan Alat di Saluran Sekunder (1)',
            'C2' => 'Galian Tanah dengan Alat di Saluran Sekunder (2)',
            'C3' => 'Pasangan Batu Tipe N (1PC 4PP) semi mekanis di Sal Sekunder',
            'C4' => 'Pekerjaan Plesteran 1PC : 3PP di Saluran Sekunder',
            'C5' => 'Pembesian di Saluran Sekunder',
            'C6' => 'Beton Fc 20 Mpa Agregat Maks 19 mm Semi Mekanis di Sal Sekunder',

            'D1' => 'Galian Tanah dengan Alat di Saluran Pembuang (1)',
            'D2' => 'Galian Tanah dengan Alat di Saluran Pembuang (2)',

            'E1' => 'Galian Tanah dengan Alat di Bangunan Sadap',
            'E2' => 'Pasangan Batu Tipe N (1PC 4PP) semi mekanis di Bangunan Sadap',

            'F1' => 'Galian Tanah dengan Alat di Bangunan Pelengkap',
            'F2' => 'Pasangan Batu Tipe N (1PC 4PP) semi mekanis di Bangunan Pelengkap',
        ];

        foreach ($rows as $row) {

            // Lewati baris tanpa tanggal
            if (!isset($row[0]) || trim($row[0]) === '') {
                continue;
            }

            $tanggal = \Carbon\Carbon::parse($row[0])->format('Y-m-d');

            DB::transaction(function () use ($tanggal, $header, $row, $catMap, $itemMap) {

                // Insert master
                $target = DataTarget::firstOrCreate([
                    'satker_id'  => 1,
                    'ppk_id'     => 1,
                    'package_id' => 3,
                    'tanggal'    => $tanggal,
                ]);

                // Loop kolom volume
                for ($i = 1; $i < count($row); $i++) {

                    $rawCol = trim($header[$i] ?? '');

                    // Ambil kode A1, B3, C6, dst dari awal header
                    preg_match('/^[A-Z][0-9]+/', $rawCol, $match);
                    $colCode = $match[0] ?? null;

                    if (!$colCode) continue;
                    if (!isset($itemMap[$colCode])) continue;

                    // Volume: kosong → 0
                    $val = $row[$i] ?? '0';
                    $volume = floatval(str_replace(',', '.', $val));

                    // Ambil kategori
                    $huruf = substr($colCode, 0, 1);
                    $jobCategoryId = $catMap[$huruf];
                    $itemName = $itemMap[$colCode];

                    // Cari item
                    $item = Item::where('package_id', 3)
                        ->where('job_category_id', $jobCategoryId)
                        ->where('name', $itemName)
                        ->first();

                    if (!$item) {
                        dump("Item tidak ditemukan: $colCode → $itemName");
                        continue;
                    }

                    // Insert detail (0 tetap masuk)
                    DataTargetDetail::updateOrCreate([
                        'data_target_id' => $target->id,
                        'item_id'        => $item->id,
                    ], [
                        'job_category_id' => $jobCategoryId,
                        'volume'          => $volume,
                        'satuan_id'       => $item->default_unit_id,
                    ]);
                }
            });
        }

        echo "Import selesai.\n";
    }
}
