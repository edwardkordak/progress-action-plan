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
    // protected string $view = 'filament.pages.dashboard';

    use HasFiltersForm;
      protected static ?string $title = 'PROGRESS FISIK TA 2025';
    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('package_id')
                            ->label('Paket Pekerjaaan')
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
