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
        $event = $this->events->findPublishedBySlug($slug);
        return $this->participantsViewFor($event);
    }

    public function participantsLatest()
    {
        $event = Event::where('is_published', true)
            ->orderByDesc('starts_at')
            ->firstOrFail();
        return $this->participantsViewFor($event);
    }

    private function participantsViewFor(Event $event)
    {
        $registrations = Registration::with(['values.field'])
            ->where('event_id', $event->id)
            ->where('checked_in_at', '!=', null)
            ->orderByDesc('created_at')
            ->get();
        $total = $registrations->count();

        return view('public.register.participant', compact('event', 'registrations', 'total'));
    }

    public function eventImage(string $img)
    {
        return $this->assets->streamPublicImage($img);
    }
}
