<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeatController extends Controller
{
    public function map(Event $event)
    {
        // Ambil semua kursi dan status terpakai/tersedia
        $seats = Seat::with('assignment')
            ->where('event_id', $event->id)
            ->orderBy('section')->orderBy('row')->orderBy('col')
            ->get()
            ->map(fn($s)=>[
                'id' => $s->id,
                'label'=>$s->label,
                'section'=>$s->section,
                'row'=>$s->row,
                'col'=>$s->col,
                'type'=>$s->type,
                'status'=>$s->status,
                'taken'=> (bool) $s->assignment, // true jika sudah ditempati
            ]);

        return response()->json(['data'=>$seats]);
    }

    public function assign(Request $req, Event $event)
    {
        $validated = $req->validate([
            'seat_id' => 'required|exists:seats,id',
            'registration_id' => 'required|exists:registrations,id',
        ]);

        $userId = $req->user()?->id;

        return DB::transaction(function () use ($validated, $event, $userId) {
            /** @var Seat $seat */
            $seat = Seat::where('event_id',$event->id)->lockForUpdate()->findOrFail($validated['seat_id']);

            if ($seat->status !== 'available') {
                return response()->json(['message'=>'Kursi tidak tersedia.'], 409);
            }

            // Pastikan registrasi belum punya kursi
            $already = SeatAssignment::where('registration_id',$validated['registration_id'])->exists();
            if ($already) {
                return response()->json(['message'=>'Registrasi sudah memiliki kursi.'], 409);
            }

            // Cek kursi belum dipakai (unik seat_id dijaga oleh constraint juga)
            if (SeatAssignment::where('seat_id',$seat->id)->exists()) {
                return response()->json(['message'=>'Kursi baru saja dipilih pihak lain.'], 409);
            }

            SeatAssignment::create([
                'event_id' => $event->id,
                'seat_id' => $seat->id,
                'registration_id' => $validated['registration_id'],
                'assigned_by' => $userId,
                'assigned_at' => now(),
            ]);

            return response()->json(['message'=>'Kursi berhasil ditetapkan.']);
        });
    }
}
