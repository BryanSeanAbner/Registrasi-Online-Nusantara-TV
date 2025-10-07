<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Scan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function page(){ 
        return view('public.scan.page'); 
    }

    public function scan(Request $request)
    {
        $data = $request->validate(['code'=>'required']);
        $reg = Registration::where('code', $data['code'])->first();

        if (!$reg) return response()->json([
            'ok' => false,
            'msg' => 'Kode tidak ditemukan'
        ], 404);

        if ($reg->status !== Registration::ST_APPROVED) {
            return response()->json([
                'ok' => false,
                'msg' => 'Belum approved'
            ], 422);
        }
        if ($reg->checked_in_at) {
            $seat = $reg->seatAssignment?->seat->label;

            if (!$seat) {
                return response()->json([
                    'ok' => true,
                    'msg' => 'Silahkan pilih Kursi Anda', 
                    'time' => $reg->checked_in_at,
                    'code' => $reg->code
                ], 200);
            }

            return response()->json([
                'ok' => true,
                'msg' => 'Sudah check-in',
                'code' => $reg->code
            ], 200);
        }

        DB::transaction(function () use ($reg) {
            $reg->forceFill(['checked_in_at' => now()])->save();
            Scan::create([
                'registration_id' => $reg->id,
                'code' => $reg->code,
                'scanned_at' => $reg->checked_in_at,
                'scanned_by' => Auth::id(),
            ]);
        });

        return response()->json([
            'ok' => true,
            'msg' => 'Check-in OK', 
            'time' => $reg->checked_in_at,
            'code' => $reg->code,
        ], 200);
    }

    public function fragment(string $code)
    {
        $reg = Registration::with(['event','seatAssignment.seat', 'fieldValues.field'])
            ->where('code', $code)->firstOrFail();
        
        $html = view('public.scan.partials.detail', compact('reg'))->render();

        return response()->json(['html' => $html]);
    }
}
