<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Target;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use App\Models\DataSubmissionDetail;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LineChart extends ChartWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 2;
    protected ?string $heading = 'Line Chart';
    protected int|string|array $columnSpan = 'full';


    protected function getData(): array
    {
       
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $packageId = $this->filters['package_id'] ?? null;
        //  dd($this->filters);
        // === TARGET (kumulatif dari bobot) ===
        $tq = Target::query()->orderBy('tanggal');
        if ($packageId) $tq->where('packages_id', $packageId);
        if ($startDate) $tq->whereDate('tanggal', '>=', $startDate);
        if ($endDate)   $tq->whereDate('tanggal', '<=', $endDate);

        $baseline = 19.69;
        if ($startDate) {
            $baseline += Target::query()
                ->when($packageId, fn ($q) => $q->where('packages_id', $packageId))
                ->whereDate('tanggal', '<', $startDate)
                ->sum('bobot');
        }

        $targets = $tq->get(['tanggal', 'bobot']);

        // === MANUAL REALISASI (hardcode cumulative) ===
        $manualRealisasi = [
            '2025-08-31' => 20.120,
            '2025-09-01' => 20.120,
            '2025-09-02' => 20.120,
            '2025-09-03' => 20.120,
            '2025-09-04' => 20.790,
            '2025-09-05' => 20.790,
            '2025-09-06' => 20.790,
            '2025-09-07' => 20.790,
            '2025-09-08' => 20.790,
            '2025-09-09' => 20.790,
            '2025-09-10' => 21.700,
            '2025-09-11' => 21.700,
            '2025-09-12' => 21.700,
            '2025-09-13' => 21.700,
            '2025-09-14' => 21.700,
            '2025-09-15' => 21.700,
            '2025-09-16' => 21.700,
            '2025-09-17' => 22.730,
            '2025-09-18' => 22.730,
            '2025-09-19' => 22.730,
            '2025-09-20' => 22.730,
            '2025-09-21' => 22.730,
            '2025-09-22' => 22.730,
            '2025-09-23' => 26.320,
            '2025-09-24' => 26.320,
            '2025-09-25' => 26.320,
            '2025-09-26' => 29.230,
            '2025-09-27' => 29.230,
            '2025-09-28' => 29.230,
            '2025-09-29' => 29.230,
            '2025-09-30' => 29.230,
            '2025-10-01' => 29.230,
            '2025-10-02' => 30.590,
        ];

        // Filter manualRealisasi sesuai filter tanggal
        if ($startDate || $endDate) {
            $manualRealisasi = array_filter($manualRealisasi, function ($v, $k) use ($startDate, $endDate) {
                return (!$startDate || $k >= $startDate) && (!$endDate || $k <= $endDate);
            }, ARRAY_FILTER_USE_BOTH);
        }

        // === DETAIL REALISASI (harian, bukan cumulative) ===
        $dq = DataSubmissionDetail::with(['submission.package', 'item']);
        if ($packageId) $dq->whereHas('submission', fn ($q) => $q->where('package_id', $packageId));
        if ($startDate) $dq->whereHas('submission', fn ($q) => $q->whereDate('tanggal', '>=', $startDate));
        if ($endDate)   $dq->whereHas('submission', fn ($q) => $q->whereDate('tanggal', '<=', $endDate));

        $details = $dq->get()
            ->groupBy(fn ($detail) => $detail->submission->tanggal)
            ->map(function ($group) {
                return $group->sum(function ($d) {
                    $packagePrice = $d->submission->package->price ?: 1;
                    return ($d->volume * $d->item->price) / $packagePrice * 100;
                });
            });

        // === Gabung semua tanggal (target + manual + detail) ===
        $allDates = collect()
            ->merge($targets->pluck('tanggal')->map(fn ($t) => Carbon::parse($t)->toDateString()))
            ->merge(array_keys($manualRealisasi))
            ->merge($details->keys())
            ->unique()
            ->sort()
            ->values();

        // === Labels untuk chart ===
        $labels = $allDates->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray();

        // === Hitung target cumulative ===
        $targetCumulative = [];
        $targetTotal = $baseline;
        foreach ($allDates as $d) {
            $row = $targets->firstWhere('tanggal', $d);
            if ($row) {
                $targetTotal += $row->bobot;
            }
            $targetCumulative[] = round($targetTotal, 3);
        }

        // === Hitung realisasi cumulative ===
        $realisasiSeries = [];
        $cumulative = 0;
        foreach ($allDates as $d) {
            if (isset($manualRealisasi[$d])) {
                $cumulative = $manualRealisasi[$d];
            } elseif (isset($details[$d])) {
                $cumulative += $details[$d];
            }
            $realisasiSeries[] = round($cumulative, 3);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Target (%)',
                    'data' => array_values($targetCumulative),
                    'borderColor' => 'rgb(153, 102, 255)',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Realisasi (%)',
                    'data' => array_values($realisasiSeries),
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.15)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => array_values($labels),
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
