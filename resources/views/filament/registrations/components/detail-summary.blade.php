@props([
    'registration',
    'participantName' => null,
    'participantPhone' => null,
])

@php
    /** @var \App\Models\Registration $registration */
    $event = $registration->event;
    $seat = $registration->seatAssignment?->seat;

    $statusColors = [
        \App\Models\Registration::ST_APPROVED => 'rf-status-success',
        \App\Models\Registration::ST_REJECTED => 'rf-status-danger',
        \App\Models\Registration::ST_PENDING => 'rf-status-warning',
    ];

    $statusBadgeClass = $statusColors[$registration->status] ?? 'rf-status-muted';
@endphp

<div class="rf-summary-stack">
    <div class="rf-panel">
        <p class="rf-panel-title">Informasi Peserta</p>
        <div class="rf-summary-grid">
            <dl class="rf-summary-card">
                <dt>Nama Peserta</dt>
                <dd>{{ $participantName ?? 'Tidak diketahui' }}</dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Nomor WhatsApp</dt>
                <dd>{{ $participantPhone ?? 'Tidak diketahui' }}</dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Status</dt>
                <dd>
                    <span class="rf-status-badge {{ $statusBadgeClass }}">
                        {{ \Illuminate\Support\Str::headline($registration->status) }}
                    </span>
                </dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Kode Tiket</dt>
                <dd>{{ $registration->code ?? 'Belum tersedia' }}</dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Approved By</dt>
                <dd>{{ $registration->approver?->name ?? '—' }}</dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>ID Registrasi</dt>
                <dd>#{{ $registration->id }}</dd>
            </dl>
        </div>
    </div>

    <div class="rf-panel">
        <p class="rf-panel-title">Event &amp; Kehadiran</p>
        <div class="rf-summary-grid">
            <dl class="rf-summary-card">
                <dt>Event</dt>
                <dd>{{ $event->title ?? '—' }}</dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Seat</dt>
                <dd>{{ $seat?->label ?? 'Belum dipilih' }}</dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Check-in</dt>
                <dd>
                    @if ($registration->checked_in_at)
                        {{ $registration->checked_in_at->format('d M Y H:i') }}
                    @else
                        Belum check-in
                    @endif
                </dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Dibuat</dt>
                <dd>{{ optional($registration->created_at)?->format('d M Y H:i') ?? '—' }}</dd>
            </dl>
            <dl class="rf-summary-card">
                <dt>Diupdate</dt>
                <dd>{{ optional($registration->updated_at)?->format('d M Y H:i') ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
