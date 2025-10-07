@php
/** @var \App\Models\Registration $record */
    $record = $getRecord();
    $items = $record->fieldValues
        ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $record->event_id)
        ->map(fn ($fv) => [
            'label' => $fv->field->label ?: \Illuminate\Support\Str::title(str_replace('_', ' ', $fv->formField->slug)),
            'value' => $fv->value,
        ])
        ->values();
@endphp

@if ($items->isEmpty())
  <span class="rf-badge">Tidak ada jawaban</span>
@else
  <dl class="rf-fieldlist">
    @foreach ($items as $item)
      <dt>{{ $item['label'] }}</dt>
      <dd>{{ $item['value'] ?: '—' }}</dd>
    @endforeach
  </dl>
@endif
