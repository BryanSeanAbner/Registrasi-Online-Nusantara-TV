<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Registration;

class EventController extends Controller {

    public function index() {
        $events = Event::where('is_published', true)->orderBy('starts_at', 'desc')->get();
        return view('public.events.index', compact('events'));
    }

    public function show(string $slug) {
        $event = Event::whereSlug($slug)->where('is_published', true)->firstOrFail();
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
            ->get();
        $total = $registrations->count();

        return view('public.register.participant', compact('event','registrations','total'));
    }
}
