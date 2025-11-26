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
        $startDate = $this->filters['startDate']
            ? Carbon::parse($this->filters['startDate'])
            : Carbon::parse(DataTarget::min('tanggal') ?? now()->startOfMonth());

        $endDate = $this->filters['endDate']
            ? Carbon::parse($this->filters['endDate'])
            : Carbon::parse(DataTarget::max('tanggal') ?? now()->endOfMonth());

        $packageId = $this->filters['package_id'] ?? 1;

        // $packageId = $this->filters['package_id'] ?? null;

        // === Buat range tanggal ===
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate->copy()->addDay());
        $dates = collect($period)->map(fn ($d) => $d->format('Y-m-d'))->toArray();

        // === Hitung baseline (data sebelum tanggal mulai) ===
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

        // === Ambil data target & submission ===
        $targets = DataTarget::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        $subs = DataSubmission::with(['details.item', 'package'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get();

        // === Hitung kumulatif harian ===
        $targetProgress = [];
        $submissionProgress = [];

        $cumTarget = $baselineTarget;
        $cumSub = $baselineSub;

        foreach ($dates as $date) {
            // Target harian
            $dayTarget = $targets->where('tanggal', $date)->sum(function ($t) {
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
            $cumTarget += $dayTarget;
            $targetProgress[] = round($cumTarget, 2);

            // Submission harian
            $daySub = $subs->where('tanggal', $date)->sum(function ($s) {
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
            $cumSub += $daySub;
            $submissionProgress[] = round($cumSub, 2);
        }

        // === Total akhir dan deviasi ===
        $totalTarget = end($targetProgress) ?: $baselineTarget;
        $totalSub = end($submissionProgress) ?: $baselineSub;
        $deviasi = round($totalSub - $totalTarget, 2);

        return [
            Stat::make('Target', $totalTarget . '%')
                ->description('Target Progres')
                  ->Icon('heroicon-m-arrow-trending-up')
                ->chart($targetProgress)
                ->color('target'),

            Stat::make('Realisasi', $totalSub . '%')
                ->description('Progres Realisasi')
                ->Icon('heroicon-m-arrow-trending-up')
                ->chart($submissionProgress)
                ->color('realisasi'),

            Stat::make('Deviasi', $deviasi . '%')
                ->description('Realisasi - Target')
                // ->Icon('heroicon-m-arrow-trending-down')
                ->chart(array_map(fn ($i) => round($submissionProgress[$i] - $targetProgress[$i], 2), array_keys($dates)))
                ->color($deviasi >= 0 ? 'success' : 'danger'),
        ];
    }
}
