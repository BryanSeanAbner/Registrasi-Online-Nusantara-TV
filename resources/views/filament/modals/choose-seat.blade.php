<div
    x-data="seatPicker({ eventId: {{ $eventId }}, registrationId: {{ $registrationId }} })"
    x-init="init()"
    class="space-y-3"
>
    <template x-if="loading">
        <div class="text-sm text-gray-500">Memuat peta kursi…</div>
    </template>

    <div class="flex gap-2 items-center">
        <span class="inline-block w-3 h-3 bg-green-500"></span><span class="text-xs">Tersedia</span>
        <span class="inline-block w-3 h-3 bg-gray-400"></span><span class="text-xs">Terisi</span>
        <span class="inline-block w-3 h-3 bg-amber-500"></span><span class="text-xs">Diblok</span>
    </div>

    <template x-for="(group, section) in grouped" :key="section">
        <div class="border rounded p-3">
            <div class="font-semibold mb-2">Section: <span x-text="section || 'Default'"></span></div>
            <div class="grid"
                 :style="`grid-template-columns: repeat(${maxCols(section)}, minmax(28px, 1fr)); gap:6px;`">
                <template x-for="seat in group" :key="seat.id">
                    <button
                        class="text-xs rounded py-1"
                        :class="btnClass(seat)"
                        x-text="seat.label"
                        @click="select(seat)"
                        :disabled="seat.taken || seat.status !== 'available'"
                    ></button>
                </template>
            </div>
        </div>
    </template>

    <script>
        function seatPicker({eventId, registrationId}) {
            return {
                loading: true,
                seats: [],
                grouped: {},
                init() {
                    this.fetchSeats();
                },
                async fetchSeats() {
                    this.loading = true;
                    const res = await fetch(`{{ url('/events') }}/${eventId}/seats/map`);
                    const json = await res.json();
                    this.seats = json.data ?? [];
                    this.grouped = this.seats.reduce((acc, s) => {
                        (acc[s.section ?? ''] ??= []).push(s); return acc;
                    }, {});
                    // urutkan per row/col biar rapi
                    Object.keys(this.grouped).forEach(k=>{
                        this.grouped[k].sort((a,b)=>{
                            if (a.row === b.row) return (a.col ?? 0) - (b.col ?? 0);
                            return (''+(a.row??'')).localeCompare((''+(b.row??'')), 'en', {numeric:true});
                        });
                    });
                    this.loading = false;
                },
                maxCols(section) {
                    return Math.max(1, ...this.grouped[section].map(s => s.col ?? 1));
                },
                btnClass(seat) {
                    if (seat.taken) return 'bg-gray-400 text-white cursor-not-allowed';
                    if (seat.status !== 'available') return 'bg-amber-500 text-white cursor-not-allowed';
                    return 'bg-green-500/90 hover:bg-green-600 text-white';
                },
                async select(seat) {
                    if (seat.taken || seat.status !== 'available') return;
                    if (!confirm(`Tetapkan kursi ${seat.label}?`)) return;

                    const res = await fetch(`{{ url('/events') }}/${eventId}/seats/assign`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ seat_id: seat.id, registration_id: registrationId })
                    });

                    if (res.ok) {
                        alert('Berhasil menugaskan kursi.');
                        // Tutup modal Filament via event
                        window.dispatchEvent(new CustomEvent('close-modal'));
                        // Optional: refresh tabel
                        window.location.reload();
                    } else {
                        const data = await res.json().catch(()=>({message:'Gagal'}));
                        alert(data.message || 'Gagal menugaskan kursi.');
                        this.fetchSeats(); // refresh status
                    }
                },
            }
        }
    </script>
</div>
