<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
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

    public function participants(string $slug)
    {
        $event = Event::whereSlug($slug)
            ->where('is_published', true)
            ->firstOrFail();

        $registrations = Registration::with(['values.field'])
            ->where('event_id', $event->id)
            ->orderByDesc('created_at')
            ->get();
        $total = $registrations->count();

        return view('public.register.participant', compact('event','registrations','total'));
    }

    public function participantsLatest()
    {
        $event = Event::where('is_published', true)
            ->orderByDesc('starts_at')
            ->firstOrFail();

        $registrations = Registration::with(['values.field'])
            ->where('event_id', $event->id)
            ->orderByDesc('created_at')
            ->paginate(50);
        $total = $registrations->total();

        return view('public.register.participant', compact('event','registrations','total'));
    }

    public function eventImage(string $img)
    {
        return $this->assets->streamPublicImage($img);
    }
}
