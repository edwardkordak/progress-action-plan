<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('package_id')
                    ->relationship('package', 'nama_paket')
                    ->label('Paket Pekerjaan')
                    ->required(),
                Select::make('job_category_id')
                    ->label('Jenis Pekerjaan')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->required(),
                Select::make('default_unit_id')
                    ->label('Satuan Volume')
                    ->relationship('defaultUnit', 'name')
                    ->default(null),
            ]);
    }
}
