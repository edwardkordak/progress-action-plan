<?php

namespace App\Filament\Resources\Realizations;

use App\Filament\Resources\Realizations\Pages\CreateRealization;
use App\Filament\Resources\Realizations\Pages\EditRealization;
use App\Filament\Resources\Realizations\Pages\ListRealizations;
use App\Filament\Resources\Realizations\Schemas\RealizationForm;
use App\Filament\Resources\Realizations\Tables\RealizationsTable;
use App\Models\Realization;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RealizationResource extends Resource
{
    protected static ?string $model = Realization::class;

    // Navigation Icon (pakai string, bukan enum lama)
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $modelLabel = 'Realisasi';
    protected static ?string $pluralModelLabel = 'Realisasi';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Management';
    protected static ?string $recordTitleAttribute = 'Realization';

    public static function form(Schema $schema): Schema
    {
        return RealizationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RealizationsTable::configure($table);
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
            'index' => ListRealizations::route('/'),
            'create' => CreateRealization::route('/create'),
            'edit' => EditRealization::route('/{record}/edit'),
        ];
    }
}
