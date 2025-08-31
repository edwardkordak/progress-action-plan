<?php

namespace App\Filament\Resources\Ppks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PpkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('satker_id')
                    ->label('Satuan Kerja')
                    ->relationship('satker', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
