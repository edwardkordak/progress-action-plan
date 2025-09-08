<?php

namespace App\Filament\Resources\Items\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('package.nama_paket')
                    ->label('Nama Paket')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->package?->nama_paket)
                    ->sortable()
                    ->searchable(),

                TextColumn::make('category.name')
                    ->label('Jenis Pekerjaan')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Pekerjaan Saluran' => 'blue',
                        'Pekerjaan Bangunan Sadap' => 'green',
                        'Pekerjaan Bangunan Pelengkap' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Item Pekerjaan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->name)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Harga Item')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('defaultUnit.name')
                    ->label('Satuan Volume')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
            ])
            ->filters([
                // contoh filter tambahan kalau perlu
            ])
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
