<?php

namespace App\Filament\Resources\Event\Tables;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('venue'),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('ends_at')->dateTime()->sortable(),
                IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->recordUrl(null)
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->url(fn (Event $e) => route('event.show', $e->slug))->openUrlInNewTab(),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->label('Edit')
                    ->url(fn (Event $e) => static::getResourceUrl('edit', $e)),
            ]);
    }

    protected static function getResourceUrl(string $name, Event $event): string
    {
        /** @var class-string<\App\Filament\Resources\Event\EventResource> $res */
        $res = \App\Filament\Resources\Event\EventResource::class;
        return $res::getUrl($name, ['record' => $event]);
    }
}

