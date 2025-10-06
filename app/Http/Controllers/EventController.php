<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Response;

class EventController extends Controller {

    public function index() {
        $events = Event::where('is_published', true)->orderBy('starts_at', 'desc')->get();
        return view('public.events.index', compact('events'));
    }

    public function show(string $slug) {
        $event = Event::whereSlug($slug)->where('is_published', true)->firstOrFail();
        return view('public.events.show', compact('event')); 
    }

    public function eventImage(string $img) {
        $img = ltrim($img, '/');
        if (str_contains($img, '..')) abort(403);

        $path = storage_path("app/public/{$img}");
        abort_unless(file_exists($path), 404);

        $mime = mime_content_type($path) ?: 'application/octet-stream';

        $response = response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($img) . '"',
        ]);

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        return $response;
    }
}
