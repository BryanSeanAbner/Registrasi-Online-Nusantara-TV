<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScanResource\Pages;
use App\Models\Scan;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ScanResource extends Resource
{
    protected static ?string $model = Scan::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scans';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            //
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->copyable()
                    ->wrap(),
                TextColumn::make('registration.event.title')
                    ->label('Event')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Location')
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('scannedBy.name')
                    ->label('Scanner')
                    ->toggleable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Scanned At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordUrl(null)
            ->recordActions([
                //
            ]);
    }    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScans::route('/'),
        ];
    }    
}