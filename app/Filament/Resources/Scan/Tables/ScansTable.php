<?php

namespace App\Filament\Resources\Scan\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Preload relations used by dynamic form field view
                $query->with(['registration.event', 'registration.fieldValues.field', 'scannedBy']);
            })
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->wrap(),
                TextColumn::make('registration.event.title')
                    ->label('Event')
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Location')
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('scannedBy.name')
                    ->label('Scanner')
                    ->toggleable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Scanned At')
                    ->dateTime()
                    ->sortable(),
                ViewColumn::make('form_answers')
                    ->label('Form Answers')
                    ->view('filament.tables.columns.scan-form-fields')
                    ->toggleable()
                    ->grow(),
            ])
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('registration.event', 'title')
                    ->preload()
                    ->searchable(),
            ])
            ->recordUrl(null)
            ->recordActions([
                //
            ]);
    }
}
