<?php

namespace App\Filament\Widgets;

use App\Models\Realization;
use App\Models\Package;
use Filament\Widgets\ChartWidget;

class RealisasiChart extends ChartWidget
{
    protected ?string $heading = 'Realisasi Chart';
    protected int|string|array $columnSpan = 'full';

    // 🟢 Tambahkan filters
    protected function getFilters(): ?array
    {
        return Package::pluck('nama_paket', 'id')->toArray();
    }

    protected function getData(): array
    {
        // ambil filter aktif (default null)
        $packageId = $this->filter;

        // kalau ada filter, ambil data berdasarkan packages_id
        $query = Realization::query()->orderBy('tanggal');

        if ($packageId) {
            $query->where('packages_id', $packageId);
        }

        $data = $query->get(['tanggal', 'bobot']);

        // bikin kumulatif mulai dari nilai awal
        $cumulative = [];
        $total = 15.73; // contoh nilai awal

        foreach ($data as $row) {
            $total += $row->bobot;
            $cumulative[] = round($total, 3);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Bobot Kumulatif',
                    'data' => $cumulative,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $data->pluck('tanggal')
                ->map(fn ($t) => \Carbon\Carbon::parse($t)->format('d M'))
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
