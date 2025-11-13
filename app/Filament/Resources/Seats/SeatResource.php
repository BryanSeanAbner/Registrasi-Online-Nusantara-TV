<?php

namespace App\Filament\Resources\Seats;

use App\Filament\Resources\Seats\Pages\CreateSeat;
use App\Filament\Resources\Seats\Pages\EditSeat;
use App\Filament\Resources\Seats\Pages\ListSeats;
use App\Filament\Resources\Seats\Schemas\SeatForm;
use App\Filament\Resources\Seats\Tables\SeatsTable;
use App\Models\Seat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SeatResource extends Resource
{
    protected static ?string $model = Seat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return SeatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeatsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeats::route('/'),
            'create' => CreateSeat::route('/create'),
            'edit' => EditSeat::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }
}
