@props([
    'registration',
    'participantName' => null,
    'participantPhone' => null,
])

<div class="rf-modal-stack">
    @if (! empty($detailActions ?? []))
        <div class="rf-action-bar">
            @foreach ($detailActions as $action)
                {{ $action }}
            @endforeach
        </div>
    @endif

    <div class="rf-panel">
        <p class="rf-panel-title">Jawaban Form</p>
        @include('filament.registrations.components.form-answers', [
            'registration' => $registration,
        ])
    </div>
    
    @include('filament.registrations.components.detail-summary', [
        'registration' => $registration,
        'participantName' => $participantName,
        'participantPhone' => $participantPhone,
    ])
</div>
