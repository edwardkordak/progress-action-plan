<?php

namespace App\Filament\Resources\Data\Schemas;

use App\Models\Ppk;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Satker;
use App\Models\Package;
use App\Models\JobCategory;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class DataForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // === Dropdown bertingkat Satker → PPK → Paket ===
            Select::make('satker_id')
                ->label('Satuan Kerja')
                ->options(fn () => Satker::pluck('name', 'id'))
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('ppk_id', null)),

            Select::make('ppk_id')
                ->label('PPK')
                ->options(function (callable $get) {
                    $satkerId = $get('satker_id');
                    if (!$satkerId) return [];
                    return Ppk::where('satker_id', $satkerId)->pluck('name', 'id');
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(fn (callable $set) => $set('package_id', null)),

            Select::make('package_id')
                ->label('Paket Pekerjaan')
                ->options(function (callable $get) {
                    $ppkId = $get('ppk_id');
                    $satkerId = $get('satker_id');
                    if (!$ppkId || !$satkerId) return [];
                    return Package::where('ppk_id', $ppkId)
                        ->where('satker_id', $satkerId)
                        ->pluck('nama_paket', 'id');
                })
                ->searchable()
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    $package = Package::find($state);
                    if ($package) {
                        $set('penyedia_jasa', $package->penyedia_jasa);
                        $set('lokasi', $package->lokasi);
                    } else {
                        $set('penyedia_jasa', null);
                        $set('lokasi', null);
                    }
                }),

            // === Otomatis isi dari package ===
            TextInput::make('penyedia_jasa')
                ->label('Penyedia Jasa')
                ->disabled()
                ->dehydrated()
                ->required(),

            TextInput::make('lokasi')
                ->label('Lokasi')
                ->disabled()
                ->dehydrated()
                ->required(),

            // === Info umum ===
            TextInput::make('nama')
                ->label('Nama')
                ->required()
                ->maxLength(255),

            TextInput::make('jabatan')
                ->label('Jabatan')
                ->required()
                ->maxLength(255),

            DatePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->displayFormat('d M Y'),


        ]);
    }
}
