<?php

namespace App\Filament\Resources\Data\Pages;

use App\Filament\Resources\Data\DataResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListData extends ListRecords
{
    protected static string $resource = DataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
