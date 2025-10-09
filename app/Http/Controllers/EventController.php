<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\EventRepositoryInterface as Events;
use App\Services\EventAssetService;

class EventController extends Controller
{
    public function __construct(
        protected Events $events,
        protected EventAssetService $assets,
    ) {}

    public function index()
    {
        $events = $this->events->getPublished();
        return view('public.events.index', compact('events'));
    }

    public function show(string $slug)
    {
        $event = $this->events->findPublishedBySlug($slug);
        return view('public.events.show', compact('event'));
    }

    public function eventImage(string $img)
    {
        return $this->assets->streamPublicImage($img);
    }
}
