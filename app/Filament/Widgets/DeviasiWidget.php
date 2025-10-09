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
    // protected ?string $heading = 'Statistik Progres';
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate']
            ? Carbon::parse($this->filters['startDate'])
            : DataTarget::min('tanggal') ?? Carbon::now()->startOfMonth();

        $endDate = $this->filters['endDate']
            ? Carbon::parse($this->filters['endDate'])
            : DataTarget::max('tanggal') ?? Carbon::now()->endOfMonth();

        $packageId = $this->filters['package_id'] ?? null;

        // === Target ===
        $targets = DataTarget::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $totalTarget = 0;
        foreach ($targets as $t) {
            $pkgPrice = $t->package->price ?? 0;
            foreach ($t->details as $d) {
                $totalTarget += ($d->volume * ($d->item->price ?? 0) / $pkgPrice) * 100;
            }
        }

        // === Submission ===
        $subs = DataSubmission::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $totalSub = 30.82;
        foreach ($subs as $s) {
            $pkgPrice = $s->package->price ?? 0;
            foreach ($s->details as $d) {
                $totalSub += ($d->volume * ($d->item->price ?? 0) / $pkgPrice) * 100;
            }
        }

        // === Deviasi ===
        $deviasi = round($totalSub - $totalTarget, 2);

        // Format angka dua desimal
        $totalTarget = round($totalTarget, 2);
        $totalSub = round($totalSub, 2);

        return [
            Stat::make('Target', $totalTarget . '%')
                ->description('Total bobot target kumulatif')
                // ->descriptionIcon('heroicon-o-bullseye')
                ->color('target'),

            Stat::make('Realisasi', $totalSub . '%')
                ->description('Total bobot realisasi kumulatif')
                // ->descriptionIcon('heroicon-o-check-circle')
                ->color('realisasi'),

            Stat::make('Deviasi', $deviasi . '%')
                ->description('Realisasi - Target')
                // ->descriptionIcon($deviasi >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                ->color($deviasi >= 0 ? 'success' : 'danger'),
        ];
    }
}
