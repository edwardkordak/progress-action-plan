<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\DataSubmission;
use App\Models\DataTargetDetail;
use App\Models\DataSubmissionDetail;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Panel;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SubmissionMatrixTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected static ?string $heading = 'Rekap Progress';
    protected int|string|array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $packageId = $this->filters['package_id'] ?? null;
        $start = $this->filters['startDate'] ?? null;
        $end = $this->filters['endDate'] ?? null;

        return DataSubmission::query()
            ->with(['details.item', 'details.jobCategory'])
            ->when($packageId, fn ($q) => $q->where('package_id', $packageId))
            ->when($start, fn ($q) => $q->whereDate('tanggal', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('tanggal', '<=', $end))
            ->orderByDesc('tanggal');
    }

    protected function getTableColumns(): array
    {
        return [

            // === HEADER INFO ====================================================
            TextColumn::make('header_info')
                ->label('Informasi Submission')
                ->html()
                ->state(function ($record) {
                    $tanggal = Carbon::parse($record->tanggal)->translatedFormat('d F Y');
                    $jam = Carbon::parse($record->created_at)->translatedFormat('H:i');
                    $package = e($record->package->nama_paket ?? '-');
                    $packageId = $record->package_id;
                    $startDate = $record->tanggal;

                    // === 1️⃣ Hitung bobot harian ===
                    $packagePrice = $record->package->price ?? 0;
                    $bobotHarian = 0;

                    if ($packagePrice > 0) {
                        foreach ($record->details as $detail) {
                            $price = $detail->item->price ?? 0;
                            $volume = $detail->volume ?? 0;
                            $bobotHarian += ($volume * $price / $packagePrice) * 100;
                        }
                    }

                    // === 2️⃣ Hitung baseline (tanggal sebelum startDate) ===
                    $baselineSubmission = \App\Models\DataSubmission::with(['details.item', 'package'])
                        ->where('package_id', $packageId)
                        ->where('tanggal', '<', $startDate)
                        ->get()
                        ->sum(function ($sub) {
                            $packagePrice = $sub->package->price ?? 0;
                            if ($packagePrice == 0) return 0;

                            $bobot = 0;
                            foreach ($sub->details as $detail) {
                                $price = $detail->item->price ?? 0;
                                $volume = $detail->volume ?? 0;
                                $bobot += ($volume * $price / $packagePrice) * 100;
                            }
                            return $bobot;
                        });

                    // === 3️⃣ Total kumulatif (baseline + harian sampai hari ini) ===
                    $bobotKumulatif = round($baselineSubmission + $bobotHarian, 2);
                    $bobotHarian = round($bobotHarian, 2);

                    // Warna tampilan
                    $colorHarian = $bobotHarian > 0
                        ? 'var(--filament-color-success-600)'
                        : 'var(--filament-color-gray-500)';
                    $colorKumulatif = $bobotKumulatif > 0
                        ? 'var(--filament-color-primary-600)'
                        : 'var(--filament-color-gray-500)';

                    // === 4️⃣ HTML tampilan header ===
                    return "
                    <div style='
                         display: grid;
                         grid-template-columns: 1.3fr 1fr 0.7fr 0.6fr 0.6fr;
                         align-items: center;
                         border: 1px solid var(--filament-color-gray-300);
                         border-radius: 6px;
                         background-color: var(--filament-color-gray-50);
                         padding: 8px 12px;
                         font-size: 13px;
                         color: var(--filament-color-gray-900);
                    '>
                    <div style='color:var(--filament-color-gray-600);'>{$package}</div>
                    <div style='font-weight:600; color:var(--filament-color-warning-600);'>📅 {$tanggal}</div>
                    <div style='font-weight:500;'>⏰ {$jam}</div>
  
                    <div style='font-weight:700; color:{$colorKumulatif};'>
                📈 {$bobotKumulatif}%
                    </div>
                </div>";
                })
                ->sortable(false)
                ->alignLeft(),



            // === PANEL DETAIL ===================================================
            Panel::make([
                TextColumn::make('details_html')
                    ->label('')
                    ->html()
                    ->state(function (DataSubmission $record) {
                        $details = $record->details()->with(['jobCategory', 'item'])->get();

                        if ($details->isEmpty()) {
                            return '<p style="color:var(--filament-color-gray-500); font-size:13px;">Tidak ada rincian input untuk tanggal ini.</p>';
                        }

                        $grouped = $details->groupBy(fn ($d) => optional($d->jobCategory)->name ?? 'Tanpa Kategori');

                        $html = '<div style="margin-top: 8px;">';
                        foreach ($grouped as $catName => $rows) {
                            $html .= "
                            <div style='
                                border: 1px solid var(--filament-color-gray-300);
                                border-radius: 6px;
                                background-color: var(--filament-color-gray-50);
                                padding: 10px;
                                margin-bottom: 16px;
                            '>
                                <div style='
                                    font-weight: 600;
                                    font-size: 14px;
                                    color: var(--filament-color-primary-600);
                                    margin-bottom: 6px;
                                    border-bottom: 1px solid var(--filament-color-gray-200);
                                    padding-bottom: 4px;
                                '>{$catName}</div>

                                <div style='overflow-x:auto;'>
                                <table style='width:100%; border-collapse: collapse; font-size:13px; color:var(--filament-color-gray-900);'>
                                    <thead style='background:var(--filament-color-gray-100);'>
                                        <tr>
                                            <th style='text-align:left; padding:6px 10px; width:30%;'>Item</th>
                                            <th style='text-align:left; padding:6px 10px; width:10%;'>Satuan</th>
                                            <th style='text-align:left; padding:6px 10px; width:10%;'>Target Harian</th>
                                            <th style='text-align:right; padding:6px 10px; width:10%;'>Realisasi Harian</th>
                                            <th style='text-align:right; padding:6px 10px; width:10%;'>Deviasi Harian</th>
                                            <th style='text-align:right; padding:6px 10px; width:10%;'>Progres Kumulatif</th>
                                            <th style='text-align:right; padding:6px 10px; width:10%;'>Target Volume</th>
                                            <th style='text-align:right; padding:6px 10px; width:10%;'>Deviasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                            foreach ($rows as $r) {
                                $item = $r->item;
                                $itemTarget = $item->volume ?? 0;
                                $harian = $r->volume ?? 0;
                                $targetHarian = optional(
                                    DataTargetDetail::where('item_id', $r->item_id)
                                        ->whereHas('target', function ($q) use ($record) {
                                            $q->where('package_id', $record->package_id)
                                                ->whereDate('tanggal', '<=', $record->tanggal);
                                        })
                                        ->latest('data_target_id')
                                        ->first()
                                )->volume ?? 0;


                                $kumulatif = DataSubmissionDetail::query()
                                    ->whereHas('submission', function ($q) use ($record, $r) {
                                        $q->where('package_id', $record->package_id)
                                            ->whereDate('tanggal', '<=', $record->tanggal);
                                    })
                                    ->where('item_id', $r->item_id)
                                    ->sum('volume');

                                $sisa = $itemTarget - $kumulatif;
                                $color = $sisa < 0
                                    ? 'var(--filament-color-danger-600)'
                                    : 'var(--filament-color-success-600)';

                                $html .= "
                                <tr style='border-bottom:1px solid var(--filament-color-gray-200);'>
                                    <td style='padding:4px 10px;'>" . e($item->name) . "</td>
                                    <td style='padding:4px 10px;'>" . e($item->defaultUnit->symbol) . "</td>
                                    <td style='padding:4px 10px;'>" . number_format($targetHarian, 2) . "</td>
                                    <td style='padding:4px 10px; text-align:right; font-family:monospace;'>" . number_format($harian, 2) . "</td>
                                    <td style='padding:4px 10px; text-align:right; font-family:monospace;'>" .number_format($targetHarian - $harian, 2) . "</td>

                                    <td style='padding:4px 10px; text-align:right; font-family:monospace;'>" . number_format($kumulatif, 2) . "</td>
                                    <td style='padding:4px 10px; text-align:right; font-family:monospace;'>" . number_format($itemTarget, 2) . "</td>
                                    <td style='padding:4px 10px; text-align:right; font-family:monospace; color:{$color}; font-weight:600;'>" . "-" .number_format($sisa, 2) . "</td>
                                </tr>";
                            }

                            $html .= "</tbody></table></div></div>";
                        }

                        $html .= '</div>';
                        return $html;
                    }),
            ])
                ->collapsible()
                ->collapsed(),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return ($this->filters['package_id'] ?? null)
            ? 'Tidak ada data submission untuk filter ini.'
            : 'Pilih Paket terlebih dahulu.';
    }
}
