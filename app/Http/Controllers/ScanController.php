<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;
use Illuminate\Support\Carbon;

class ScanController extends Controller
{
    public function page(Request $request){ 
        // if ($request->q) {
        //     $request->merge(['code'=>$request->q]);
        //     return $this->scan($request);
        // }
        return view('public.scan.page'); 
    }

    public function scan(Request $request){
        $data = $request->validate(['code'=>'required']);
        $reg = Registration::where('qr_code',$data['code'])->first();
        if(!$reg) return response()->json(['ok'=>false,'msg'=>'QR tidak ditemukan']);

        if($reg->checked_in_at) return response()->json([
            'ok'=>false,
            'msg'=>'Sudah check-in: '.$reg->checked_in_at->format('d/m H:i')
        ]);
        
        $reg->update(['checked_in_at'=>now()]);
        return response()->json(['ok'=>true,'msg'=>'Check-in sukses untuk '.$reg->name]);
    }
}
