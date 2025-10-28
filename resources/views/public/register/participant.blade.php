@extends('public.shared.layout')

@section('content')
<section class="px-4 py-12">
  <div class="mx-auto w-full max-w-7xl 2xl:max-w-screen-2xl">
    <div class="mb-6 flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Peserta Event</h1>
        @isset($event)
          <p class="mt-1 text-sm text-gray-600">{{ $event->title }}</p>
        @endisset
      </div>
      <span class="text-sm font-medium text-gray-900">Total: {{ $total ?? ($registrations->count() ?? 0) }}</span>
    </div>

    @php $list = $registrations ?? collect(); 
    
    @endphp

    @if($list->isEmpty())
      <p class="text-gray-600">Belum ada peserta terdaftar.</p>
    @else
      @php
        $namePatterns = '/^(name|full[_\s]?name|nama(_lengkap)?|nama\s+lengkap)$/i';
      @endphp
      <!-- Tabel responsif -->
      <div class="overflow-x-auto">
        <div class="min-w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
          <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs sm:text-sm lg:text-base font-semibold uppercase tracking-wider text-gray-600">#</th>
              <th class="px-6 py-3 text-left text-xs sm:text-sm lg:text-base font-semibold uppercase tracking-wider text-gray-600">Nama</th>
              <th class="px-6 py-3 text-left text-xs sm:text-sm lg:text-base font-semibold uppercase tracking-wider text-gray-600">Photo</th>
              <th class="px-6 py-3 text-left text-xs sm:text-sm lg:text-base font-semibold uppercase tracking-wider text-gray-600">Waktu Check-in</th>
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
                $photoPath = null;
                if (isset($reg->values)) {
                  $imgVal = $reg->values->first(function($v) {
                    return (($v->field->type ?? null) === 'image') && !empty($v->value);
                  });
                  $photoPath = $imgVal->value ?? null;
                }
                $time = $reg->checked_in_at ?: $reg->created_at;
                $tz = config('app.timezone', 'Asia/Jakarta');
              @endphp
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-3 text-sm lg:text-base 2xl:text-lg text-gray-500">{{ $i + 1 }}</td>
                <td class="px-6 py-3 text-sm lg:text-base 2xl:text-lg font-medium text-gray-900">{{ $derivedName ?: '-' }}</td>
                <td class="px-6 py-3 text-sm lg:text-base 2xl:text-lg text-gray-900">
                  @if($photoPath)
                    <img src="{{ asset('storage/' . $photoPath) }}" alt="Foto Peserta" class="h-20 w-20 lg:h-24 lg:w-24 2xl:h-28 2xl:w-28 rounded object-contain" />
                  @else
                    <span class="text-gray-400">-</span>
                  @endif
                </td>
                <td class="px-6 py-3 text-sm lg:text-base 2xl:text-lg text-gray-800">{{ optional($time)->setTimezone($tz)->locale('id')->translatedFormat('d F Y H:i') }} WIB</td>
              </tr>
            @endforeach
          </tbody>
          </table>
        </div>
      </div>
      
    @endif
  </div>
</section>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const pageSize = 5; // jumlah baris per tampilan
    let startIndex = 0;

      // Siapkan transition untuk animasi
      rows.forEach((row) => {
        row.style.transition = 'transform 450ms ease, opacity 450ms ease';
        row.style.willChange = 'transform, opacity';
      });

      function showSlice(start) {
        rows.forEach((row, idx) => {
          const visible = idx >= start && idx < start + pageSize;
          row.style.display = visible ? '' : 'none';
          if (visible) {
            row.style.opacity = '1';
            row.style.transform = 'translateX(0)';
          }
        });
      }

    // Tampilkan awal
    showSlice(startIndex);

      if (rows.length > pageSize) {
        setInterval(() => {
          // batch saat ini
          const currentStart = startIndex;
          const currentEnd = Math.min(currentStart + pageSize, rows.length);
          const currentRows = rows.slice(currentStart, currentEnd);

          // hitung batch berikutnya
          startIndex += pageSize;
          if (startIndex >= rows.length) {
            startIndex = 0; // kembali ke awal
          }

          // animasi keluar ke kanan
          currentRows.forEach((row) => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(40px)';
          });

          // setelah keluar, tampilkan batch berikut masuk dari kiri
          setTimeout(() => {
            currentRows.forEach((row) => {
              row.style.display = 'none';
            });

            const nextStart = startIndex;
            const nextEnd = Math.min(nextStart + pageSize, rows.length);
            const nextRows = rows.slice(nextStart, nextEnd);
            nextRows.forEach((row) => {
              row.style.display = '';
              row.style.opacity = '0';
              row.style.transform = 'translateX(-40px)';
            });

            requestAnimationFrame(() => {
              nextRows.forEach((row) => {
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
              });
            });
          }, 480); // ~durasi animasi keluar
        }, 5000); // ganti setiap 5 detik
      }
  });
</script>
@endsection