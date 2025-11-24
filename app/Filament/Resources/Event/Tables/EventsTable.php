<?php

namespace App\Filament\Resources\Event\Tables;

use App\Filament\Resources\Event\EventResource;
use App\Services\ReminderBlastService;
use App\Models\Event;
use App\Models\Registration;
use App\Services\ShortLinkService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
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
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->tooltip(fn ($record) => (string) ($record->title ?? '')),

                TextColumn::make('short_link')
                    ->limit(30)
                    ->copyable()
                    ->tooltip(fn ($record) => (string) ($record->short_link ?? '')),

                TextColumn::make('venue')
                    ->limit(40)
                    ->wrap()
                    ->tooltip(fn ($record) => (string) ($record->venue ?? '')),

                TextColumn::make('starts_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                IconColumn::make('is_published')->boolean()->label('Published'),
            ])
            ->recordUrl(null)
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-o-eye')
                        ->label('View')
                        ->url(fn (Event $e) => route('event.show', $e->slug))->openUrlInNewTab(),
                    Action::make('download_register_qr')
                        ->icon('heroicon-o-qr-code')
                        ->label('Download QR Registrasi')
                        ->url(fn (Event $e) => route('event.register.qr', $e->slug))
                        ->openUrlInNewTab(),
                    Action::make('genearte_short_link')
                        ->icon('heroicon-o-link')
                        ->label('Generate Short Link')
                        ->disabled(fn (Event $e) => ! empty($e->short_link))
                        ->action(function (Event $event) {
                            $svc = app(ShortLinkService::class);
                            $longUrl = $svc->eventRegisterUrl($event->slug);
                            $short = $svc->shortenTinyURL($longUrl);

                            if ($short) {
                                $event->short_link = $short;
                                $event->save();
                                Notification::make()
                                    ->title('Short link generated')
                                    ->body($short)
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Gagal membuat short link')
                                    ->danger()
                                    ->send();
                            }
                        }),
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
                        ->disabled(fn (Event $e) => ! (bool) data_get($e->brand, 'wa_blast_enabled', true))
                        ->tooltip(fn (Event $e) => (bool) data_get($e->brand, 'wa_blast_enabled', true) ? null : 'Blast WA dinonaktifkan untuk event ini')
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
                            try {
                                $result = $svc->blast($event, (string) $data['message'], (bool) (false));

                                $notif = Notification::make()
                                    ->title('Blast WA dijadwalkan')
                                    ->body("Total: {$result['total']}\nDikirim: {$result['dispatched']}")
                                    ->success()
                                    ->persistent();

                                $notif->send();
                                try { $notif->sendToDatabase(Auth::user()); } catch (\Throwable) {}
                            } catch (\Throwable $e) {
                                $notif = Notification::make()
                                    ->title('Blast WA gagal')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->persistent();
                                $notif->send();
                                try { $notif->sendToDatabase(Auth::user()); } catch (\Throwable) {}
                            }
                        }),
                    Action::make('toggle_wa_blast')
                        ->icon(fn (Event $e) => (bool) data_get($e->brand, 'wa_blast_enabled', true) ? 'heroicon-o-bolt-slash' : 'heroicon-o-bolt')
                        ->label(fn (Event $e) => (bool) data_get($e->brand, 'wa_blast_enabled', true) ? 'Disable WA Blast' : 'Enable WA Blast')
                        ->color(fn (Event $e) => (bool) data_get($e->brand, 'wa_blast_enabled', true) ? 'danger' : 'success')
                        ->visible(fn () => Auth::check() && (Auth::user()?->role === 'super_admin'))
                        ->requiresConfirmation()
                        ->modalHeading('Ubah status WA Blast?')
                        ->modalDescription(fn (Event $e) => (bool) data_get($e->brand, 'wa_blast_enabled', true)
                            ? 'Menonaktifkan Blast WA untuk event ini.'
                            : 'Mengaktifkan kembali Blast WA untuk event ini.')
                        ->action(function (Event $event) {
                            $brand = (array) ($event->brand ?? []);
                            $current = (bool) data_get($brand, 'wa_blast_enabled', true);
                            $brand['wa_blast_enabled'] = ! $current;
                            $event->update(['brand' => $brand]);

                            $n = Notification::make()
                                ->title('Pengaturan WA Blast diupdate')
                                ->body('Status: ' . (! $current ? 'Enabled' : 'Disabled'))
                                ->success();
                            $n->send();
                            try { $n->sendToDatabase(Auth::user()); } catch (\Throwable) {}
                        }),
                    Action::make('delete')
                        ->icon('heroicon-m-trash')
                        ->label('Delete')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Event')
                        ->modalDescription('Apakah Anda yakin ingin menghapus event ini? Tindakan ini tidak dapat dibatalkan.')
                        ->action(fn (Event $e) => $e->delete()),
                ])
            ]);
    }

    protected static function getResourceUrl(string $name, Event $event): string
    {
        $res = EventResource::class;
        return $res::getUrl($name, ['record' => $event]);
    }
}
