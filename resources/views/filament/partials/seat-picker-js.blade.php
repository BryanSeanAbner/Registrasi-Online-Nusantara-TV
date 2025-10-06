<script>
// =============== Seat Picker (vanilla) ===============
(function () {
  // Helper log agar terlihat di console kalau file ini termuat
  console.debug('[seat-picker] JS loaded');

  function buildSeatPicker(el) {
    if (!el || el.__spInit) return; // hindari double init
    el.__spInit = true;

    const eventId = el.getAttribute('data-event-id');
    const registrationId = el.getAttribute('data-registration-id');
    const loadingEl = el.querySelector('#sp-loading');
    const errorEl   = el.querySelector('#sp-error');
    const contentEl = el.querySelector('#sp-content');

    const state = { seats: [], grouped: {} };

    const show = (node) => node && (node.style.display = '');
    const hide = (node) => node && (node.style.display = 'none');

    function groupSeats() {
      const g = {};
      for (const s of state.seats) (g[s.section ?? ''] ??= []).push(s);
      for (const k of Object.keys(g)) {
        g[k].sort((a,b)=>{
          if (a.row === b.row) return (a.col ?? 0) - (b.col ?? 0);
          return (''+(a.row??'')).localeCompare((''+(b.row??'')), 'en', {numeric:true});
        });
      }
      state.grouped = g;
    }

    function maxCols(section) {
      const arr = state.grouped[section] || [];
      return Math.max(1, ...arr.map(s => s.col ?? 1));
    }

    function seatStateClass(seat) {
      if (seat.taken) return 'is-taken';
      if (seat.status !== 'available') return 'is-blocked';
      return 'is-available';
    }

    function render() {
      if (!contentEl) return;
      contentEl.innerHTML = '';
      for (const section of Object.keys(state.grouped)) {
        const box = document.createElement('div');
        box.className = 'sp-section';

        const title = document.createElement('div');
        title.className = 'fi-ta-text';
        title.textContent = `Section: ${section || 'Default'}`;
        box.appendChild(title);

        const grid = document.createElement('div');
        grid.className = 'sp-grid';
        grid.style.gridTemplateColumns = `repeat(${maxCols(section)}, minmax(28px, 1fr))`;

        for (const seat of state.grouped[section]) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = `sp-seat ${seatStateClass(seat)}`;
          btn.textContent = seat.label;
          btn.disabled = !!seat.taken || seat.status !== 'available';
          btn.addEventListener('click', () => handleSelect(seat));
          grid.appendChild(btn);
        }

        box.appendChild(grid);
        contentEl.appendChild(box);
      }
    }

    async function fetchSeats() {
      hide(errorEl); show(loadingEl);
      try {
        const res = await fetch(`{{ url('/events') }}/${eventId}/seats/map`, {
          headers: { 'Accept': 'application/json' },
          credentials: 'same-origin',
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        state.seats = json.data ?? [];
        groupSeats();
        render();
        show(contentEl);
      } catch (e) {
        if (errorEl) errorEl.textContent = 'Gagal memuat peta kursi.';
        show(errorEl); hide(contentEl);
        console.error('[seat-picker] fetchSeats error:', e);
      } finally { hide(loadingEl); }
    }

    async function handleSelect(seat) {
      if (seat.taken || seat.status !== 'available') return;
      if (!confirm(`Tetapkan kursi ${seat.label}?`)) return;

      try {
        const res = await fetch(`{{ url('/events') }}/${eventId}/seats/assign`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
          body: JSON.stringify({ seat_id: seat.id, registration_id: registrationId }),
        });
        if (!res.ok) {
          const data = await res.json().catch(()=>({}));
          throw new Error(data.message || `HTTP ${res.status}`);
        }
        alert('Berhasil menugaskan kursi.');
        window.dispatchEvent(new CustomEvent('close-modal'));
        window.location.reload();
      } catch (e) {
        alert(e.message || 'Gagal menugaskan kursi.');
        fetchSeats();
      }
    }

    // mulai
    fetchSeats();
  }

  // ====== Auto-init saat elemen muncul (modal dibuka) ======
  function initExisting() {
    document.querySelectorAll('#seat-picker:not([data-sp-bound])').forEach(el => {
      el.setAttribute('data-sp-bound', '1');
      buildSeatPicker(el);
    });
  }

  // 1) Saat dokumen siap & setiap Livewire navigate
  document.addEventListener('DOMContentLoaded', initExisting);
  window.addEventListener('livewire:navigated', initExisting);

  // 2) Saat modal Filament dibuka (event global)
  window.addEventListener('filament:modal.opened', initExisting);

  // 3) Fallback: MutationObserver pantau node baru (robust)
  const mo = new MutationObserver(() => initExisting());
  mo.observe(document.documentElement, { childList: true, subtree: true });

})();
</script>
