<?php

namespace App\Filament\Resources\Event\Tables;

use App\Services\ReminderBlastService;
use App\Models\Event;
use App\Models\Registration;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('title')->searchable(),
                TextColumn::make('slug'),
                TextColumn::make('venue'),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('ends_at')->dateTime()->sortable(),
                IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->recordUrl(null)
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->label('View')
                        ->url(fn (Event $e) => route('event.show', $e->slug))->openUrlInNewTab(),
                    Action::make('edit')
                        ->icon('heroicon-m-pencil-square')
                        ->label('Edit')
                        ->color('warning')
                        ->url(fn (Event $e) => static::getResourceUrl('edit', $e)),
                    Action::make('toggle_publish')
                        ->icon(fn (Event $e) => $e->is_published ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->label(fn (Event $e) => $e->is_published ? 'Unpublish' : 'Publish')
                        ->action(function (Event $event) {
                            $event->is_published = ! $event->is_published;
                            $event->save();
                        })
                        ->color(function (Event $e) {
                            return $e->is_published ? 'danger' : 'success';
                        }),
                    Action::make('list_participants')
                        ->icon('heroicon-o-users')
                        ->label('Participants')
                        ->url(fn (Event $e) => route('event.participants', $e->slug))
                        ->openUrlInNewTab(),
                    Action::make('blast_wa_reminder')
                        ->icon('heroicon-o-paper-airplane')
                        ->label('Blast WA Reminder')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Reminder WA ke peserta approved?')
                        ->modalDescription('Pesan akan dikirim ke semua pendaftar yang statusnya Approved pada event ini.')
                        ->form([
                            Textarea::make('message')
                                ->label('Pesan')
                                ->required()
                                ->rows(6)
                                ->placeholder("Contoh: Halo {name}, ini pengingat acara {event} di {location} pada {date}. Mohon hadir 15 menit lebih awal. Terima kasih."),
                        ])
                        ->action(function (Event $event, array $data) {
                            /** @var ReminderBlastService $svc */
                            $svc = app(ReminderBlastService::class);
                            $result = $svc->blast($event, (string) $data['message'], (bool) (false));
    
                            $notif = \Filament\Notifications\Notification::make()
                                ->title('Blast WA dijadwalkan')
                                ->body("Total: {$result['total']}\nDikirim: {$result['dispatched']}")
                                ->success()
                                ->persistent();
    
                            $notif->send();
                            try { $notif->sendToDatabase(Auth::user()); } catch (\Throwable) {}
                        }),
                ])
            ]);
    }

    protected static function getResourceUrl(string $name, Event $event): string
    {
        /** @var class-string<\App\Filament\Resources\Event\EventResource> $res */
        $res = \App\Filament\Resources\Event\EventResource::class;
        return $res::getUrl($name, ['record' => $event]);
    }
}
