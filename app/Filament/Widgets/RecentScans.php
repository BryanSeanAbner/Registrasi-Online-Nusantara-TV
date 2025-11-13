<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentScans extends BaseWidget
{
    protected static ?string $heading = 'Scan Terbaru';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $active = session('active_event_id');

        return $table
            ->query(
                Scan::query()
                    ->with(['registration.event', 'scannedBy'])
                    ->when($active, fn ($q, $id) => $q->whereHas('registration', fn ($r) => $r->where('event_id', $id)))
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('code')->label('Kode')->copyable(),
                TextColumn::make('registration.event.title')->label('Event')->toggleable(),
                TextColumn::make('scannedBy.name')->label('Petugas')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('location')->label('Lokasi')->limit(24)->toggleable(),
                TextColumn::make('created_at')->label('Waktu')->dateTime()->sortable(),
            ]);
    }
}
