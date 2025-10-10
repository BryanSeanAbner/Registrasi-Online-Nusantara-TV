@extends('public.shared.layout')

@section('content')
<section class="px-4 py-12">
  <div class="mx-auto w-full max-w-5xl">
    <div class="mb-6 flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Peserta Event</h1>
        @isset($event)
          <p class="mt-1 text-sm text-gray-600">{{ $event->title }}</p>
        @endisset
      </div>
      <span class="text-sm font-medium text-gray-900">Total: {{ $total ?? ($registrations->count() ?? 0) }}</span>
    </div>

    @php $list = $registrations ?? collect(); @endphp

    @if($list->isEmpty())
      <p class="text-gray-600">Belum ada peserta terdaftar.</p>
    @else
      @php
        $namePatterns = '/^(name|full[_\s]?name|nama(_lengkap)?|nama\s+lengkap)$/i';
      @endphp
      <!-- Tabel responsif -->
      <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">#</th>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Nama</th>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Waktu Check-in</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            @foreach($list as $i => $reg)
              @php
                $derivedName = $reg->name ?? null;
                if (!$derivedName && isset($reg->values)) {
                  $val = $reg->values->first(function($v) use ($namePatterns) {
                    $fname = $v->field->name ?? '';
                    return is_string($fname) && preg_match($namePatterns, $fname);
                  });
                  $derivedName = $val->value ?? null;
                }
                $time = $reg->checked_in_at ?: $reg->created_at;
                $tz = config('app.timezone', 'Asia/Jakarta');
              @endphp
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $derivedName ?: '-' }}</td>
                <td class="px-6 py-3 text-sm text-gray-800">{{ optional($time)->setTimezone($tz)->locale('id')->translatedFormat('d F Y H:i') }} WIB</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</section>
@endsection