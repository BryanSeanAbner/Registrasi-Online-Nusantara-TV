<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Services\RegistrationApprovalService;
use Filament\Notifications\Notification;
use Filament\Actions\Action as NotificationAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCustomWaMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function backoff(): array
    {
        return [5, 15, 60];
    }

    public function __construct(
        public Registration $registration,
        public string $message,
        public ?string $url = null,
        public ?int $messageId = null,
        public ?int $notifyUserId = null,
    ) {}

    public function handle(RegistrationApprovalService $service): void
    {
        $reg = $this->registration->fresh();

        if (! $reg || ! $reg->exists) {
            return;
        }
        $msgModel = null;
        if ($this->messageId) {
            $msgModel = \App\Models\WaMessage::find($this->messageId);
        }

        if ($msgModel) {
            $msgModel->update([
                'dispatched_at' => now(),
                'status' => 'dispatched',
            ]);
        }

        $caught = null;
        try {
            $service->sendCustomWaMessage($reg, $this->message, $this->url);
            if ($msgModel) {
                $msgModel->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                if ($msgModel->blast_id) {
                    optional($msgModel->blast)->increment('sent');
                }
            }
            if (! $msgModel || ! $msgModel->blast_id) {
                $this->notifyDatabase(
                    title: 'WA terkirim',
                    body: 'Pesan WA berhasil dikirim ke peserta.',
                    url: \App\Filament\Resources\Registration\RegistrationResource::getUrl('view', ['record' => $reg]),
                    success: true,
                    userId: $this->notifyUserId ?: optional($msgModel?->blast)->initiated_by,
                );
            }
        } catch (\Throwable $e) {
            if ($msgModel) {
                $msgModel->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error' => $e->getMessage(),
                ]);
                if ($msgModel->blast_id) {
                    optional($msgModel->blast)->increment('failed');
                }
            }
            // Notifikasi kegagalan selalu dikirim (baik single maupun blast)
            $this->notifyDatabase(
                title: 'WA gagal dikirim',
                body: 'Pengiriman WA gagal: ' . $e->getMessage(),
                url: \App\Filament\Resources\Registration\RegistrationResource::getUrl('view', ['record' => $reg]),
                success: false,
                userId: $this->notifyUserId ?: optional($msgModel?->blast)->initiated_by,
            );
            $caught = $e;
        } finally {
            // Jika bagian dari blast, cek apakah semua pesan sudah selesai dan kirim ringkasan
            if ($msgModel && $msgModel->blast_id) {
                $this->maybeNotifyBlastSummary($msgModel->blast_id);
            }
            if ($caught) {
                throw $caught;
            }
        }
    }

    private function notifyDatabase(string $title, string $body, string $url, bool $success, ?int $userId): void
    {
        if (! $userId) return;
        try {
            $user = \App\Models\User::find($userId);
            if (! $user) return;

            $n = Notification::make()
                ->title($title)
                ->body($body)
                ->icon($success ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle');
            if ($success) {
                $n->success();
            } else {
                $n->danger();
            }
            $n->actions([
                NotificationAction::make('view')
                    ->button()
                    ->label('View')
                    ->url($url)
                    ->openUrlInNewTab(),
            ])
            ->persistent()
            ->sendToDatabase($user);
        } catch (\Throwable) {
            // Ignore notification delivery failures
        }
    }

    private function maybeNotifyBlastSummary(int $blastId): void
    {
        try {
            $blast = \App\Models\WaBlast::find($blastId);
            if (! $blast) return;

            $remaining = \App\Models\WaMessage::where('blast_id', $blastId)
                ->whereIn('status', ['queued', 'dispatched'])
                ->count();

            if ($remaining === 0) {
                // Hindari duplikasi notifikasi dengan conditional update
                $updated = \App\Models\WaBlast::where('id', $blastId)
                    ->where('status', '!=', 'completed')
                    ->update([
                        'status' => 'completed',
                        'finished_at' => now(),
                    ]);

                if ($updated > 0) {
                    // Refresh blast to get latest counters
                    $blast = $blast->fresh();
                    $userId = $blast->initiated_by;
                    if ($userId) {
                        $user = \App\Models\User::find($userId);
                        if ($user) {
                            Notification::make()
                                ->title('Blast WA selesai')
                                ->body("Total: {$blast->total}\nTerkirim: {$blast->sent}\nGagal: {$blast->failed}")
                                ->success()
                                ->persistent()
                                ->sendToDatabase($user);
                        }
                    }
                }
            }
        } catch (\Throwable) {}
    }
}
