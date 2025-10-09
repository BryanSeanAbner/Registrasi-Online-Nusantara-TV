<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Scan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScanService
{
    /**
     * Process scan by registration code and perform check-in if needed.
     * Returns an array suitable for JSON response: [ok, msg, code, time].
     */
    public function scanByCode(string $code, ?int $userId = null): array
    {
        $reg = Registration::where('code', $code)->first();

        if (! $reg) {
            return [
                'status' => 404,
                'payload' => [
                    'ok' => false,
                    'msg' => 'Kode tidak ditemukan',
                ],
            ];
        }

        if ($reg->status !== Registration::ST_APPROVED) {
            return [
                'status' => 422,
                'payload' => [
                    'ok' => false,
                    'msg' => 'Belum approved',
                ],
            ];
        }

        if ($reg->checked_in_at) {
            $seat = $reg->seatAssignment?->seat->label;

            if (! $seat) {
                return [
                    'status' => 200,
                    'payload' => [
                        'ok' => true,
                        'msg' => 'Silahkan pilih Kursi Anda',
                        'time' => $reg->checked_in_at,
                        'code' => $reg->code,
                    ],
                ];
            }

            return [
                'status' => 200,
                'payload' => [
                    'ok' => true,
                    'msg' => 'Sudah check-in',
                    'code' => $reg->code,
                ],
            ];
        }

        DB::transaction(function () use ($reg, $userId) {
            $reg->forceFill(['checked_in_at' => now()])->save();
            Scan::create([
                'registration_id' => $reg->id,
                'code' => $reg->code,
                'scanned_at' => $reg->checked_in_at,
                'scanned_by' => $userId ?? Auth::id(),
            ]);
        });

        return [
            'status' => 200,
            'payload' => [
                'ok' => true,
                'msg' => 'Check-in OK',
                'time' => $reg->checked_in_at,
                'code' => $reg->code,
            ],
        ];
    }

    /**
     * Render scan fragment HTML for a registration code.
     */
    public function renderFragment(string $code): string
    {
        $reg = Registration::with(['event', 'seatAssignment.seat', 'fieldValues.field'])
            ->where('code', $code)
            ->firstOrFail();

        return view('public.scan.partials.detail', compact('reg'))->render();
    }
}

