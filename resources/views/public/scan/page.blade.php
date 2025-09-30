@extends('public.shared.layout')

@section('content')
  <section class="px-4 py-12">
    <div class="mx-auto w-full max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <h1 class="mb-4 text-2xl font-bold text-gray-900">📷 Scan Check-in</h1>

      {{-- QR Scanner box (kamera) --}}
      <div id="reader" class="overflow-hidden rounded-md border border-gray-300"></div>

      {{-- Input manual / scanner hardware --}}
      <div class="mt-4">
        <label for="code" class="mb-1 block text-sm font-medium text-gray-700">
          Atau scan / ketik kode tiket:
        </label>
        <input id="code" autofocus
               class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
               placeholder="Scan atau ketik kode lalu Enter">
      </div>

      {{-- Hasil scan --}}
      <pre id="result" class="mt-4 whitespace-pre-wrap rounded-md bg-gray-50 p-3 text-sm text-gray-800"></pre>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="https://unpkg.com/html5-qrcode" defer></script>
  <script>
  document.addEventListener('DOMContentLoaded', () => { 
      const urlParams = new URLSearchParams(window.location.search);
      const myParam = urlParams.get('q');
      const result = document.getElementById('result');
      const input = document.getElementById('code');

      const submit = async (code) => {
          if (!code) return;
          result.textContent = "⏳ Memeriksa...";

          const r = await fetch("{{ route('scan.submit') }}", {
              method: "POST",
              headers: {
                  "Content-Type": "application/json",
                  "X-CSRF-TOKEN": "{{ csrf_token() }}"
              },
              body: JSON.stringify({ code })
          });

          const j = await r.json();
          result.textContent = (j.ok ? "✅ " : "❌ ") + j.msg;
          result.className = "mt-4 whitespace-pre-wrap rounded-md p-3 text-sm " +
              (j.ok
                ? "bg-green-50 text-green-700 border border-green-200"
                : "bg-red-50 text-red-700 border border-red-200");
      };

      // kalau ada param q di URL → auto submit
      if (myParam) {
          input.value = myParam;
          submit(myParam);
      }

      // hardware scanner (CR/LF mode) → otomatis submit setiap kali Enter
      input.addEventListener('change', e => {
          const code = e.target.value.trim();
          submit(code);
          e.target.value = ""; // kosongkan supaya siap scan berikutnya
      });

      // kamera (html5-qrcode)
      let html5QRCodeScanner = new Html5QrcodeScanner(
          "reader", { fps: 10, qrbox: { width: 300, height: 300 } }
      );

      function onScanSuccess(decodedText, decodedResult) {
          input.value = decodedText;
          submit(decodedText);
          html5QRCodeScanner.clear();
      }

      html5QRCodeScanner.render(onScanSuccess);
  });
  </script>
@endpush
