<?php

namespace App\Filament\Widgets;

use App\Models\Target;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class DeviasiWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // ===== Filters dari dashboard =====
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $packageId = $this->filters['package_id'] ?? null;

        // ===== Baseline target sebelum periode =====
        $baseline = 15.73;
        if ($startDate) {
            $baseline += Target::query()
                ->when($packageId, fn ($q) => $q->where('packages_id', $packageId))
                ->whereDate('tanggal', '<', $startDate)
                ->sum('bobot');
        }

        // ===== Ambil data target dalam rentang =====
        $tq = Target::query()->orderBy('tanggal');
        if ($packageId) $tq->where('packages_id', $packageId);
        if ($startDate) $tq->whereDate('tanggal', '>=', $startDate);
        if ($endDate)   $tq->whereDate('tanggal', '<=', $endDate);

        $rows = $tq->get(['tanggal', 'bobot']);

        // Series kumulatif TARGET untuk sparkline + nilai terakhir
        $seriesTarget = [];
        $cumTarget = $baseline;
        foreach ($rows as $r) {
            $cumTarget += (float) $r->bobot;
            $seriesTarget[] = round($cumTarget, 1);
        }
        $lastTarget = !empty($seriesTarget) ? end($seriesTarget) : round($baseline, 3);

        // ====== REALISASI MANUAL (tanpa carry-forward) ======
        // Format: 'YYYY-MM-DD' => persen kumulatif (decimal)
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

        // Susun seri Realisasi mengikuti tanggal data target (tanpa carry-forward)
        $labelDates = $rows->pluck('tanggal')->map(fn ($t) => \Carbon\Carbon::parse($t)->toDateString())->values();
        $seriesReal = [];
        foreach ($labelDates as $d) {
            $seriesReal[] = array_key_exists($d, $manualRealisasi)
                ? round((float) $manualRealisasi[$d], 1)
                : null; // null jika tidak ada data
        }

        // Cari nilai realisasi terakhir yang tidak null dalam rentang
        $lastReal = null;
        for ($i = count($seriesReal) - 1; $i >= 0; $i--) {
            if ($seriesReal[$i] !== null) {
                $lastReal = $seriesReal[$i];
                break;
            }
        }
        // Jika seluruh rentang kosong, coba ambil nilai manual terakhir sebelum startDate (opsional)
        if ($lastReal === null && $startDate) {
            $prevKeys = array_keys(array_filter($manualRealisasi, fn ($v, $k) => $k < $startDate, ARRAY_FILTER_USE_BOTH));
            if (!empty($prevKeys)) {
                $lastKey = end($prevKeys);
                $lastReal = round((float) $manualRealisasi[$lastKey], 1);
            }
        }

        // ===== Periode (untuk description) =====
        $from = $startDate ?? optional($rows->first())->tanggal;
        $to   = $endDate   ?? optional($rows->last())->tanggal;
        $period = null;
        if ($from || $to) {
            $fromStr = $from ? Carbon::parse($from)->format('d M Y') : '…';
            $toStr   = $to   ? Carbon::parse($to)->format('d M Y')   : '…';
            $period  = "{$fromStr} – {$toStr}";
        }

        // ===== Formatting dan deviasi =====
        $fmt = fn ($v) => \Illuminate\Support\Number::format($v) . '%';

        $hasReal = $lastReal !== null;
        $dev     = $hasReal ? round($lastReal - $lastTarget, 1) : null;
        $devFmt  = $dev !== null ? $fmt($dev) : 'Belum Ada';

        $devPos  = $dev !== null && $dev >= 0;
        $devColor = $dev === null ? 'gray' : ($devPos ? 'success' : 'danger');
        $devIcon  = $dev === null ? 'heroicon-o-minus' : ($devPos ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down');

        return [
            // ===== Kartu Target =====
            Stat::make('Target', $fmt($lastTarget))
                ->description($period ? "{$period}" : 'Periode aktif')
                ->descriptionIcon('heroicon-o-calendar')
                ->chart(!empty($seriesTarget) ? $seriesTarget : [$lastTarget])
                ->icon('heroicon-o-check-badge')
                ->color('target')
                ->extraAttributes(['class' => 'rounded-2xl']),

            // ===== Kartu Realisasi =====
            Stat::make('Realisasi', $hasReal ? $fmt($lastReal) : 'Belum Ada')
                ->description($period ? "{$period}" : 'Periode aktif')
                ->descriptionIcon('heroicon-o-flag')
                // null dibiarkan untuk putus; kalau mau mulus, ganti null dengan last-known.
                ->chart(!empty($seriesReal) ? array_map(fn ($v) => $v, $seriesReal) : ($hasReal ? [$lastReal] : [0]))
                ->icon('heroicon-o-flag')
                ->color('realisasi')
                ->extraAttributes(['class' => 'rounded-2xl']),

            // ===== Kartu Deviasi =====
            Stat::make('Deviasi', $devFmt)
                ->description($period ? "{$period}" : 'Periode aktif')
                ->descriptionIcon($devIcon)
                ->chart($dev !== null ? [$dev < 0 ? 0 : $dev / 2, $dev] : [0, 0]) // sparkline sederhana
                ->color($devColor)
                ->icon('heroicon-o-scale')
                ->extraAttributes(['class' => 'rounded-2xl']),
        ];
    }
}
