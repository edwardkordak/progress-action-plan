<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Satker;
use App\Models\Ppk;
use App\Models\Package;
use App\Models\JobCategory;
use App\Models\Unit;
use App\Models\Item;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name'  => 'Balai Wilayah Sungai Sulawesi I',
            'email' => 'bwss1@example.com',
        ]);

        $jobCategories = [
            ['code' => 'PESA', 'name' => 'Pekerjaan Saluran',   'sort_order' => 1],
            ['code' => 'PEMBA', 'name' => 'Pekerjaan Bangunan Sadap', 'sort_order' => 2],
            ['code' => 'PELENG', 'name' => 'Pekerjaan Bangunan Pelengkap', 'sort_order' => 3],
        ];
        foreach ($jobCategories as $jc) {
            JobCategory::firstOrCreate(
                ['code' => $jc['code']],
                ['name' => $jc['name'], 'sort_order' => $jc['sort_order']]
            );
        }

        $units = [
            ['name' => 'Meter Persegi', 'symbol' => 'm²'],
            ['name' => 'Meter Kubik', 'symbol' => 'm³'],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u['name'], 'symbol' => $u['symbol']]);
        }

        $tree = [
            'SNVT PJPA' => [
                'Irigasi dan Rawa' => [
                    ['nama_paket' => 'Rehabilitasi Daerah Irigasi Dataran Kotamobagu', 
                     'penyedia_jasa' => 'PT. Nihara Anugerah',
                     'price' => 10000,
                     'lokasi' => 'Kab. Bolaang Mongondow Timur',
                    ],
                ],
            ],
     
        ];

        DB::transaction(function () use ($tree) {
            foreach ($tree as $satkerName => $ppks) {
                $satker = Satker::firstOrCreate(['name' => $satkerName]);

                foreach ($ppks as $ppkName => $packages) {
                    $ppk = Ppk::firstOrCreate([
                        'satker_id' => $satker->id,
                        'name'      => $ppkName,
                    ]);

                    foreach ($packages as $pkg) {
                        // Unik per PPK + nama_paket (sesuai unique index migration)
                        Package::firstOrCreate(
                            ['ppk_id' => $ppk->id,
                             'penyedia_jasa' => $pkg['penyedia_jasa'],
                             'nama_paket' => $pkg['nama_paket']],
                            ['price' => $pkg['price'] ?? 0,
                             'satker_id' => $satker->id, 
                             'lokasi' => $pkg['lokasi'] ?? null]
                        );
                    }
                }
            }
        });

        // 3) Item per Paket & Jenis (dengan default satuan, optional)
        $catsByCode    = JobCategory::pluck('id', 'code'); // ['GAL'=>1, 'PEM'=>2, 'FIN'=>3]
        $defaultUnitId = Unit::where('symbol', 'm²')->orWhere('name', 'Meter Persegi')->value('id');

        $catalog = [
            'PESA' => ['Galian Tanah', 
                      'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)', 
                      'Pekerjaan Plesteran',
                      'Pekerjaan Siaran'],
            'PEMBA' => ['Galian Tanah', 
                      'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)', 
                      'Pekerjaan Plesteran',
                      'Pekerjaan Siaran'],
            'PELENG' => ['Galian Tanah', 
                      'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)', 
                      'Pekerjaan Plesteran',
                      'Pekerjaan Siaran'],
        ];

        foreach (Package::cursor() as $pkg) {
            foreach ($catalog as $code => $names) {
                $catId = $catsByCode[$code] ?? null;
                if (!$catId) {
                    continue;
                }

                foreach ($names as $name) {
                    Item::firstOrCreate(
                        [
                            'package_id'      => $pkg->id,
                            'job_category_id' => $catId,
                            'name'            => $name,
                            'price'          => rand(100000, 5000000), 
                        ],
                        [
                            'default_unit_id' => $defaultUnitId, // boleh null jika tidak mau default
                        ]
                    );
                }
            }
        }

        $this->command?->info('Seeding selesai: JobCategories, Units, Satker/PPK/Packages, Items.');
    }
}
