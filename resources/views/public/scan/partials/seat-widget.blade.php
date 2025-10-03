<div class="mt-4 space-y-3">
  <div class="flex gap-3 items-center text-xs">
    <span class="inline-block w-3 h-3 rounded bg-green-600"></span> Tersedia
    <span class="inline-block w-3 h-3 rounded bg-gray-400"></span> Terisi
    <span class="inline-block w-3 h-3 rounded bg-amber-500"></span> Diblok
  </div>

  <div id="seat-status" class="text-sm text-gray-500">Memuat peta kursi…</div>
  <div id="seat-sections" class="space-y-3"></div>
</div>

<script>
(function(){
  const EVENT_ID = {{ $eventId }};
  const REGISTRATION_ID = {{ $registrationId }};
  const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
  const panel = document.getElementById('seat-widget');
  const statusEl = document.getElementById('seat-status');
  const sectionsEl = document.getElementById('seat-sections');

  if (!panel || !statusEl || !sectionsEl) return;

  function bySection(seats){
    const groups = {};
    seats.forEach(s => {
      const key = s.section || '';
      (groups[key] ||= []).push(s);
    });
    Object.keys(groups).forEach(k=>{
      groups[k].sort((a,b)=>{
        if (a.row === b.row) return (a.col || 0) - (b.col || 0);
        return String(a.row || '').localeCompare(String(b.row || ''), 'en', { numeric: true });
      });
    });
    return groups;
  }
  function seatBtnClass(seat){
    if (seat.taken) return 'bg-gray-400 text-white cursor-not-allowed';
    if (seat.status !== 'available') return 'bg-amber-500 text-white cursor-not-allowed';
    return 'bg-green-600 hover:bg-green-700 text-white';
  }

  async function loadSeats(){
    statusEl.textContent = 'Memuat peta kursi…';
    sectionsEl.innerHTML = '';
    try {
      const res = await fetch(`{{ url('/events') }}/${EVENT_ID}/seats/map`, { headers: { 'Accept': 'application/json' } });
      const json = await res.json();
      const seats = json.data || [];

      const groups = bySection(seats);
      statusEl.textContent = '';

      if (Object.keys(groups).length === 0) {
        statusEl.textContent = 'Tidak ada data kursi.';
        return;
      }

      Object.entries(groups).forEach(([section, group])=>{
        const wrap = document.createElement('div');
        wrap.className = 'border rounded-xl p-3';

        const header = document.createElement('div');
        header.className = 'font-semibold mb-2';
        header.textContent = 'Section: ' + (section || 'Default');
        wrap.appendChild(header);

        const maxCols = Math.max(1, ...group.map(s => s.col || 1));
        const grid = document.createElement('div');
        grid.className = 'grid gap-1';
        grid.style.gridTemplateColumns = `repeat(${maxCols}, minmax(30px, 1fr))`;

        group.forEach(seat=>{
          const btn = document.createElement('button');
          btn.className = `text-[11px] px-2 py-1 rounded ${seatBtnClass(seat)}`;
          btn.textContent = seat.label;
          btn.title = seat.label;
          btn.disabled = seat.taken || seat.status !== 'available';

          btn.addEventListener('click', () => assignSeat(seat, btn));

          grid.appendChild(btn);
        });

        wrap.appendChild(grid);
        sectionsEl.appendChild(wrap);
      });
    } catch (e) {
      statusEl.textContent = 'Gagal memuat peta kursi.';
    }
  }

  let busy = false;
  async function assignSeat(seat, btn){
    if (busy || btn.disabled) return;
    if (!confirm(`Tetapkan kursi ${seat.label}?`)) return;

    busy = true;
    try {
      const res = await fetch(`{{ url('/events') }}/${EVENT_ID}/seats/assign`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ seat_id: seat.id, registration_id: REGISTRATION_ID }),
      });

      if (res.ok) {
        alert('Kursi berhasil ditetapkan.');
        const label = document.getElementById('seat-label');
        if (label) label.textContent = seat.label;
        panel.classList.add('hidden');
      } else {
        const data = await res.json().catch(()=>({message:'Gagal'}));
        alert(data.message || 'Gagal menugaskan kursi.');
        await loadSeats();
      }
    } catch (e) {
      alert('Tidak bisa terhubung ke server.');
    } finally {
      busy = false;
    }
  }

  loadSeats();

  window.refreshSeatWidget = loadSeats;
})();
</script>