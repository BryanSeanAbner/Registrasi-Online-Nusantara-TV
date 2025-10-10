<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentRegistrations extends BaseWidget
{
    protected static ?string $heading = 'Recent Registrations';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $active = session('active_event_id');

        return $table
            ->query(
                Registration::query()
                    ->when($active, fn ($q, $id) => $q->where('event_id', $id))
                    ->latest()
                    ->limit(8)
            )
            ->columns([
                TextColumn::make('event.title')->label('Event')->toggleable(),
                TextColumn::make('code')->label('Code')->copyable(),
                TextColumn::make('status')->badge()->color(fn ($state) => match ($state) {
                    'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', default => 'gray',
                }),
                TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
            ]);
    }
}

