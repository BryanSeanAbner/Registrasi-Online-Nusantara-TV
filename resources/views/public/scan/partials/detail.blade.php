<div class="border rounded p-4 space-y-1 bg-white shadow-sm">
  <div class="text-lg font-semibold mb-2">Detail Registrasi</div>
  <div><b>Nama:</b> {{ $reg->name }}</div>
  <div><b>Kode:</b> {{ $reg->code }}</div>
  <div><b>Event:</b> {{ $reg->event->title }}</div>
  <div><b>Kursi:</b> <span id="seat-label">{{ $reg->seatAssignment->seat->label ?? 'Belum ditetapkan' }}</span></div>
</div>

@if(! $reg->seatAssignment)
  <button id="btn-open-seat" class="mt-4 px-3 py-2 rounded bg-indigo-600 text-white">Pilih Kursi</button>

  <div id="seat-widget" class="hidden mt-3"
       data-seat-widget="1"
       data-event-id="{{ $reg->event_id }}"
       data-registration-id="{{ $reg->id }}">
    <div class="flex gap-3 items-center text-xs mb-2">
      <span class="inline-block w-3 h-3 rounded bg-green-600"></span> Tersedia
      <span class="inline-block w-3 h-3 rounded bg-gray-400"></span> Terisi
      <span class="inline-block w-3 h-3 rounded bg-amber-500"></span> Diblok
    </div>
    <div data-seat-status class="text-sm text-gray-500">Memuat peta kursi…</div>
    <div data-seat-sections class="space-y-3"></div>
  </div>
@endif
