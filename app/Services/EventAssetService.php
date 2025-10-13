<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EventAssetService
{
    /**
     * Stream a public image stored under storage/app/public.
     * Validates input to prevent traversal and sets CORS headers.
     */
    public function streamPublicImage(string $img): BinaryFileResponse
    {
        $img = ltrim($img, '/');

        if (str_contains($img, '..')) {
            abort(403);
        }

        $path = storage_path("app/public/{$img}");
        abort_unless(file_exists($path), 404);

        $mime = mime_content_type($path) ?: 'application/octet-stream';

        $response = Response::file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($img) . '"',
        ]);

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        return $response;
    }
}
