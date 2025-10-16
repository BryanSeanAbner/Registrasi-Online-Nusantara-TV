<?php

namespace App\Filament\Resources\SeatTables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SeatTableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')->relationship('event','title')->required()->label('Event'),
                TextInput::make('label')->required()->maxLength(100),
                TextInput::make('capacity')->numeric()->minValue(0),
                Textarea::make('notes')->rows(3)->columnSpanFull(),
            ]);
    }
}

