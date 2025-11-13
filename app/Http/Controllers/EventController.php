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

    private function participantsViewFor(Event $event)
    {
        $event->load('formFields');
        $registrations = Registration::with(['values.field'])
            ->where('event_id', $event->id)
            ->where('checked_in_at', '!=', null)
            ->orderByDesc('created_at')
            ->get();
        $total = $registrations->count();

        return view('public.register.participant', compact('event', 'registrations', 'total'));
    }

    public function participantRowJson(Event $event, Registration $registration)
    {
        abort_unless($registration->event_id === $event->id, 404);
        $registration->load(['values.field']);

        $values = $registration->values
            ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $event->id)
            ->map(fn ($fv) => [
                'field_id' => $fv->field->id,
                'type' => $fv->field->type,
                'value' => $fv->value,
            ])
            ->values();

        return response()->json([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'code' => $registration->code,
            'checked_in_at' => optional($registration->checked_in_at)->toIso8601String(),
            'values' => $values,
        ]);
    }

    public function eventImage(string $img)
    {
        return $this->assets->streamPublicImage($img);
    }
}
