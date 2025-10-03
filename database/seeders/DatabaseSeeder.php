<?php

namespace Database\Seeders;

use App\Models\Ppk;
use App\Models\Item;

use App\Models\Unit;
use App\Models\User;
use App\Models\Satker;
use App\Models\Target;
use League\Csv\Reader;
use App\Models\Package;
use App\Models\JobCategory;
use App\Models\DataSubmission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\DataSubmissionDetail;

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
                    [
                        'nama_paket' => 'Rehabilitasi Daerah Irigasi Dataran Kotamobagu',
                        'penyedia_jasa' => 'PT. Nihara Anugerah',
                        'price' => 19389321000,
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
                        Package::firstOrCreate(
                            [
                                'ppk_id' => $ppk->id,
                                'penyedia_jasa' => $pkg['penyedia_jasa'],
                                'nama_paket' => $pkg['nama_paket']
                            ],
                            [
                                'price' => $pkg['price'] ?? 0,
                                'satker_id' => $satker->id,
                                'lokasi' => $pkg['lokasi'] ?? null
                            ]
                        );
                    }
                }
            }
        });

        $catsByCode    = JobCategory::pluck('id', 'code');
        $defaultUnitId = Unit::where('symbol', 'm²')
            ->orWhere('name', 'Meter Persegi')
            ->value('id');

        $catalog = [
            'PESA' => [
                [
                    'name'  => 'Galian Tanah',
                    'price' => 17500,
                    'unit'  => 2
                ],
                [
                    'name'  => 'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)',
                    'price' => 1098000,
                    'unit'  => 2
                ],
                [
                    'name'  => 'Pekerjaan Plesteran',
                    'price' => 70200,
                    'unit'  => 1
                ],
                [
                    'name'  => 'Pekerjaan Siaran',
                    'price' => 72800,
                    'unit'  => 1
                ],
            ],
            'PEMBA' => [
                [
                    'name'  => 'Galian Tanah',
                    'price' => 17500,
                    'unit' => 2
                ],
                [
                    'name'  => 'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)',
                    'price' => 1098000,
                    'unit'  => 2
                ],
                [
                    'name'  => 'Pekerjaan Plesteran',
                    'price' => 70200,
                    'unit'  => 1
                ],
                [
                    'name'  => 'Pekerjaan Siaran',
                    'price' => 72800,
                    'unit'  => 1
                ],
            ],
            'PELENG' => [
                [
                    'name'  => 'Galian Tanah',
                    'price' => 17500,
                    'unit'  => 2
                ],
                [
                    'name'  => 'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)',
                    'price' => 1098000,
                    'unit'  => 2
                ],
                [
                    'name'  => 'Pekerjaan Plesteran',
                    'price' => 70200,
                    'unit'  => 1
                ],
                [
                    'name'  => 'Pekerjaan Siaran',
                    'price' => 72800,
                    'unit'  => 1
                ],
            ],
        ];

        foreach (Package::cursor() as $pkg) {
            foreach ($catalog as $code => $items) {
                $catId = $catsByCode[$code] ?? null;
                if (!$catId) {
                    continue;
                }

                foreach ($items as $item) {
                    Item::firstOrCreate(
                        [
                            'package_id'      => $pkg->id,
                            'job_category_id' => $catId,
                            'name'            => $item['name'],
                        ],
                        [
                            'price'           => $item['price'],
                            'default_unit_id' => $item['unit'],
                        ]
                    );
                }
            }
        }


        $this->command?->info('Seeding selesai: JobCategories, Units, Satker/PPK/Packages, Items.');

        // Baca file CSV dari folder database/seeders/csv/targets.csv (buat folder sendiri)
        $csv = Reader::createFromPath(database_path('seeders/targets.csv'), 'r');
        $csv->setHeaderOffset(0); // Baris pertama jadi header

        $package = Package::first(); // atau cari berdasarkan nama_paket
        $packageId = $package->id;

        foreach ($csv as $record) {
            Target::create([
                'bobot'       => $record['bobot'],
                'tanggal'     => $record['tanggal'],
                'packages_id' => $packageId,
            ]);
        }
    }
}
