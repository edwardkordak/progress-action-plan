<?php

namespace App\Filament\Resources\JobCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JobCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
