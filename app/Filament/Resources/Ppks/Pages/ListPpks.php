<?php

namespace App\Filament\Resources\Ppks\Pages;

use App\Filament\Resources\Ppks\PpkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPpks extends ListRecords
{
    protected static string $resource = PpkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
