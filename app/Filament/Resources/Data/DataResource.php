<?php

namespace App\Filament\Resources\Data;

use App\Filament\Resources\Data\Pages\CreateData;
use App\Filament\Resources\Data\Pages\EditData;
use App\Filament\Resources\Data\Pages\ListData;
use App\Filament\Resources\Data\Schemas\DataForm;
use App\Filament\Resources\Data\Tables\DataTable;
use App\Models\Data;
use App\Models\DataSubmission;
use App\Models\DataSubmissionDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataResource extends Resource
{
    protected static ?string $model = DataSubmission::class;
    

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $modelLabel = 'Data Submission';
    protected static ?string $pluralModelLabel = 'Data Submissions';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Management';
    protected static ?string $recordTitleAttribute = 'DataSubmission';

    public static function form(Schema $schema): Schema
    {
        return DataForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataTable::configure($table);
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
            'index' => ListData::route('/'),
            'create' => CreateData::route('/create'),
            'edit' => EditData::route('/{record}/edit'),
        ];
    }
}
