<?php

namespace App\Filament\Resources\Packages\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class PackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('satker_id')
                    ->label('Satuan Kerja')
                    ->relationship('satker', 'name')
                    ->required()
                    ->reactive(), // penting supaya trigger perubahan

                Select::make('ppk_id')
                    ->label('PPK')
                    ->required()
                    ->options(function (callable $get) {
                        $satkerId = $get('satker_id');

                        if (!$satkerId) {
                            return [];
                        }

                        return \App\Models\Ppk::query()
                            ->where('satker_id', $satkerId)
                            ->pluck('name', 'id');
                    })
                    ->reactive()
                    ->disabled(fn (callable $get) => blank($get('satker_id'))),

                TextInput::make('nama_paket')
                    ->label('Nama Paket')
                    ->required(),

                TextInput::make('penyedia_jasa')
                    ->label('Penyedia Jasa')
                    ->required(),

                TextInput::make('lokasi')
                    ->default(null),
            ]);
    }
}
