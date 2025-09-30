<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Event,Registration};
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode; 
use Illuminate\Support\Facades\Storage;

class RegisterController extends Controller
{
    public function create(string $slug) { 
        $event=Event::whereSlug($slug)->firstOrFail(); 
        return view('public.register.form', compact('event')); 
    }

    public function store(Request $r, string $slug){
        $event=Event::whereSlug($slug)->firstOrFail();
        $data=$r->validate([ 
            'name'=>'required|string|max:120',
            'phone'=>'required|string',
            'email'=>'nullable|email',
            'company'=>'nullable|string'
        ]);

        return DB::transaction(function() use ($data,$event) {
            $code = Str::ulid();
            $reg = Registration::create($data + ['event_id'=>$event->id,'qr_code'=>$code]);
            // generate QR png
            // $png = QrCode::format('png')->size(512)->generate(route('ticket.show',$reg->qr_code));
            $png = QrCode::format('png')->size(300)->generate($reg->qr_code);
            Storage::disk('public')->put("qr/{$reg->qr_code}.png", $png);

            // // kirim WhatsApp
            // app(Whatsapp::class)->send(
            // to: $reg->phone,
            // text: "Halo {$reg->name}! Pendaftaran {$event->title} berhasil. E-ticket: ".route('ticket.show',$reg->qr_code)
            // );
            
            return redirect()->route('ticket.show',$reg->qr_code)->with('ok','Registrasi berhasil. Link tiket juga dikirim via WhatsApp.');
        });
    }
}
