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
        // Ambil filter dari Dashboard
        $startDate = $this->filters['startDate'] ?? null;
        $endDate   = $this->filters['endDate']   ?? null;
        $packageId = $this->filters['package_id'] ?? null;

        // Baseline awal + akumulasi sebelum startDate
        $baseline = 15.73;
        if ($startDate) {
            $baseline += Target::query()
                ->when($packageId, fn ($q) => $q->where('packages_id', $packageId))
                ->whereDate('tanggal', '<', $startDate)
                ->sum('bobot');
        }

        // Data dalam rentang
        $q = Target::query()->orderBy('tanggal');
        if ($packageId) $q->where('packages_id', $packageId);
        if ($startDate) $q->whereDate('tanggal', '>=', $startDate);
        if ($endDate)   $q->whereDate('tanggal', '<=', $endDate);

        $rows = $q->get(['tanggal', 'bobot']);

        // Series kumulatif untuk sparkline
        $series = [];
        $cum = $baseline;
        foreach ($rows as $r) {
            $cum += (float) $r->bobot;
            $series[] = round($cum, 2);
        }

        // Nilai Realisasi terakhir (atau baseline jika kosong)
        $realisasi = ! empty($series) ? end($series) : round($baseline, 2);

        // Periode untuk deskripsi
        $from = $startDate ?? optional($rows->first())->tanggal;
        $to   = $endDate   ?? optional($rows->last())->tanggal;
        $period = null;
        if ($from || $to) {
            $fromStr = $from ? Carbon::parse($from)->format('d M Y') : '…';
            $toStr   = $to   ? Carbon::parse($to)->format('d M Y')   : '…';
            $period  = "{$fromStr} – {$toStr}";
        }

        $fmt = fn ($v) => Number::format($v) . '%';

        return [
            Stat::make('Target', $fmt($realisasi))
                ->description($period ? "{$period}" : 'Periode aktif')
                ->descriptionIcon('heroicon-o-calendar')
                ->chart(! empty($series) ? $series : [$realisasi]) // sparkline
                ->icon('heroicon-o-check-badge')
                ->color('info')
                ->extraAttributes(['class' => 'rounded-2xl']),

                
            Stat::make('Realisasi', 'Belum Ada')
                ->description($period ? "{$period}" : 'Periode aktif')
                ->descriptionIcon('heroicon-o-calendar')
                ->descriptionIcon('heroicon-o-flag')
                ->chart([15.73, 100]) // sparkline sederhana
                ->color('success')
                ->icon('heroicon-o-flag')
                ->extraAttributes(['class' => 'rounded-2xl']),

            // ===== Kartu Deviasi =====
            Stat::make('Deviasi','Belum Ada')
                ->description($period ? "{$period}" : 'Periode aktif')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->chart([15.73, 100]) 
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle')
                ->extraAttributes(['class' => 'rounded-2xl']),
        ];
    }
}
