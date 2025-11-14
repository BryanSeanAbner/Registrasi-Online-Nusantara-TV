<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShortLinkService
{
    public function publicBaseUrl(): string
    {
        $ngrok = rtrim((string) config('services.ngrok.url'), '/');
        $appUrl = rtrim((string) config('app.url'), '/');
        $mode = (string) config('services.shortlink.base', 'auto');

        if ($mode === 'ngrok') {
            return $ngrok !== '' ? $ngrok : ($appUrl !== '' ? $appUrl : 'http://localhost');
        }

        if ($mode === 'app') {
            return $appUrl !== '' ? $appUrl : ($ngrok !== '' ? $ngrok : 'http://localhost');
        }

        if (app()->environment('local')) {
            return $ngrok !== '' ? $ngrok : ($appUrl !== '' ? $appUrl : 'http://localhost');
        }

        return $appUrl !== '' ? $appUrl : ($ngrok !== '' ? $ngrok : 'http://localhost');
    }

    public function eventRegisterUrl(string $slug): string
    {
        return $this->publicBaseUrl() . '/e/' . $slug . '/register';
    }

    /**
     * Shorten a URL using the Cutt.ly API.
     * Returns the short URL on success, or null on any failure.
     */
    public function shortenCuttLy(string $url): ?string
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

    /**
     * Shorten a URL using the TinyURL API.
     * Returns the short URL on success, or null on any failure.
     */    
    public function shortenTinyURL(string $url): ?string
    {
        $apiKey = (string) (config('services.tinyurl.key'));
        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                ])
                ->post('https://api.tinyurl.com/create', [
                    'url' => $url,
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $data = $response->json('data');
        if (! is_array($data)) {
            return null;
        }

        $short = $data['tiny_url'] ?? null;
        return $short ?: null;
    }
}
