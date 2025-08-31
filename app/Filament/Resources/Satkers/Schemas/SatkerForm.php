<?php

namespace App\Filament\Resources\Satkers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SatkerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
            ]);
    }
}
