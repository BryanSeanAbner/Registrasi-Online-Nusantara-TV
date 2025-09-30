<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function show(string $code)
    {
        $reg = Registration::where('code', $code)->first();
        if (!$reg) return response()->json(['message' => 'Kode tidak ditemukan'], 404);

        return response()->json([
            'status' => $reg->status,
            'checked_in_at' => $reg->checked_in_at,
        ]);
    }

    public function checkin(Request $request, string $code)
    {
        $reg = Registration::where('code', $code)->first();
        if (!$reg) return response()->json(['message' => 'Kode tidak ditemukan'], 404);
        if ($reg->status !== Registration::ST_APPROVED) {
            return response()->json(['message' => 'Belum approved'], 422);
        }
        if ($reg->checked_in_at) {
            return response()->json(['message' => 'Sudah check-in'], 409);
        }

        $reg->forceFill(['checked_in_at' => now()])->save();

        return response()->json(['message' => 'Check-in OK', 'time' => $reg->checked_in_at]);
    }
}
