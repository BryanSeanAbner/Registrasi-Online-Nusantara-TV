<?php

namespace App\Filament\Resources\Event\Schemas;

use App\Models\FormField;
use App\Models\Event;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

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
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                    $base = Str::slug($state ?? '');
                                    if ($base === '') {
                                        $set('slug', '');
                                        return;
                                    }

                                    $id = $get('id');
                                    $slug = $base;
                                    $i = 2;
                                    while (
                                        Event::query()
                                            ->when($id, fn ($q) => $q->where('id', '!=', $id))
                                            ->where('slug', $slug)
                                            ->exists()
                                    ) {
                                        $slug = $base . '-' . $i;
                                        $i++;
                                    }

                                    $set('slug', $slug);
                                }),
    
                            TextInput::make('slug')
                                ->label('Slug')
                                ->required()
                                ->disabled()
                                ->dehydrated(true)
                                ->unique(ignoreRecord: true)
                                ->default(function (Get $get) {
                                    $title = $get('title');
                                    $id = $get('id');
                                    $base = Str::slug($title ?? '');
                                    if ($base === '') {
                                        return null;
                                    }

                                    $slug = $base;
                                    $i = 2;
                                    while (
                                        Event::query()
                                            ->when($id, fn ($q) => $q->where('id', '!=', $id))
                                            ->where('slug', $slug)
                                            ->exists()
                                    ) {
                                        $slug = $base . '-' . $i;
                                        $i++;
                                    }

                                    return $slug;
                                }),
    
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
                        Toggle::make('brand.wa_blast_enabled')
                            ->label('Enable WA Blast')
                            ->helperText('Jika dimatikan, fitur Blast WA untuk event ini dinonaktifkan. Hanya Super Admin yang dapat mengubah pengaturan ini.')
                            ->default(true)
                            ->visible(fn () => Auth::check() && (Auth::user()?->role === 'super_admin')),
                        Placeholder::make('wa_template_help')
                            ->hiddenLabel()
                            ->content('Gunakan template pesan WhatsApp untuk setiap event. Anda dapat memakai placeholder: {name}, {event}, {code}, {location}, {qr_url}. Biarkan kosong untuk memakai template bawaan.'),

                        Textarea::make('brand.wa_template')
                            ->rows(8)
                            ->helperText('Contoh penggunaan: "Selamat, {name}! Acara: {event}. Kode: {code}." Tekan Enter untuk baris baru. Placeholder akan otomatis diganti sesuai data peserta.')
                            ->placeholder("Selamat, {name}!\n\nPendaftaran kamu telah DISETUJUI.\n\nAcara: {event}\nLokasi: {location}\nKode Tiket: {code}\n\nSimpan kode ini dan tunjukkan QR Code saat check-in di lokasi.\nSampai jumpa di acara!"),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                // Section::make('Email Message Template')
                //     ->schema([
                //         Placeholder::make('email_template_help')
                //             ->hiddenLabel()
                //             ->content('Opsional. Template email untuk peserta. Placeholder yang tersedia sama: {name}, {event}, {code}, {location}, {qr_url}. Jika dikosongkan, sistem dapat memakai template default (bila fitur email diaktifkan).'),

                //         Textarea::make('brand.email_template')
                //             ->rows(8)
                //             ->helperText('Contoh: "Halo {name}, pendaftaran untuk {event} berhasil. Kode tiket: {code}."')
                //             ->placeholder("Halo {name},\n\nPendaftaran kamu untuk acara {event} telah disetujui.\nLokasi: {location}\nKode Tiket: {code}\n\nSampai jumpa!"),
                //     ])
                //     ->columns(1)
                //     ->columnSpanFull(),

                Section::make('Form Roles')
                    ->schema([
                        Placeholder::make('roles_help')
                            ->hiddenLabel()
                            ->content('Pemetaan kolom penting untuk event ini. Pilih field mana yang berisi Nomor WhatsApp, Nama Lengkap, dan Email. Mapping ini dipakai untuk: pengiriman WA, personalisasi {name}, dan (opsional) pengiriman email. Jika daftar kosong, buat field-nya di menu Form Fields.'),

                        Grid::make(3)->schema([
                            \Filament\Forms\Components\Select::make('brand.roles.wa_phone_field_id')
                                ->label('Nomor WhatsApp')
                                ->options(function (Get $get) {
                                    $eventId = $get('id');
                                    if (! $eventId) return [];
                                    return FormField::where('event_id', $eventId)
                                        ->orderBy('sort_order')
                                        ->pluck('label', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->helperText('Digunakan sebagai tujuan pengiriman WA.'),

                            \Filament\Forms\Components\Select::make('brand.roles.full_name_field_id')
                                ->label('Nama Lengkap')
                                ->options(function (Get $get) {
                                    $eventId = $get('id');
                                    if (! $eventId) return [];
                                    return FormField::where('event_id', $eventId)
                                        ->orderBy('sort_order')
                                        ->pluck('label', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->helperText('Dipakai untuk menyapa peserta di pesan.'),

                            \Filament\Forms\Components\Select::make('brand.roles.email_field_id')
                                ->label('Email')
                                ->options(function (Get $get) {
                                    $eventId = $get('id');
                                    if (! $eventId) return [];
                                    return FormField::where('event_id', $eventId)
                                        ->orderBy('sort_order')
                                        ->pluck('label', 'id');
                                })
                                ->searchable()
                                ->preload()
                                ->helperText('Opsional. Dipakai sebagai tujuan pengiriman email jika fitur email diaktifkan.'),
                        ])->columnSpanFull(),
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
