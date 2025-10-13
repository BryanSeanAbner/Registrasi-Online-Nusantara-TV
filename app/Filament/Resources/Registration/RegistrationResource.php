<?php

namespace App\Filament\Resources\Registration;

use App\Filament\Resources\Registration\Pages;
use App\Filament\Resources\Registration\Tables\RegistrationsTable;
use App\Models\Registration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'view'  => Pages\ViewRegistration::route('/{record}'),
        ];
    }
}

