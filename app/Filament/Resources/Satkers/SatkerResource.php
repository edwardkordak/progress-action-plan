<?php

namespace App\Filament\Resources\Satkers;

use App\Filament\Resources\Satkers\Pages\CreateSatker;
use App\Filament\Resources\Satkers\Pages\EditSatker;
use App\Filament\Resources\Satkers\Pages\ListSatkers;
use App\Filament\Resources\Satkers\Schemas\SatkerForm;
use App\Filament\Resources\Satkers\Tables\SatkersTable;
use App\Models\Satker;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SatkerResource extends Resource
{
    protected static ?string $model = Satker::class;

      // Label Resource
    protected static ?string $modelLabel = 'Satuan Kerja';
    protected static ?string $pluralModelLabel = 'Satuan Kerja';

    // Navigation Group
    protected static string|\UnitEnum|null $navigationGroup = 'Options';

    // Navigation Icon (pakai string, bukan enum lama)
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';


    protected static ?string $recordTitleAttribute = 'Satker';

    public static function form(Schema $schema): Schema
    {
        return SatkerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SatkersTable::configure($table);
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
            'index' => ListSatkers::route('/'),
            'create' => CreateSatker::route('/create'),
            'edit' => EditSatker::route('/{record}/edit'),
        ];
    }
}
