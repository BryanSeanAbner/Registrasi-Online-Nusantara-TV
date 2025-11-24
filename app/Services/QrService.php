<?php

namespace App\Services;

use App\Models\Event;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrService
{
    public function makePng(string $text, int $size = 600, int $margin = 1): string
    {
        return QrCode::format('png')->size($size)->margin($margin)->generate($text);
    }

    /**
     * Generate registration QR PNG for an event.
     * Uses short_link if available, otherwise falls back to registration URL.
     */
    public function registrationQrForEvent(Event $event, int $size = 600, int $margin = 1): string
    {
        $url = $event->short_link ?: route('register.create', ['slug' => $event->slug]);
        return $this->makePng($url, $size, $margin);
    }
}

