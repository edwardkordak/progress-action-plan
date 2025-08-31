<?php

namespace App\Filament\Resources\Ppks;

use App\Filament\Resources\Ppks\Pages\CreatePpk;
use App\Filament\Resources\Ppks\Pages\EditPpk;
use App\Filament\Resources\Ppks\Pages\ListPpks;
use App\Filament\Resources\Ppks\Schemas\PpkForm;
use App\Filament\Resources\Ppks\Tables\PpksTable;
use App\Models\Ppk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PpkResource extends Resource
{
    protected static ?string $model = Ppk::class;

    protected static ?string $modelLabel = 'PPK';
    protected static ?string $pluralModelLabel = 'PPK';

    // Navigation Group
    protected static string|\UnitEnum|null $navigationGroup = 'Options';

    // Navigation Icon (pakai string, bukan enum lama)
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    public static function form(Schema $schema): Schema
    {
        return PpkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PpksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPpks::route('/'),
            'create' => CreatePpk::route('/create'),
            'edit' => EditPpk::route('/{record}/edit'),
        ];
    }
}
