<?php

namespace App\Filament\Resources\Realizations\Pages;

use App\Filament\Resources\Realizations\RealizationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRealization extends EditRecord
{
    protected static string $resource = RealizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
