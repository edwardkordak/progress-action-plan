<?php

namespace App\Filament\Widgets;

use App\Models\Target;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class LineChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Line Chart';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $packageId = $this->filters['package_id'] ?? null;

        // ===== TARGET (kumulatif dari bobot) =====
        $tq = Target::query()->orderBy('tanggal');
        if ($packageId) $tq->where('packages_id', $packageId);
        if ($startDate) $tq->whereDate('tanggal', '>=', $startDate);
        if ($endDate)   $tq->whereDate('tanggal', '<=', $endDate);

        $baseline = 15.73;
        if ($startDate) {
            $baseline += Target::query()
                ->when($packageId, fn ($q) => $q->where('packages_id', $packageId))
                ->whereDate('tanggal', '<', $startDate)
                ->sum('bobot');
        }

        $targets = $tq->get(['tanggal', 'bobot']);

        $targetCumulative = [];
        $targetTotal = $baseline;
        foreach ($targets as $row) {
            $targetTotal += $row->bobot;
            $targetCumulative[] = round($targetTotal, 3);
        }
        $labelDates = $targets->pluck('tanggal')->map(fn ($t) => \Carbon\Carbon::parse($t)->toDateString());
        $labels = $labelDates->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray();

        $manualRealisasi = [
            '2025-08-25' => 18.770,
            '2025-08-26' => 18.770,
            '2025-08-27' => 18.770,
            '2025-08-28' => 20.120,
            '2025-08-29' => 20.120,
            '2025-08-30' => 20.120,
            '2025-08-31' => 20.120,
            '2025-09-01' => 20.120,
            '2025-09-02' => 20.120,
            '2025-09-03' => 20.120,
            '2025-09-04' => 20.739,
            '2025-09-05' => 20.739,
            '2025-09-06' => 20.790,
            '2025-09-07' => 20.790,
            '2025-09-08' => 20.790,
            '2025-09-09' => 21.700,
            '2025-09-10' => 21.700,
            '2025-09-11' => 21.700,
            '2025-09-12' => 21.700,
            '2025-09-13' => 21.700,
            '2025-09-14' => 21.700,
            '2025-09-15' => 21.700,
        ];

        $realisasiSeries = [];
        foreach ($labelDates as $d) {
            $key = (string) $d; 
            $realisasiSeries[] = array_key_exists($key, $manualRealisasi)
                ? round(max(0, min(100, (float) $manualRealisasi[$key])), 3)
                : null;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Target (%)',
                    'data' => $targetCumulative,
                    'borderColor' => 'rgb(153, 102, 255)',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Realisasi (%)',
                    'data' => $realisasiSeries,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.15)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    ticks: {
                        callback: (value) => value + '%',
                    },
                },
            },
        }
        JS);
    }
}
