<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    public function map(Request $req, Event $event)
    {
        $registrationId = (int) $req->query('registration_id');

        $seats = Seat::where('event_id', $event->id)
        ->get()
        ->map(function ($s) use ($registrationId) {
            $assignment = $s->assignment;
            $takenByOther = $assignment && $assignment->registration_id !== $registrationId;

            return [
                'id'      => $s->id,
                'label'   => $s->label,
                'section' => $s->section,
                'row'     => $s->row,
                'col'     => $s->col,
                'status'  => $s->status,
                'taken'   => (bool) $takenByOther,
            ];
        });

        return response()->json(['data'=>$seats]);
    }

    public function assign(Request $req, Event $event)
    {
        $validated = $req->validate([
            'seat_id'         => ['required','integer','exists:seats,id'],
            'registration_id' => ['required','integer','exists:registrations,id'],
        ]);

        $seatBelongs = \App\Models\Seat::whereKey($validated['seat_id'])
            ->where('event_id', $event->id)
            ->exists();

        $regBelongs = \App\Models\Registration::whereKey($validated['registration_id'])
            ->where('event_id', $event->id)
            ->exists();

        if (! $seatBelongs || ! $regBelongs) {
            return response()->json(['message' => 'Data tidak sesuai event.'], 422);
        }

        $userId = $req->user()?->id;

        return DB::transaction(function () use ($validated, $event, $userId) {

            /** @var \App\Models\Seat $seat */
            $seat = \App\Models\Seat::where('event_id', $event->id)
                ->lockForUpdate()
                ->findOrFail($validated['seat_id']);

            /** @var \App\Models\SeatAssignment|null $current */
            $current = \App\Models\SeatAssignment::where('event_id', $event->id)
                ->where('registration_id', $validated['registration_id'])
                ->lockForUpdate()
                ->first();

            if ($current && $current->seat_id == $seat->id) {
                return response()->json(['message' => 'Kursi tidak berubah.'], 200);
            }

            if ($seat->status !== 'available') {
                return response()->json(['message' => 'Kursi tidak tersedia.'], 409);
            }

            $takenByOther = \App\Models\SeatAssignment::where('event_id', $event->id)
                ->where('seat_id', $seat->id)
                ->where('registration_id', '!=', $validated['registration_id'])
                ->lockForUpdate()
                ->exists();

            if ($takenByOther) {
                return response()->json(['message' => 'Kursi sudah diambil pihak lain.'], 409);
            }

            if ($current) {
                $current->update([
                    'seat_id'     => $seat->id,
                    'assigned_by' => $userId,
                    'assigned_at' => now(),
                ]);
                return response()->json(['message' => 'Kursi berhasil diubah.'], 200);
            }

            \App\Models\SeatAssignment::create([
                'event_id'        => $event->id,
                'registration_id' => $validated['registration_id'],
                'seat_id'         => $seat->id,
                'assigned_by'     => $userId,
                'assigned_at'     => now(),
            ]);

            return response()->json(['message' => 'Kursi berhasil ditetapkan.'], 201);
        });
    }

    public function unassign(Request $req, Event $event)
    {
        $registrationId = (int) $req->query('registration_id');

        SeatAssignment::where('event_id', $event->id)
            ->where('registration_id', $registrationId)
            ->delete();

        return response()->json(['ok' => true]);
    }
}
