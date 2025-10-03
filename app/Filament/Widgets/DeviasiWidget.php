<?php

namespace App\Filament\Widgets;

use App\Models\Target;
use App\Models\DataSubmissionDetail;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class DeviasiWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $packageId = $this->filters['package_id'] ?? null;

        // === TARGET ===
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

        // === MANUAL REALISASI (cumulative) ===
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
        // Filter manual sesuai periode
        if ($startDate || $endDate) {
            $manualRealisasi = array_filter($manualRealisasi, function ($v, $k) use ($startDate, $endDate) {
                return (!$startDate || $k >= $startDate) && (!$endDate || $k <= $endDate);
            }, ARRAY_FILTER_USE_BOTH);
        }

        // === DETAIL REALISASI (harian) ===
        $dq = DataSubmissionDetail::with(['submission.package', 'item']);
        if ($packageId) $dq->whereHas('submission', fn ($q) => $q->where('package_id', $packageId));
        if ($startDate) $dq->whereHas('submission', fn ($q) => $q->whereDate('tanggal', '>=', $startDate));
        if ($endDate)   $dq->whereHas('submission', fn ($q) => $q->whereDate('tanggal', '<=', $endDate));

        $details = $dq->get()
            ->groupBy(fn ($d) => $d->submission->tanggal)
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

        // === Series Target cumulative ===
        $seriesTarget = [];
        $cumTarget = $baseline;
        foreach ($allDates as $d) {
            $row = $targets->firstWhere('tanggal', $d);
            if ($row) {
                $cumTarget += (float) $row->bobot;
            }
            $seriesTarget[] = round($cumTarget, 1);
        }
        $lastTarget = !empty($seriesTarget) ? end($seriesTarget) : round($baseline, 1);

        // === Series Realisasi cumulative ===
        $seriesReal = [];
        $cumulative = 0;
        foreach ($allDates as $d) {
            if (isset($manualRealisasi[$d])) {
                $cumulative = $manualRealisasi[$d];
            } elseif (isset($details[$d])) {
                $cumulative += $details[$d];
            }
            $seriesReal[] = round($cumulative, 1);
        }
        $lastReal = !empty($seriesReal) ? end($seriesReal) : null;

        // === Deviasi ===
        $hasReal = $lastReal !== null;
        $dev     = $hasReal ? round($lastReal - $lastTarget, 1) : null;

        $fmt = fn ($v) => Number::format($v) . '%';

        $devFmt   = $dev !== null ? $fmt($dev) : 'Belum Ada';
        $devPos   = $dev !== null && $dev >= 0;
        $devColor = $dev === null ? 'gray' : ($devPos ? 'success' : 'danger');
        $devIcon  = $dev === null ? 'heroicon-o-minus' : ($devPos ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down');

        // === Periode label ===
        $from = $startDate ?? $allDates->first();
        $to   = $endDate   ?? $allDates->last();
        $period = null;
        if ($from || $to) {
            $fromStr = $from ? Carbon::parse($from)->format('d M Y') : '…';
            $toStr   = $to   ? Carbon::parse($to)->format('d M Y')   : '…';
            $period  = "{$fromStr} – {$toStr}";
        }

        return [
            Stat::make('Target', $fmt($lastTarget))
                ->description($period ?? 'Periode aktif')
                ->descriptionIcon('heroicon-o-calendar')
                ->chart($seriesTarget)
                ->icon('heroicon-o-check-badge')
                ->color('target')
                ->extraAttributes(['class' => 'rounded-2xl']),

            Stat::make('Realisasi', $hasReal ? $fmt($lastReal) : 'Belum Ada')
                ->description($period ?? 'Periode aktif')
                ->descriptionIcon('heroicon-o-flag')
                ->chart($seriesReal)
                ->icon('heroicon-o-flag')
                ->color('realisasi')
                ->extraAttributes(['class' => 'rounded-2xl']),

            Stat::make('Deviasi', $devFmt)
                ->description($period ?? 'Periode aktif')
                ->descriptionIcon($devIcon)
                ->chart($dev !== null ? [$dev < 0 ? 0 : $dev / 2, $dev] : [0, 0])
                ->color($devColor)
                ->icon('heroicon-o-scale')
                ->extraAttributes(['class' => 'rounded-2xl']),
        ];
    }
}
