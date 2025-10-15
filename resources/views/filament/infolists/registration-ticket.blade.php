@php
/** @var \App\Models\Registration $record */
    $record = $getRecord();
    $code = (string) ($record->code ?? '');
    $qrUrl = $code ? route('ticket.qr', ['code' => $code]) : null;
    $ticketUrl = $code ? route('ticket.show', ['code' => $code]) : null;
@endphp

<div>
  @if ($qrUrl)
    <a href="{{ $ticketUrl }}" target="_blank" rel="noopener">
      <img src="{{ $qrUrl }}" alt="QR {{ $code }}" width="220">
    </a>
  @else
    <p>Belum ada kode tiket.</p>
  @endif

  @if ($code)
    <p>Kode: <strong>{{ $code }}</strong></p>
    <p>
      <x-filament::link href="{{ $ticketUrl }}" target="_blank" rel="noopener">
        Lihat Tiket
      </x-filament::link>
    </p>
  @endif
</div>
