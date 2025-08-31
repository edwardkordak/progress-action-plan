<?php

namespace App\Filament\Resources\Realizations\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

class RealizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('packages_id')
                    ->relationship('package', 'nama_paket')
                    ->required(),
                TextInput::make('bobot')
                    ->required()
                    ->numeric(),
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->date('Y-m-d')
                    ->required(),
            ]);
    }
}
