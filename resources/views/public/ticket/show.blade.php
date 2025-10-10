@extends('public.shared.layout')

@section('content')
  <section class="px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm">
      {{-- Judul --}}
      <h1 class="text-2xl font-bold text-gray-900">E-Ticket</h1>  

      {{-- Nama & Event --}}
      <p class="mt-3 text-gray-700">
        <span class="font-medium">{{ $reg->name }}</span><br>
        <span class="text-sm text-gray-500">{{ $reg->event->title }}</span>
      </p>

      {{-- QR Code --}}
      <div class="mt-6 flex justify-center">
        <div class="rounded-lg border bg-gray-50 p-4 shadow-inner">
          <img src="{{ route('ticket.qr', $reg->code) }}" 
               alt="QR Code"
               class="w-56 h-56 object-contain" />
        </div>
      </div>

      {{-- Status Check-in --}}
      {{-- @if($reg->checked_in_at)
        <p class="mt-6 text-sm font-medium text-green-600">
          ✅ Sudah check-in pada {{ $reg->checked_in_at->format('d/m H:i') }}
        </p>
      @else
        <p class="mt-6 text-sm text-gray-500">Belum check-in</p>
      @endif --}}
      <p class="mt-6 text-sm text-gray-500">Silahkan tunjukkan QR Code ini saat check-in.</p>
    </div>
  </section>
@endsection
