<?php

namespace App\Filament\Resources\Ppks\Pages;

use App\Filament\Resources\Ppks\PpkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPpk extends EditRecord
{
    protected static string $resource = PpkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
