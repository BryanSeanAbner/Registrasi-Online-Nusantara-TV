<?php

namespace App\Filament\Resources\Seats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'title')
                    ->required()
                    ->label('Event'),
                TextInput::make('section'),
                TextInput::make('row'),
                TextInput::make('col')
                    ->numeric(),
                TextInput::make('label')
                    ->required(),
                TextInput::make('type')
                    ->required()
                    ->default('regular'),
                Select::make('status')
                    ->options(['available' => 'Available', 'blocked' => 'Blocked', 'maintenance' => 'Maintenance'])
                    ->default('available')
                    ->required(),
            ]);
    }
}
