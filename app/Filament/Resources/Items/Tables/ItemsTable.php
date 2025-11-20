<?php

namespace App\Filament\Resources\Items\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Tables\Grouping\Group;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ToggleColumnsAction;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
         ->groups([
    Group::make('package.nama_paket')
        ->label('Nama Paket'),
])

            ->columns([
                TextColumn::make('name')
                    ->label('Item')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->limit(35)
                    ->wrap()
                    ->tooltip(fn ($record) => $record->name)
                    ->description(fn ($record) => 'Kategori: ' . $record->category?->name)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Harga')
                    ->badge()
                    ->money('idr')
                    ->sortable(),

                TextColumn::make('defaultUnit.name')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('package_id')
                    ->relationship('package', 'nama_paket')
                    ->searchable()
                    ->label('Filter Paket'),

                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->label('Jenis Pekerjaan'),
            ])

            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])

            // ->headerActions([
            //     ToggleColumnsAction::make(),
            // ])

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
