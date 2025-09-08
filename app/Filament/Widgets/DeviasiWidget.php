<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Number;

class DeviasiWidget extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {

        return [
            Stat::make('Realisasi', '')
                ->description('Realisasi'),
            Stat::make('Target','')
                ->description('Target'),
            Stat::make('Deviasi', '')
                ->description('Deviasi')
        ];
    }
}
