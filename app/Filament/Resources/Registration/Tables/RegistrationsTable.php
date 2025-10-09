<?php

namespace App\Filament\Resources\Registration\Tables;

use App\Models\Registration;
use App\Services\RegistrationApprovalService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['fieldValues.field', 'event']))
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
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkApprove')
                        ->label('Approve Terpilih')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve pendaftaran terpilih?')
                        ->modalDescription('Semua pendaftaran terpilih akan di-approve. Yang sudah approved akan otomatis di-skip. Pesan WhatsApp dikirim via background job.')
                        ->deselectRecordsAfterCompletion()
                        ->action(function (Collection $records) {
                            /** @var RegistrationApprovalService $svc */
                            $svc = app(RegistrationApprovalService::class);

                            $records->load(['event', 'fieldValues.field']);

                            $total      = $records->count();
                            $approved   = 0;
                            $skipped    = 0;
                            $failed     = 0;
                            $fails      = [];

                            foreach ($records as $registration) {
                                try {
                                    if ($registration->status === Registration::ST_APPROVED && $registration->code) {
                                        $skipped++;
                                        continue;
                                    }

                                    $svc->approve($registration);
                                    $approved++;
                                } catch (\Throwable $e) {
                                    $failed++;
                                    $fails[] = "#{$registration->id}: " . $e->getMessage();
                                }
                            }

                            $summary = "Total: {$total}\n"
                                     . "Approved: {$approved}\n"
                                     . "Skipped (sudah approved): {$skipped}\n"
                                     . "Gagal: {$failed}";

                            Notification::make()
                                ->title('Bulk Approve selesai')
                                ->body($summary . (count($fails) ? "\n\nGagal:\n- " . implode("\n- ", array_slice($fails, 0, 5)) . (count($fails) > 5 ? "\n…" : "") : ""))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
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
                        ->visible(fn ($record) => $record->event_id && $record->status === 'approved')
                        ->modalHeading(fn ($record) => $record->seatAssignment ? 'Ubah Kursi ' . $record->seatAssignment->seat->label : 'Pilih Kursi')
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
                    Action::make('resendWa')
                        ->label('Kirim Ulang WA')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim ulang WhatsApp?')
                        ->modalDescription('Pesan konfirmasi tiket akan dikirim ulang ke nomor WhatsApp peserta.')
                        ->visible(fn ($record) => $record->status === 'approved')
                        ->action(function ($record) {
                            app(RegistrationApprovalService::class)->resendWa($record);
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
}

