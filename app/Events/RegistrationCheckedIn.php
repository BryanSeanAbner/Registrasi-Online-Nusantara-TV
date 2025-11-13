<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegistrationCheckedIn implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $registrationId;
    public int $eventId;
    public ?string $checkedInAt;
    public string $code;

    public function __construct(int $registrationId, int $eventId, ?string $checkedInAt, string $code)
    {
        $this->registrationId = $registrationId;
        $this->eventId = $eventId;
        $this->checkedInAt = $checkedInAt;
        $this->code = $code;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('registrations.' . $this->eventId),
            new Channel('registrations'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'RegistrationCheckedIn';
    }

    public function broadcastWith(): array
    {
        return [
            'registration_id' => $this->registrationId,
            'event_id' => $this->eventId,
            'checked_in_at' => $this->checkedInAt,
            'code' => $this->code,
        ];
    }
}
