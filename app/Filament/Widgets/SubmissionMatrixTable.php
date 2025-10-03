<?php

namespace App\Filament\Widgets;

use App\Models\DataSubmission;
use App\Models\DataSubmissionDetail;
use App\Models\JobCategory;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\FiltersLayout;

class SubmissionMatrixTable extends BaseWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Rincian Submission Harian';
    protected int|string|array $columnSpan = 'full';

    protected function scopedQuery(): Builder
    {
        $q = DataSubmissionDetail::query()
            ->with(['submission', 'satuan', 'item', 'jobCategory']);

        if ($pid = $this->filters['package_id'] ?? null) {
            $q->whereHas('submission', fn ($s) => $s->where('package_id', $pid));
        }
        if ($start = $this->filters['startDate'] ?? null) {
            $q->whereHas('submission', fn ($s) => $s->whereDate('tanggal', '>=', $start));
        }
        if ($end = $this->filters['endDate'] ?? null) {
            $q->whereHas('submission', fn ($s) => $s->whereDate('tanggal', '<=', $end));
        }

        return $q;
    }

    protected function getTableQuery(): Builder
    {
        return $this->scopedQuery()->orderBy(
            DataSubmission::select('tanggal')
                ->whereColumn('data_submissions.id', 'data_submission_details.data_submission_id')
                ->limit(1)
        );
    }

    protected function getTableFilters(): array
    {
        $itemOptions = [
            'Galian Tanah' => 'Galian Tanah',
            'Pekerjaan Plesteran' => 'Pekerjaan Plesteran',
            'Pekerjaan Siaran' => 'Pekerjaan Siaran',
            'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)' =>
            'Pengadaan dan Pemasangan Pasangan Batu Mortar Tipe N (1 PC: 4 PP)',
        ];

        return [
            SelectFilter::make('job_category_id')
                ->label('Kategori')
                ->options(fn () => \App\Models\JobCategory::query()
                    ->orderBy('sort_order')->orderBy('name')
                    ->pluck('name', 'id')->all())
                ->placeholder('All')
                ->indicator('Kategori')
                ->searchable()
                ->preload()
                ->native(false)
                ->query(function (Builder $query, array $data) {
                    $value = $data['value'] ?? null;
                    if (filled($value)) {
                        $query->where('data_submission_details.job_category_id', $value);
                    }
                }),

            SelectFilter::make('item_name')
                ->label('Item')
                ->options($itemOptions)
                ->placeholder('All')
                ->indicator('Item')
                ->searchable()
                ->preload()
                ->native(false)
                ->query(function (Builder $query, array $data) {
                    $name = $data['value'] ?? null;
                    if (filled($name)) {
                        $query->whereHas('item', fn ($iq) => $iq->where('name', $name));
                    }
                }),
        ];
    }

    protected function getTableFiltersLayout(): ?string
    {
        return 'above-content';
    }
    protected function getTableGroups(): array
    {
        return [
            Group::make('jobCategory.name')->label('Kategori')->collapsible(),
            Group::make('item.name')->label('Item')->collapsible(),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('submission.tanggal')
                ->label('Tanggal')
                ->date('d M Y')
                ->sortable(),

            TextColumn::make('jobCategory.name')
                ->label('Kategori')
                ->toggleable()
                ->sortable(),

            TextColumn::make('item.name')
                ->label('Item')
                ->searchable()
                ->toggleable()
                ->limit(40),

            TextColumn::make('volume')
                ->label('Volume')
                ->numeric(2)
                ->alignRight(),

            TextColumn::make('satuan.symbol')
                ->label('Satuan')
                ->placeholder(fn ($r) => optional($r->item?->defaultUnit)->symbol ?? '—'),

            TextColumn::make('keterangan')
                ->label('Keterangan')
                ->searchable()
                ->limit(80)
                ->wrap(),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return ($this->filters['package_id'] ?? null)
            ? 'Tidak ada data untuk filter ini.'
            : 'Pilih Paket terlebih dahulu.';
    }
}
