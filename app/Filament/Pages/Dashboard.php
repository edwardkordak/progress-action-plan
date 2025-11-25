<?php

namespace App\Filament\Pages;

use App\Models\Package;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use App\Filament\Widgets\LineChart;
use Filament\Forms\Components\Select;
use App\Filament\Widgets\DeviasiWidget;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use App\Filament\Widgets\DeviasiTableWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'PROGRESS FISIK TA 2025';

    /**
     * Default filter values saat dashboard load pertama kali
     */
    protected function getDefaultFilters(): array
    {
        return [
            'package_id' => 1,   // default ambil package id = 1
            'startDate'  => null,
            'endDate'    => null,
        ];
    }

    /**
     * Filter form schema
     */
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
