<?php

namespace App\Filament\Resources\Scan;

use App\Filament\Resources\Scan\Pages;
use App\Filament\Resources\Scan\Schemas\ScanForm;
use App\Filament\Resources\Scan\Tables\ScansTable;
use App\Models\Scan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ScanResource extends Resource
{
    protected static ?string $model = Scan::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scans';

    public static function form(Schema $schema): Schema
    {
        return ScanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ChooseEvent::route('/'),
            'by-event' => Pages\ListScansByEvent::route('/event/{event}'),
            'list' => Pages\ListScans::route('/list'),
        ];
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }
}
