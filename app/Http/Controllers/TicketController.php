<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Registration;

class TicketController extends Controller
{
   public function show(string $code){
      $reg = Registration::where('qr_code',$code)->firstOrFail();
      return view('public.ticket.show', compact('reg'));
   }
}
