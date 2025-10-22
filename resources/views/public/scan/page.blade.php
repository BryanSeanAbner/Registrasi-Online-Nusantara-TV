@extends('public.shared.layout')

@section('content')
<section class="px-4 py-12">
  <div class="mx-auto w-full max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
    <h1 class="mb-4 text-2xl font-bold text-gray-900">📷 Scan Check-in</h1>

    {{-- <div id="reader" class="overflow-hidden rounded-md border border-gray-300"></div> --}}

    <div class="mt-4">
      <label for="code" class="mb-1 block text-sm font-medium text-gray-700">Atau scan / ketik kode tiket:</label>
      <input id="code" autofocus class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Scan atau ketik kode lalu Enter" autocomplete="off">
    </div>

    <pre id="result" class="mt-4 whitespace-pre-wrap rounded-md bg-gray-50 p-3 text-sm text-gray-800"></pre>
  </div>

  <div id="detail" class="mx-auto mt-6 w-full max-w-xl"></div>
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const result = document.getElementById('result');
  const input  = document.getElementById('code');
  const detail = document.getElementById('detail');
  const csrf   = document.querySelector('meta[name="csrf-token"]').content;

  const ajax = async (method, url, data) => {
    const opt = { method, headers: { 'Accept':'application/json' } };
    if (method === 'POST') {
      opt.headers['Content-Type'] = 'application/json';
      opt.headers['X-CSRF-TOKEN'] = csrf;
      opt.body = JSON.stringify(data || {});
    }
    const r = await fetch(url, opt);
    const j = await r.json().catch(() => ({}));
    return { ok: r.ok, status: r.status, data: j };
  };

  const loadDetailFragment = async code => {
    const { ok, data } = await ajax('GET', `{{ route('scan.fragment', ':code') }}`.replace(':code', encodeURIComponent(code)));
    detail.innerHTML = ok && data.html ? data.html : `<div class="text-red-600">Gagal memuat detail.</div>`;
    initSeatWidgetIfPresent();
  };

  const submitCode = async code => {
    if (!code) return;
    result.textContent = '⏳ Memeriksa...';
    detail.innerHTML = '';

    const { ok, data } = await ajax('POST', `{{ route('scan.submit') }}`, { code });
    result.textContent = `${ok ? '✅' : '❌'} ${data?.msg ?? 'Gagal.'}`;
    result.className = `mt-4 whitespace-pre-wrap rounded-md p-3 text-sm ${
      ok ? 'bg-green-50 text-green-700 border border-green-200'
         : 'bg-red-50 text-red-700 border border-red-200'
    }`;

    if (ok && data.code) {
      
      try { html5QRCodeScanner.clear(); } catch {}
      await loadDetailFragment(data.code);
      document.getElementById('seat-widget')?.classList.remove('hidden');
    }
  };

  const urlParams = new URLSearchParams(location.search);
  const initial = urlParams.get('q');
  if (initial) { input.value = initial; submitCode(initial); }

  // hardware scanner (CR/LF)
  input.addEventListener('change', e => {
    const code = e.target.value.trim();
    submitCode(code);
    e.target.value = '';
  });

  // window.html5QRCodeScanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: { width: 300, height: 300 } });
  // html5QRCodeScanner.render(decodedText => {
  //   input.value = decodedText;
  //   submitCode(decodedText);
  // });

  const initSeatWidgetIfPresent = () => {
    const mount = document.querySelector('[data-seat-widget="1"]');
    if (!mount) return;

    const EVENT_ID = mount.dataset.eventId;
    const REG_ID   = mount.dataset.registrationId;
    const statusEl   = mount.querySelector('[data-seat-status]');
    const sectionsEl = mount.querySelector('[data-seat-sections]');
    const seatLabel  = document.getElementById('seat-label');
    const panelWrap  = document.getElementById('seat-widget');

    const groupBySection = seats => {
      const g = {};
      seats.forEach(s => {
        const key = s.section || s.table || '';
        (g[key] ??= []).push(s);
      });
      Object.keys(g).forEach(k => {
        g[k].sort((a,b) => a.row === b.row ? (a.col ?? 0) - (b.col ?? 0) : String(a.row ?? '').localeCompare(String(b.row ?? ''), 'en', { numeric:true }));
      });
      return g;
    };

    const seatBtnClass = seat => {
      if (seat.taken) return 'bg-gray-400 text-white cursor-not-allowed';
      if (seat.status !== 'available') return 'bg-amber-500 text-white cursor-not-allowed';
      return 'bg-green-600 hover:bg-green-700 text-white';
    };

    const loadSeats = async () => {
      statusEl.textContent = 'Memuat peta kursi…';
      sectionsEl.innerHTML = '';
      const r = await fetch(`{{ url('/events') }}/${EVENT_ID}/seats/map`, { headers: { 'Accept':'application/json' }});
      const j = await r.json().catch(() => ({}));
      const groups = groupBySection(j.data ?? []);
      statusEl.textContent = '';

      if (!Object.keys(groups).length) {
        statusEl.textContent = 'Tidak ada data kursi.';
        return;
      }

      for (const [section, group] of Object.entries(groups)) {
        const wrap = document.createElement('div');
        wrap.className = 'border rounded-xl p-3';

        const header = document.createElement('div');
        header.className = 'font-semibold mb-2';
        header.textContent = `Section: ${section || 'Default'}`;
        wrap.appendChild(header);

        const maxCols = Math.max(1, ...group.map(s => s.col ?? 1));
        const grid = document.createElement('div');
        grid.className = 'grid gap-1';
        grid.style.gridTemplateColumns = `repeat(${maxCols}, minmax(30px, 1fr))`;

        for (const seat of group) {
          const btn = document.createElement('button');
          btn.className = `text-[11px] px-2 py-1 rounded ${seatBtnClass(seat)}`;
          btn.textContent = seat.label;
          btn.disabled = !!seat.taken || seat.status !== 'available';
          btn.addEventListener('click', () => assignSeat(seat));
          grid.appendChild(btn);
        }

        wrap.appendChild(grid);
        sectionsEl.appendChild(wrap);
      }
    };

    const assignSeat = async seat => {
      if (!confirm(`Tetapkan kursi ${seat.label}?`)) return;
      const res = await fetch(`{{ url('/events') }}/${EVENT_ID}/seats/assign`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ seat_id: seat.id, registration_id: REG_ID }),
      });

      if (res.ok) {
        alert('Kursi berhasil ditetapkan.');
        seatLabel && (seatLabel.textContent = seat.label);
        panelWrap && panelWrap.classList.add('hidden');
        document.getElementById('btn-open-seat').classList.add('hidden');
        // detail.innerHTML = '';
      } else {
        const data = await res.json().catch(()=>({message:'Gagal'}));
        alert(data.message || 'Gagal menugaskan kursi.');
        await loadSeats();
      }
    };

    document.getElementById('btn-open-seat')?.addEventListener('click', () => {
      panelWrap?.classList.toggle('hidden');
    });

    loadSeats();
  };
});
</script>
@endpush