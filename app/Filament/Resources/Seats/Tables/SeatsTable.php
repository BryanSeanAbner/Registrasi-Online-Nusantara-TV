<?php

namespace App\Filament\Resources\Seats\Tables;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class SeatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $active = session('active_event_id');
                if ($active) {
                    $query->where('event_id', $active);
                }
                $query->with('assignment');
            })
            ->columns([
                TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable(),
                TextColumn::make('section')
                    ->searchable(),
                TextColumn::make('row')
                    ->searchable(),
                TextColumn::make('col')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn ($record) => $record->assignment ? 'taken' : ($record->status ?? 'available'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'taken' => 'warning',
                        'available' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
            SelectFilter::make('event_id')
                ->label('Event')
                ->relationship('event', 'title')
                ->preload()
                ->default(fn () => session('active_event_id'))
                ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn ($record) => (bool) $record->assignment)
                    ->tooltip(fn ($record) => $record->assignment ? 'Tidak bisa menghapus kursi yang sudah diambil.' : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete_selected')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            $deleted = 0;
                            $blocked = 0;

                            $records->loadMissing('assignment');

                            foreach ($records as $seat) {
                                if ($seat->assignment) {
                                    $blocked++;
                                    continue;
                                }

                                $seat->delete();
                                $deleted++;
                            }

                            Notification::make()
                                ->title('Bulk delete selesai')
                                ->body("Dihapus: {$deleted}\nTerhalang (taken): {$blocked}")
                                ->{($blocked > 0) ? 'warning' : 'success'}()
                                ->send();
                        }),
                ]),
            ]);
    }
}
