<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\DataTarget;
use App\Models\DataSubmission;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Support\RawJs;

class LineChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected ?string $heading = 'Line Chart';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $packageId = $this->filters['package_id'] ?? null;

        // === Jika package belum dipilih, kembalikan chart kosong ===
        if (!$packageId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        // === RANGE TANGGAL ===
        $firstDate = DataTarget::min('tanggal');
        $lastDate  = DataTarget::max('tanggal');

        $startDate = $this->filters['startDate']
            ? Carbon::parse($this->filters['startDate'])
            : Carbon::parse($firstDate ?? now()->startOfMonth());

        $endDate = $this->filters['endDate']
            ? Carbon::parse($this->filters['endDate'])
            : Carbon::parse($lastDate ?? now()->endOfMonth());

        // Semua tanggal dalam rentang
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->copy()->addDay());
        $allDates = collect($period)->map(fn($d) => $d->format('Y-m-d'))->toArray();

        // === TARGET HARIAN ===
        $targets = DataTarget::with(['details.item', 'package'])
            ->when($packageId, fn($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $dailyTarget = [];
        foreach ($targets as $target) {
            $packagePrice = $target->package->price ?? 0;
            if ($packagePrice == 0) continue;

            $bobot = 0;
            foreach ($target->details as $detail) {
                $price = $detail->item->price ?? 0;
                $volume = $detail->volume ?? 0;
                $bobot += ($volume * $price / $packagePrice) * 100;
            }
            $dailyTarget[$target->tanggal] = round($bobot, 2);
        }

        // === REALISASI HARIAN ===
        $submissions = DataSubmission::with(['details.item', 'package'])
            ->when($packageId, fn($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->get();

        $dailySubmission = [];
        foreach ($submissions as $sub) {
            $packagePrice = $sub->package->price ?? 0;
            if ($packagePrice == 0) continue;

            $bobot = 0;
            foreach ($sub->details as $detail) {
                $price = $detail->item->price ?? 0;
                $volume = $detail->volume ?? 0;
                $bobot += ($volume * $price / $packagePrice) * 100;
            }
            $dailySubmission[$sub->tanggal] = round($bobot, 2);
        }

        // === BASELINE ===
        $baselineTarget = DataTarget::with(['details.item', 'package'])
            ->when($packageId, fn($q) => $q->where('package_id', $packageId))
            ->where('tanggal', '<', $startDate)
            ->get()
            ->sum(function ($target) {
                $packagePrice = $target->package->price ?? 0;
                if ($packagePrice == 0) return 0;
                $bobot = 0;
                foreach ($target->details as $detail) {
                    $price = $detail->item->price ?? 0;
                    $volume = $detail->volume ?? 0;
                    $bobot += ($volume * $price / $packagePrice) * 100;
                }
                return $bobot;
            });

        $baselineSubmission = DataSubmission::with(['details.item', 'package'])
            ->when($packageId, fn($q) => $q->where('package_id', $packageId))
            ->where('tanggal', '<', $startDate)
            ->get()
            ->sum(function ($sub) {
                $packagePrice = $sub->package->price ?? 0;
                if ($packagePrice == 0) return 0;
                $bobot = 0;
                foreach ($sub->details as $detail) {
                    $price = $detail->item->price ?? 0;
                    $volume = $detail->volume ?? 0;
                    $bobot += ($volume * $price / $packagePrice) * 100;
                }
                return $bobot;
            });

        // === KUMULATIF ===
        $targetSum = $baselineTarget;
        $submissionSum = $baselineSubmission;

        $targetCumulative = [];
        $submissionCumulative = [];

        foreach ($allDates as $date) {
            $targetSum += $dailyTarget[$date] ?? 0;
            $submissionSum += $dailySubmission[$date] ?? 0;
            $targetCumulative[] = round($targetSum, 3);
            $submissionCumulative[] = round($submissionSum, 3);
        }

        // === LABEL TANGGAL ===
        $labels = array_map(fn($d) => Carbon::parse($d)->format('d M'), $allDates);

        return [
            'datasets' => [
                [
                    'label' => 'Target (%)',
                    'data' => $targetCumulative,
                    'borderColor' => 'rgb(153, 102, 255)',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Realisasi (%)',
                    'data' => $submissionCumulative,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4,
                    'fill' => true,
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
                    beginAtZero: true
                },
                x: {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 10
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            }
        }
        JS);
    }
}
