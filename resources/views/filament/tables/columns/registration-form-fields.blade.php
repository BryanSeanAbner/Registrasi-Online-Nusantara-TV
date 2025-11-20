@php
    /** @var \App\Models\Registration $record */
    $record = $getRecord();
@endphp

@include('filament.registrations.components.form-answers', [
    'registration' => $record,
])
