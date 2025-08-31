<?php

namespace App\Filament\Resources\Data\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataTable
{
    
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('satker.name')
                    ->label('Satuan Kerja')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ppk.name')
                    ->label('PPK')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('package.nama_paket')
                    ->label('Paket Pekerjaan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('jabatan')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('lokasi')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tanggal')
                    ->date()
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
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
