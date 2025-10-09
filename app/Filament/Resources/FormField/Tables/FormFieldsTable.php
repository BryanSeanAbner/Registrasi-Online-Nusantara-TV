<?php

namespace App\Filament\Resources\FormField\Tables;

use App\Models\FormField;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FormFieldsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('event.title')->label('Event'),
            TextColumn::make('label')->searchable(),
            TextColumn::make('name'),
            TextColumn::make('type'),
            IconColumn::make('is_required')->boolean(),
            TextColumn::make('sort_order')->sortable(),
            TextColumn::make('updated_at')->dateTime(),
        ])
        ->filters([
            SelectFilter::make('event_id')
                ->label('Event')
                ->relationship('event', 'title')
                ->preload()
                ->searchable(),
        ])
        ->recordActions([
            Action::make('edit')
                ->icon('heroicon-m-pencil-square')
                ->label('Edit')
                ->url(fn (FormField $e) => static::getResourceUrl('edit', $e)),
            Action::make('delete')
                ->label('Delete')
                ->color('danger')
                ->requiresConfirmation()
                ->action(fn (FormField $r) => $r->delete()),
        ])
        ->defaultSort('sort_order');
    }

    protected static function getResourceUrl(string $name, FormField $field): string
    {
        /** @var class-string<\App\Filament\Resources\FormField\FormFieldResource> $res */
        $res = \App\Filament\Resources\FormField\FormFieldResource::class;
        return $res::getUrl($name, ['record' => $field]);
    }
}

