<?php

namespace App\Filament\Resources\Event\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Flex::make([
                Section::make([
                    TextInput::make('title')->label('Title')->required(),
                    TextInput::make('slug')->label('Slug')->required(),
                    TextInput::make('venue')->label('Venue')->required(),
                ]),
                Section::make([
                    DateTimePicker::make('starts_at')
                        ->label('Starts At')
                        ->required(),
                    DateTimePicker::make('ends_at')
                        ->label('Ends At')
                        ->required(),
                    Toggle::make('is_published'),
                ]),
                Section::make([
                    FileUpload::make('brand.logo')
                        ->label('Logo')
                        ->image()
                        ->disk('public_event')
                        ->directory('events/brand')
                        ->preserveFilenames()
                        ->imagePreviewHeight('200')
                        ->downloadable(),
                    FileUpload::make('brand.background')
                        ->label('Background')
                        ->image()
                        ->disk('public_event')
                        ->directory('events/brand')
                        ->preserveFilenames()
                        ->imagePreviewHeight('200')
                        ->downloadable(),
                ])->columns(1),
            ])->columnSpanFull(),
        ]);
    }
}
