<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShortLinkService
{
    /**
     * Shorten a URL using the Cutt.ly API.
     * Returns the short URL on success, or null on any failure.
     */
    public function shorten(string $url): ?string
    {
        $apiKey = (string) (config('services.cuttly.key'));
        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(10)->get('https://cutt.ly/api/api.php', [
                'key' => $apiKey,
                'short' => $url,
            ]);

            if (! $response->ok()) {
                return null;
            }

            $data = $response->json('url');
            if (! is_array($data)) {
                return null;
            }

            $status = (int) ($data['status'] ?? 0);
            // Cutt.ly success status is 7
            if ($status !== 7) {
                return null;
            }

            $short = $data['shortLink'] ?? null;
            return $short ?: null;
        } catch (\Throwable) {
            return null;
        }
    }
}

