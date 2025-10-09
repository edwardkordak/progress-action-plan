<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\DataTarget;
use App\Models\DataSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeviasiWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // === RANGE TANGGAL ===
        $startDate = $this->filters['startDate']
            ? Carbon::parse($this->filters['startDate'])
            : Carbon::parse(DataTarget::min('tanggal') ?? now()->startOfMonth());

        $endDate = $this->filters['endDate']
            ? Carbon::parse($this->filters['endDate'])
            : Carbon::parse(DataTarget::max('tanggal') ?? now()->endOfMonth());

        $packageId = $this->filters['package_id'] ?? null;

        // === TARGET HARIAN ===
        $targets = DataTarget::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $totalTarget = 0;
        foreach ($targets as $t) {
            $pkgPrice = $t->package->price ?? 0;
            if ($pkgPrice == 0) continue;

            foreach ($t->details as $d) {
                $price = $d->item->price ?? 0;
                $volume = $d->volume ?? 0;
                $totalTarget += ($volume * $price / $pkgPrice) * 100;
            }
        }

        // === SUBMISSION HARIAN ===
        $subs = DataSubmission::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $totalSub = 0;
        foreach ($subs as $s) {
            $pkgPrice = $s->package->price ?? 0;
            if ($pkgPrice == 0) continue;

            foreach ($s->details as $d) {
                $price = $d->item->price ?? 0;
                $volume = $d->volume ?? 0;
                $totalSub += ($volume * $price / $pkgPrice) * 100;
            }
        }

        // === BASELINE (otomatis dari data sebelum tanggal start) ===
        $baselineTarget = DataTarget::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->where('tanggal', '<', $startDate)
            ->get()
            ->sum(function ($t) {
                $pkgPrice = $t->package->price ?? 0;
                if ($pkgPrice == 0) return 0;

                $sum = 0;
                foreach ($t->details as $d) {
                    $price = $d->item->price ?? 0;
                    $volume = $d->volume ?? 0;
                    $sum += ($volume * $price / $pkgPrice) * 100;
                }
                return $sum;
            });

        $baselineSub = DataSubmission::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->where('tanggal', '<', $startDate)
            ->get()
            ->sum(function ($s) {
                $pkgPrice = $s->package->price ?? 0;
                if ($pkgPrice == 0) return 0;

                $sum = 0;
                foreach ($s->details as $d) {
                    $price = $d->item->price ?? 0;
                    $volume = $d->volume ?? 0;
                    $sum += ($volume * $price / $pkgPrice) * 100;
                }
                return $sum;
            });

        // Tambahkan baseline ke total
        $totalTarget += $baselineTarget;
        $totalSub += $baselineSub;

        // === DEVIASI ===
        $deviasi = round($totalSub - $totalTarget, 2);

        // Format angka dua desimal
        $totalTarget = round($totalTarget, 2);
        $totalSub = round($totalSub, 2);

        return [
            Stat::make('Target', $totalTarget . '%')
                ->description('Total bobot target kumulatif')
                ->color('target'),

            Stat::make('Realisasi', $totalSub . '%')
                ->description('Total bobot realisasi kumulatif')
                ->color('realisasi'),

            Stat::make('Deviasi', $deviasi . '%')
                ->description('Realisasi - Target')
                ->color($deviasi >= 0 ? 'success' : 'danger'),
        ];
    }
}
