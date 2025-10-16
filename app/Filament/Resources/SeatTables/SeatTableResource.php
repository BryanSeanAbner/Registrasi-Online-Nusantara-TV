<?php

namespace App\Filament\Resources\SeatTables;

use App\Filament\Resources\SeatTables\Pages\CreateSeatTable;
use App\Filament\Resources\SeatTables\Pages\EditSeatTable;
use App\Filament\Resources\SeatTables\Pages\ListSeatTables;
use App\Filament\Resources\SeatTables\Schemas\SeatTableForm;
use App\Filament\Resources\SeatTables\Tables\SeatTablesTable;
use App\Models\SeatTable;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeatTableResource extends Resource
{
    protected static ?string $model = SeatTable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return SeatTableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeatTablesTable::configure($table);
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
            'index' => ListSeatTables::route('/'),
            'create' => CreateSeatTable::route('/create'),
            'edit' => EditSeatTable::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }
}
