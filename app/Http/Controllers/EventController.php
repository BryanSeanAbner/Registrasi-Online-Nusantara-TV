<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller {

    public function index() {
        $events = Event::where('is_published', true)->orderBy('starts_at', 'desc')->get();
        return view('public.events.index', compact('events'));
    }

    public function show(string $slug) {
        $event = Event::whereSlug($slug)->where('is_published', true)->firstOrFail();
        return view('public.events.show', compact('event')); 
    }
}
