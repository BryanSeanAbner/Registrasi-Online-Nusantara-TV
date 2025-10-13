<?php

namespace App\Filament\Resources\Event\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(1)->schema([  // satu kolom penuh
                Section::make('Event Info')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('title')
                                ->label('Title')
                                ->required(),
    
                            TextInput::make('slug')
                                ->label('Slug')
                                ->required(),
    
                            TextInput::make('venue')
                                ->label('Venue')
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            DateTimePicker::make('starts_at')
                                ->label('Starts At')
                                ->required(),

                            DateTimePicker::make('ends_at')
                                ->label('Ends At')
                                ->required(),
                        ])->columnSpanFull(),

                        Toggle::make('is_published')
                            ->label('Is published'),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('WA Message Template')
                    ->schema([
                        Textarea::make('brand.wa_template')
                            ->rows(8)
                            ->helperText('Placeholder: {name}, {event}, {code}, {location}, {qr_url}. Kosongkan untuk template default.')
                            ->placeholder("Selamat, {name}!\n\nPendaftaran kamu telah DISETUJUI.\n\nAcara: {event}\nKode Tiket: {code}\n\nSimpan kode ini dan tunjukkan QR Code saat check-in di lokasi.\nSampai jumpa di acara!"),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make('Brand')
                    ->schema([
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
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ])->columnSpanFull(),
        ]);
    }
}
