<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use App\Models\Scan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    public function page(Request $request){ 
        // if ($request->q) {
        //     $request->merge(['code'=>$request->q]);
        //     return $this->scan($request);
        // }
        return view('public.scan.page'); 
    }

    // public function scan(Request $request){
    //     $data = $request->validate(['code'=>'required']);
    //     $reg = Registration::where('qr_code',$data['code'])->first();
    //     if(!$reg) return response()->json(['ok'=>false,'msg'=>'QR tidak ditemukan']);

    //     if($reg->checked_in_at) return response()->json([
    //         'ok'=>false,
    //         'msg'=>'Sudah check-in: '.$reg->checked_in_at->format('d/m H:i')
    //     ]);
        
    //     $reg->update(['checked_in_at'=>now()]);
    //     return response()->json(['ok'=>true,'msg'=>'Check-in sukses untuk '.$reg->name]);
    // }

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
            return response()->json([
                'ok' => false,
                'msg' => 'Sudah check-in'
            ], 409);
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
            'time' => $reg->checked_in_at
        ], 200);
    }
}
