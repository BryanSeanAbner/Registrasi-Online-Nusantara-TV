<?php

namespace App\Filament\Resources\Registration;

use App\Filament\Resources\Registration\Pages;
use App\Filament\Resources\Registration\Tables\RegistrationsTable;
use App\Models\Registration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Components\Text as SchemaText;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Tables\Table;

class RegistrationResource extends Resource {
    protected static ?string $model = Registration::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // No form for listing-only resource
        ]);
    }

    public static function table(Table $table): Table
    {
        return RegistrationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make('Detail Pendaftaran')
                ->columns(2)
                ->schema([
                    SchemaText::make(fn ($record) => 'Event: ' . ((string) (optional($record->event)->title ?: '-'))),
                    SchemaText::make(fn ($record) => 'Status: ' . ucfirst((string) ($record->status ?? '-')))->badge(),

                    SchemaText::make(fn ($record) => 'Kode: ' . ((string) ($record->code ?? '-')))->copyable(),
                    SchemaText::make(fn ($record) => 'Kursi: ' . ((string) (optional($record->seatAssignment?->seat)->label ?: '-'))),

                    SchemaText::make(fn ($record) => 'Approved By: ' . ((string) (optional($record->approver)->name ?: '-'))),
                    SchemaText::make(fn ($record) => 'Dibuat: ' . (optional($record->created_at)->format('d M Y H:i') ?: '-')),

                    SchemaText::make(fn ($record) => 'Check-in: ' . ($record->checked_in_at ? $record->checked_in_at->format('d M Y H:i') : 'Belum')),

                    // QR ticket preview (full width)
                    SchemaView::make('filament.infolists.registration-ticket')->columnSpan(2),

                    // Form answers (full width)
                    SchemaView::make('filament.tables.columns.registration-form-fields')->columnSpan(2),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'create' => Pages\CreateManualRegistration::route('/create'),
            'view'  => Pages\ViewRegistration::route('/{record}'),
        ];
    }
}

