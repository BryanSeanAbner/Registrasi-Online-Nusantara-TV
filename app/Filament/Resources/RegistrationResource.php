<?php

namespace App\Filament\Resources;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Actions\Action;  


class RegistrationResource extends Resource {
    protected static ?string $model = Registration::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('event.title')->label('Event')->sortable()->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('qr_code')->label('Code')->copyable(),
                IconColumn::make('checked_in_at')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->label('Checked'),
            ])
            ->recordActions([
                Action::make('toggleCheckin')
                    ->label('Check-in/Undo')
                    ->action(fn (Registration $r) =>
                        $r->update(['checked_in_at' => $r->checked_in_at ? null : now()])
                    ),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Registration $r) => $r->delete()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
        ];
    }
}