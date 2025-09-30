<?php

namespace App\Filament\Resources;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\Registration;
use App\Services\RegistrationApprovalService;
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
            TextColumn::make('id')->sortable(),
            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                }),
            TextColumn::make('code')->copyable(),
            // TextColumn::make('checked_in_at')->dateTime()->toggleable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])
        ->recordUrl(null)
        ->recordActions([
            Action::make('approve')
                ->label('Approve & QR')
                ->icon('heroicon-m-check-badge')
                ->requiresConfirmation()
                ->visible(fn (Registration $r) => $r->status !== Registration::ST_APPROVED)
                ->action(function (Registration $record, RegistrationApprovalService $svc) {
                    $svc->approve($record);
                }),
            Action::make('reject')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Registration $r) => $r->status !== Registration::ST_REJECTED)
                ->action(fn (Registration $r) => $r->update([
                    'status' => Registration::ST_REJECTED,
                    'checked_in_at' => null,
                ])),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'view'  => Pages\ViewRegistration::route('/{record}'),
        ];
    }
}