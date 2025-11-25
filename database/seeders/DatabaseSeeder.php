<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User,
    Satker,
    Ppk,
    Package,
    JobCategory,
    Unit,
    Item,
    DataTarget,
    DataTargetDetail,
    DataSubmission,
    DataSubmissionDetail
};

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /** === STEP 1: Data dasar === */
          User::factory()->create([
            'name' => 'Test User',
            'email' => 'bwss1@example.com',
        ]);
        collect([
            ['code' => 'PESA', 'name' => 'Pekerjaan Saluran', 'sort_order' => 1],
            ['code' => 'PEMBA', 'name' => 'Pekerjaan Bangunan Utama', 'sort_order' => 2],
            ['code' => 'PELENG', 'name' => 'Pekerjaan Bangunan Pelengkap', 'sort_order' => 9],
            ['code' => 'PEUNG', 'name' => 'Pekerjaan Bendung', 'sort_order' => 4],
            ['code' => 'PESAI', 'name' => 'Pekerjaan Saluran Induk', 'sort_order' => 5],
            ['code' => 'PESASE', 'name' => 'Pekerjaan Saluran Sekunder', 'sort_order' => 6],
            ['code' => 'PESAPE', 'name' => 'Pekerjaan Saluran Pembuang', 'sort_order' => 7],
            ['code' => 'PEBASA', 'name' => 'Pekerjaan Bangunan Sadap', 'sort_order' => 8],
        ])->each(fn ($jc) => JobCategory::firstOrCreate(['code' => $jc['code']], $jc));

        collect([
            ['name' => 'Meter Persegi', 'symbol' => 'm²'],
            ['name' => 'Meter Kubik', 'symbol' => 'm³'],
        ])->each(fn ($u) => Unit::firstOrCreate(['name' => $u['name']], $u));

        $satker = Satker::firstOrCreate(['name' => 'SNVT PJPA']);
        $ppk    = Ppk::firstOrCreate(['satker_id' => $satker->id, 'name' => 'Irigasi dan Rawa']);
        $pkg    = Package::firstOrCreate([
            'ppk_id' => $ppk->id,
            'nama_paket' => 'Rehabilitasi Daerah Irigasi Dataran Kotamobagu',
        ], [
            'satker_id' => $satker->id,
            'penyedia_jasa' => 'PT. Nihara Anugerah',
            'price' => 19389321000,
            'lokasi' => 'Kab. Bolaang Mongondow Timur',
        ]);

        $this->command->info("✅ Dasar selesai: JobCategories, Units, Satker/PPK/Package.");

        /** === STEP 2: Items === */
        $cats = JobCategory::pluck('id', 'code');
        $catalog = [
            'PESA' => [
                ['Galian Tanah', 17500, 7965, 2],
                ['Pasangan Batu Mortar Tipe N (1 PC:4 PP)', 1098000, 15128.93, 2],
                ['Plesteran', 70200, 16899.5, 1],
                ['Siaran', 72800, 13974.22, 1],
            ],
            'PEMBA' => [
                ['Galian Tanah', 17500, 179.79, 2],
                ['Pasangan Batu Mortar Tipe N', 1098000, 205.23, 2],
                ['Plesteran', 70200, 281.71, 1],
                ['Siaran', 72798, 128.61, 1],
            ],
            'PELENG' => [
                ['Galian Tanah', 17500, 56.48, 2],
                ['Pasangan Batu Mortar Tipe N', 1097984, 145.39, 2],
                ['Plesteran', 70198, 203.65, 1],
                ['Siaran', 72800, 29.46, 1],
            ],
        ];

        foreach ($catalog as $code => $items) {
            foreach ($items as [$name, $price, $volume, $unit]) {
                Item::firstOrCreate([
                    'package_id' => $pkg->id,
                    'job_category_id' => $cats[$code],
                    'name' => $name,
                ], compact('price', 'volume') + ['default_unit_id' => $unit]);
            }
        }

        $this->command->info("✅ Items selesai.");

        /** === MAPPING === */
        $mapJob  = ['A' => 1, 'B' => 2, 'C' => 3];
        $mapItem = [
            'A1' => 1, 'A2' => 2, 'A3' => 3, 'A4' => 4,
            'B1' => 5, 'B2' => 6, 'B3' => 7, 'B4' => 8,
            'C1' => 9, 'C2' => 10, 'C3' => 11, 'C4' => 12,
        ];

        /** === STEP 3: Target dari CSV === */
        $targetCsv = database_path('seeders/TargetTerbaru.csv');
        if (file_exists($targetCsv)) {
            $count = $this->importCsvData($targetCsv, DataTarget::class, 
            DataTargetDetail::class, $satker, $ppk, $pkg, $mapJob, $mapItem);
            $this->command->info("✅ Target CSV selesai! Total detail diimpor: {$count}");
        } else {
            $this->command->warn("⚠️ File TargetTerbaru.csv tidak ditemukan!");
        }

        /** === STEP 4: Submission dari CSV === */
        $submissionCsv = database_path('seeders/SubmissionTerbaru.csv');
        if (file_exists($submissionCsv)) {
            $count = $this->importCsvData(
                $submissionCsv,
                DataSubmission::class,
                DataSubmissionDetail::class,
                $satker,
                $ppk,
                $pkg,
                $mapJob,
                $mapItem,
                [
                    'penyedia_jasa' => 'PT. Mihara Anugera',
                    'nama' => 'Juan',
                    'jabatan' => 'Staff',
                    'lokasi' => 'Kab. Bolaang Mongondow Timur',
                ]
            );
            $this->command->info("✅ Submission CSV selesai! Total detail diimpor: {$count}");
        } else {
            $this->command->warn("⚠️ File SubmissionTerbaru.csv tidak ditemukan!");
        }
    }

    /**
     * Import CSV untuk DataTarget / DataSubmission
     */
    private function importCsvData($file, $mainModel, $detailModel, $satker, $ppk, $pkg, $mapJob, $mapItem, $extra = []): int
    {
        $handle = fopen($file, 'r');
        $header = array_map(fn ($h) => trim(preg_replace('/[\xEF\xBB\xBF]/', '', $h)), fgetcsv($handle));
        $count = 0;

        DB::transaction(function () use ($handle, $header, $mainModel, $detailModel, $satker, $ppk, $pkg, $mapJob, $mapItem, $extra, &$count) {
            while (($row = fgetcsv($handle)) !== false) {
                $data = @array_combine($header, $row);
                if (!$data) continue;

                $tanggal = Carbon::parse(str_replace('/', '-', reset($data)))->format('Y-m-d');

                $base = [
                    'satker_id' => $satker->id,
                    'ppk_id' => $ppk->id,
                    'package_id' => $pkg->id,
                    'tanggal' => $tanggal,
                ];

                $record = $mainModel::firstOrCreate($base, $extra);

                foreach ($data as $col => $val) {
                    if (preg_match('/([ABC]\d+)/', $col, $m)) {
                        $code = $m[1];
                        $itemId = $mapItem[$code] ?? null;
                        $jobId = $mapJob[$code[0]] ?? null;
                        if (!$itemId || !$jobId) continue;

                        $detailModel::updateOrCreate([
                            $mainModel === DataTarget::class ? 'data_target_id' : 'data_submission_id' => $record->id,
                            'item_id' => $itemId,
                        ], [
                            'job_category_id' => $jobId,
                            'volume' => is_numeric($val) ? (float)$val : 0,
                            'satuan_id' => $detailModel === DataSubmissionDetail::class
                                ? (Item::find($itemId)->default_unit_id ?? null)
                                : null,
                            'keterangan' => null,
                            'updated_at' => now(),
                        ]);
                        $count++;
                    }
                }
            }
        });

        fclose($handle);
        return $count;
    }
}
