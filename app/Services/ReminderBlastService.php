<?php

namespace App\Services;

use App\Jobs\SendCustomWaMessageJob;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\Str;

class ReminderBlastService
{
    public function __construct(private readonly RegistrationApprovalService $approval)
    {
    }

    /**
     * Blast custom WA reminder to all approved registrations of an event.
     *
     * @return array{total:int, dispatched:int}
     */
    public function blast(Event $event, string $template, bool $includeQr = true): array
    {
        $total = 0;
        $dispatched = 0;

        $event->loadMissing('registrations');

        $event->registrations()
            ->where('status', Registration::ST_APPROVED)
            ->orderBy('id')
            ->chunkById(500, function ($regs) use ($event, $template, $includeQr, &$total, &$dispatched) {
                $regs->load(['fieldValues.field']);
                foreach ($regs as $reg) {
                    $total++;

                    $message = $this->renderTemplate($event, $reg, $template, $includeQr);
                    $qrUrl = $includeQr && $reg->code
                        ? (env('WA_LINK_IMG')
                            ? env('WA_LINK_IMG') . "/t/{$reg->code}/qrcode/preview"
                            : url("/t/{$reg->code}/qrcode/preview"))
                        : null;

                    dispatch(new SendCustomWaMessageJob($reg, $message, $qrUrl));
                    $dispatched++;
                }
            });

        return compact('total', 'dispatched');
    }

    private function renderTemplate(Event $event, Registration $reg, string $template, bool $includeQr): string
    {
        $name = (string) ($this->approval->getParticipantName($reg) ?? '');
        $replacements = [
            '{name}'     => $name,
            '{event}'    => (string) ($event->title ?? ''),
            '{location}' => (string) ($event->venue ?? ''),
            '{code}'     => (string) ($reg->code ?? ''),
            '{date}'     => optional($event->starts_at)?->format('d M Y H:i') ?? '',
            '{qr_url}'   => $includeQr && $reg->code
                ? (env('WA_LINK_IMG')
                    ? env('WA_LINK_IMG') . "/t/{$reg->code}/qrcode/preview"
                    : url("/t/{$reg->code}/qrcode/preview"))
                : '',
        ];

        return strtr($template, $replacements);
    }
}

