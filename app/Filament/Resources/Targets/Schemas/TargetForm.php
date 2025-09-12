<?php

namespace App\Filament\Resources\Targets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('packages_id')
                    ->required()
                    ->numeric(),
                TextInput::make('bobot')
                    ->required()
                    ->numeric(),
                DatePicker::make('tanggal')
                    ->required(),
            ]);
    }
}
