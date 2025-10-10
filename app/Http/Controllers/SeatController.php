<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Services\SeatService;

class SeatController extends Controller
{
    public function __construct(protected SeatService $seats)
    {
    }

    public function map(Request $req, Event $event)
    {
        $registrationId = (int) $req->query('registration_id');
        $data = $this->seats->map($event, $registrationId);
        return response()->json(['data' => $data]);
    }

    public function assign(Request $req, Event $event)
    {
        $validated = $req->validate([
            'seat_id'         => ['required','integer','exists:seats,id'],
            'registration_id' => ['required','integer','exists:registrations,id'],
        ]);

        $result = $this->seats->assign(
            $event,
            (int) $validated['seat_id'],
            (int) $validated['registration_id'],
            $req->user()?->id,
        );

        return response()->json(['message' => $result['message']], $result['status']);
    }

    public function unassign(Request $req, Event $event)
    {
        $registrationId = (int) $req->query('registration_id');
        $this->seats->unassign($event, $registrationId);
        return response()->json(['ok' => true]);
    }
}
