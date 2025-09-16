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
    $endDate = $this->filters['endDate'] ?? null;
    $packageId = $this->filters['package_id'] ?? null;

    $query = Target::query()->orderBy('tanggal');

    if ($packageId) {
        $query->where('packages_id', $packageId);
    }
    if ($startDate) {
        $query->whereDate('tanggal', '>=', $startDate);
    }
    if ($endDate) {
        $query->whereDate('tanggal', '<=', $endDate);
    }

    $baseline = 15.73;
    if ($startDate) {
        $baseline += Target::query()
            ->when($packageId, fn ($q) => $q->where('packages_id', $packageId))
            ->whereDate('tanggal', '<', $startDate)
            ->sum('bobot');
    }

    $data = $query->get(['tanggal', 'bobot']);

    $cumulative = [];
    $total = $baseline;

    foreach ($data as $row) {
        $total += $row->bobot;
        $cumulative[] = round($total, 2);
    }

    return [
        'datasets' => [
            [
                'label' => 'Target (%)',
                'data' => $cumulative,
                'borderColor' => 'rgb(153, 102, 255)',
                'backgroundColor' => 'rgba(153, 102, 255, 0.2)',
                'tension' => 0.4,
            ],
        ],
        'labels' => $data->pluck('tanggal')
            ->map(fn ($t) => Carbon::parse($t)->format('d M'))
            ->toArray(),
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
