<?php

namespace App\Filament\Widgets;

use App\Models\Scan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentScans extends BaseWidget
{
    protected static ?string $heading = 'Recent Scans';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $active = session('active_event_id');

        return $table
            ->query(
                Scan::query()
                    ->when($active, fn ($q, $id) => $q->whereHas('registration', fn ($r) => $r->where('event_id', $id)))
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('code')->label('Code')->copyable(),
                TextColumn::make('registration.event.title')->label('Event')->toggleable(),
                TextColumn::make('location')->label('Location')->limit(24)->toggleable(),
                TextColumn::make('created_at')->label('When')->dateTime()->sortable(),
            ]);
    }
}

