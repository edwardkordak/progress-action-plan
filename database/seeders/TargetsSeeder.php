<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Target;

class TargetsSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('app/public/target.csv');
        if (! file_exists($path)) {
            $this->command?->warn("CSV tidak ditemukan: $path");
            return;
        }

        if (($h = fopen($path, 'r')) === false) {
            $this->command?->error("Gagal membuka CSV");
            return;
        }

        // lewati header
        fgetcsv($h);

        while (($row = fgetcsv($h)) !== false) {
            if (count($row) < 3) continue;

            $bobot      = (float) str_replace(',', '.', trim($row[0] ?? ''));
            $tanggal    = trim($row[1] ?? '');
            $packagesId = (int) trim($row[2] ?? '');

            if ($tanggal === '' || $packagesId === 0) continue;

            $t = new Target();
            $t->bobot       = $bobot;
            $t->tanggal     = $tanggal;
            $t->packages_id = $packagesId;
            $t->save();
        }

        fclose($h);
    }
}
