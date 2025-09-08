<?php

namespace App\Filament\Pages;

use App\Models\Package;
use Filament\Schemas\Schema;
use App\Filament\Widgets\BarChart;
use App\Filament\Widgets\DeviasiWidget;
use App\Filament\Widgets\LineChart;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;


class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getWidgets(): array
    {
        return [
            DeviasiWidget::class,
            // BarChart::class,
            LineChart::class,
           
        ];
    }


    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('package_id')
                            ->label('Paket Pekerjaan')
                            ->options(Package::pluck('nama_paket', 'id'))
                            ->searchable()
                            ->placeholder('Pilih Paket')
                            ->live(),

                        DatePicker::make('startDate')
                            ->label('Tanggal Mulai')
                            ->live(),


                        DatePicker::make('endDate')
                            ->label('Tanggal Selesai')
                            ->live(),

                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
