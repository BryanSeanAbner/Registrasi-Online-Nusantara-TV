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

class SendWaMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function backoff(): array
    {
        return [5, 15, 60];
    }

    public function __construct(
        public Registration $registration, 
        public ?int $messageId = null, 
        public ?int $notifyUserId = null,
        public ?string $phone
    ) {}

    public function handle(RegistrationApprovalService $service): void
    {
        $reg = $this->registration->fresh();

        if (!$reg || !$reg->exists) {
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

        try {
            $service->sendWaMessage($reg);
            if ($msgModel) {
                $msgModel->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
            // DB notification success
            $this->notifyDatabase(
                title: 'WA terkirim',
                body: "Pesan WA berhasil dikirim ke {$this->phone}.",
                url: \App\Filament\Resources\Registration\RegistrationResource::getUrl('view', ['record' => $reg]),
                success: true,
                userId: $this->notifyUserId,
            );
        } catch (\Throwable $e) {
            if ($msgModel) {
                $msgModel->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error' => $e->getMessage(),
                ]);
            }
            // DB notification failure
            $this->notifyDatabase(
                title: 'WA gagal dikirim',
                body: 'Pengiriman WA gagal: ' . $e->getMessage(),
                url: \App\Filament\Resources\Registration\RegistrationResource::getUrl('view', ['record' => $reg]),
                success: false,
                userId: $this->notifyUserId,
            );
            throw $e;
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
            // ignore
        }
    }
}
