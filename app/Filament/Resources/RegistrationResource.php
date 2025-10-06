<?php

namespace App\Filament\Resources;
use App\Filament\Resources\RegistrationResource\Pages;
use App\Models\FormField;
use App\Models\FormFieldValue;
use App\Models\Registration;
use App\Services\RegistrationApprovalService;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn, ImageColumn, ViewColumn};
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

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
        ->modifyQueryUsing(
            fn ($query) => $query->with(['fieldValues.field', 'event'])
        )
        ->filters([
            SelectFilter::make('event_id')
                ->label('Event')
                ->relationship('event', 'title')
                ->preload()
                ->searchable(),
        ])
        ->columns([
            ...self::baseColumns(),
            ViewColumn::make('form_answers')
            ->label('Form Answers')
            ->view('filament.tables.columns.registration-form-fields')
            ->toggleable()
            ->searchable(query: function (Builder $query, string $search) {
                $query->whereHas('fieldValues', function ($q) use ($search) {
                    $q->where('value', 'like', "%{$search}%");
                });
            })
            ->grow(),
        ])
        ->persistFiltersInSession()
        ->persistSearchInSession()
        ->recordUrl(null)
        ->recordActions([
            ActionGroup::make([
                Action::make('approve')
                    ->label('Approve & QR')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Registration $r) => $r->status !== Registration::ST_APPROVED)
                    ->action(function (Registration $record, RegistrationApprovalService $svc) {
                        $svc->approve($record);
                    }),
                Action::make('reject')
                    ->color('warning')
                    ->icon('heroicon-m-x-circle')
                    ->requiresConfirmation()
                    ->visible(fn (Registration $r) => $r->status !== Registration::ST_APPROVED)
                    ->action(fn (Registration $r) => $r->update([
                        'status' => Registration::ST_REJECTED,
                        'checked_in_at' => null,
                    ])),
                Action::make('delete')
                    ->color('danger')
                    ->icon('heroicon-m-trash')
                    ->requiresConfirmation()
                    ->visible(fn (Registration $r) => $r->status !== Registration::ST_APPROVED)
                    ->action(fn (Registration $r) => $r->delete()),
                
                Action::make('choose_seat')
                    ->label(fn ($record) => $record->seatAssignment ? 'Ubah Kursi' : 'Pilih Kursi')
                    ->icon('heroicon-o-viewfinder-circle')
                    ->visible(fn ($record) => $record->event_id && $record->checked_in_at)
                    ->modalHeading(fn ($record) => $record->seatAssignment ? 'Ubah Kursi '.$record->seatAssignment->seat->label : 'Pilih Kursi')
                    ->modalContent(fn ($record) => view('filament.modals.choose-seat', [
                        'eventId' => $record->event_id,
                        'registrationId' => $record->id,
                        'currentSeatId'   => optional($record->seatAssignment)->seat_id,
                    ]))
                    ->modalSubmitAction(false),

                Action::make('release_seat')
                    ->label('Lepas Kursi')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->seatAssignment)
                    ->action(function ($record) {
                        $record->seatAssignment?->delete();
                        Notification::make()
                            ->title('Kursi dilepaskan')
                            ->success()
                            ->send();
                    }),
            ]),
        ]);
    }

    public static function baseColumns(): array
    {
        return [
            TextColumn::make('event.title')
                ->label('Event')
                ->sortable()
                ->searchable(),

            TextColumn::make('status')
                ->badge()
                ->color(fn (string $state) => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                })
                ->searchable(),

            TextColumn::make('code')->copyable()->searchable(),

            ImageColumn::make('qr_code')
                ->label('QR Code')
                ->state(fn (Registration $r) => $r->code ? route('ticket.qr', ['code' => $r->code]) : null)
                ->url(fn (Registration $r) => $r->code ? route('ticket.show', ['code' => $r->code]) : null, shouldOpenInNewTab: true)
                ->square(),

            TextColumn::make('checked_in_at')
                ->label('Check-in')
                ->state(fn (Registration $r) => filled($r->checked_in_at))
                ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-minus-circle')
                ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                ->formatStateUsing(fn () => ''),

            TextColumn::make('seat.assignment.seat.label')
                ->label('Kursi')
                ->getStateUsing(fn ($record) => optional($record->seatAssignment?->seat)->label ?? '—'),

            TextColumn::make('created_at')->dateTime()->sortable(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistrations::route('/'),
            'view'  => Pages\ViewRegistration::route('/{record}'),
        ];
    }
}