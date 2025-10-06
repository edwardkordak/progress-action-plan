<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Target;
use App\Models\DataSubmissionDetail;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DeviasiTableWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected static ?string $heading = 'Progress Action Plan';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate'] ?? null;
        $packageId = $this->filters['package_id'] ?? null;
        // dd($this->filters);
        // === TARGET baseline ===
        $baseline = 19.69;
        if ($startDate) {
            $baseline += Target::query()
                ->when($packageId, fn ($q) => $q->where('packages_id', $packageId))
                ->whereDate('tanggal', '<', $startDate)
                ->sum('bobot');
        }

        $tq = Target::query()->orderBy('tanggal');
        if ($packageId) $tq->where('packages_id', $packageId);
        if ($startDate) $tq->whereDate('tanggal', '>=', $startDate);
        if ($endDate)   $tq->whereDate('tanggal', '<=', $endDate);
        $targets = $tq->get(['tanggal', 'bobot']);

        // === MANUAL realisasi ===
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
        if ($startDate || $endDate) {
            $manualRealisasi = array_filter($manualRealisasi, function ($v, $k) use ($startDate, $endDate) {
                return (!$startDate || $k >= $startDate) && (!$endDate || $k <= $endDate);
            }, ARRAY_FILTER_USE_BOTH);
        }

        // === DETAIL realisasi ===
        $dq = DataSubmissionDetail::with(['submission.package', 'item']);
        if ($packageId) $dq->whereHas('submission', fn ($q) => $q->where('package_id', $packageId));
        if ($startDate) $dq->whereHas('submission', fn ($q) => $q->whereDate('tanggal', '>=', $startDate));
        if ($endDate)   $dq->whereHas('submission', fn ($q) => $q->whereDate('tanggal', '<=', $endDate));

        $details = $dq->get()
            ->groupBy(fn ($d) => $d->submission->tanggal)
            ->map(fn ($g) => $g->sum(fn ($d) => ($d->volume * $d->item->price) / ($d->submission->package->price ?: 1) * 100));

        // === Semua tanggal gabungan ===
        $allDates = collect()
            ->merge($targets->pluck('tanggal')->map(fn ($t) => Carbon::parse($t)->toDateString()))
            ->merge(array_keys($manualRealisasi))
            ->merge($details->keys())
            ->unique()
            ->filter(fn ($d) => Carbon::parse($d)->lte(Carbon::today())) // ambil hanya <= hari ini
            ->sort()
            ->values();

        // === Build rows ===
        $rows = [];
        $cumTarget = $baseline;
        $cumReal = 0;

        foreach ($allDates as $d) {
            $rowTarget = $targets->firstWhere('tanggal', $d);
            if ($rowTarget) {
                $cumTarget += (float) $rowTarget->bobot;
            }

            if (isset($manualRealisasi[$d])) {
                $cumReal = $manualRealisasi[$d];
            } elseif (isset($details[$d])) {
                $cumReal += $details[$d];
            }

            $rows[] = [
                'tanggal'   => $d,
                'target'    => round($cumTarget, 2),
                'realisasi' => round($cumReal, 2),
                'deviasi'   => round($cumReal - $cumTarget, 2),
            ];
        }

        // === Urutkan terbaru di atas ===
        $rows = collect($rows)
            ->sortByDesc('tanggal')
            ->take(10) 
            ->sortByDesc('tanggal')
            ->values();

        return $table
            ->records(fn () => $rows)
            ->columns([
                TextColumn::make('tanggal')->label('Tanggal')->sortable(),
                TextColumn::make('target')->label('Target (%)')->sortable(),
                TextColumn::make('realisasi')->label('Realisasi (%)')->sortable(),
                TextColumn::make('deviasi')
                    ->label('Deviasi (%)')
                    ->color(fn ($record) => $record['deviasi'] >= 0 ? 'success' : 'danger')
                    ->sortable(),
            ]);
    }
}
