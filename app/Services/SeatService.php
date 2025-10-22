<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatAssignment;
use Illuminate\Support\Facades\DB;

class SeatService
{
    public function map(Event $event, int $registrationId): array
    {
        return Seat::with(['assignment', 'table'])
            ->where('event_id', $event->id)
            ->get()
            ->map(function (Seat $s) use ($registrationId) {
                $assignment = $s->assignment;
                $takenByOther = $assignment && $assignment->registration_id !== $registrationId;

                return [
                    'id'      => $s->id,
                    'label'   => $s->label,
                    'section' => $s->section,
                    'row'     => $s->row,
                    'col'     => $s->col,
                    'status'  => $s->status,
                    'table'   => $s->table->label ?? null,
                    'taken'   => (bool) $takenByOther,
                ];
            })->all();
    }

    public function assign(Event $event, int $seatId, int $registrationId, ?int $userId): array
    {
        $seatBelongs = Seat::whereKey($seatId)->where('event_id', $event->id)->exists();
        $regBelongs  = \App\Models\Registration::whereKey($registrationId)->where('event_id', $event->id)->exists();

        if (! $seatBelongs || ! $regBelongs) {
            return ['status' => 422, 'message' => 'Data tidak sesuai event.'];
        }

        return DB::transaction(function () use ($event, $seatId, $registrationId, $userId) {
            /** @var Seat $seat */
            $seat = Seat::where('event_id', $event->id)
                ->lockForUpdate()
                ->findOrFail($seatId);

            /** @var SeatAssignment|null $current */
            $current = SeatAssignment::where('event_id', $event->id)
                ->where('registration_id', $registrationId)
                ->lockForUpdate()
                ->first();

            if ($current && $current->seat_id == $seat->id) {
                return ['status' => 200, 'message' => 'Kursi tidak berubah.'];
            }

            if ($seat->status !== 'available') {
                return ['status' => 409, 'message' => 'Kursi tidak tersedia.'];
            }

            $takenByOther = SeatAssignment::where('event_id', $event->id)
                ->where('seat_id', $seat->id)
                ->where('registration_id', '!=', $registrationId)
                ->lockForUpdate()
                ->exists();

            if ($takenByOther) {
                return ['status' => 409, 'message' => 'Kursi sudah diambil pihak lain.'];
            }

            if ($current) {
                $current->update([
                    'seat_id'     => $seat->id,
                    'assigned_by' => $userId,
                    'assigned_at' => now(),
                ]);

                return ['status' => 200, 'message' => 'Kursi berhasil diubah.'];
            }

            SeatAssignment::create([
                'event_id'        => $event->id,
                'registration_id' => $registrationId,
                'seat_id'         => $seat->id,
                'assigned_by'     => $userId,
                'assigned_at'     => now(),
            ]);

            return ['status' => 201, 'message' => 'Kursi berhasil ditetapkan.'];
        });
    }

    public function unassign(Event $event, int $registrationId): void
    {
        SeatAssignment::where('event_id', $event->id)
            ->where('registration_id', $registrationId)
            ->delete();
    }
}

