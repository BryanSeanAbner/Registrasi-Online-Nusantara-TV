<?php

namespace App\Jobs;

use App\Models\Registration;
use App\Services\RegistrationApprovalService;
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

    public function __construct(public Registration $registration) {}

    public function handle(RegistrationApprovalService $service): void
    {
        $reg = $this->registration->fresh();

        if (!$reg || !$reg->exists) {
            return;
        }

        $service->sendWaMessage($reg);
    }
}
