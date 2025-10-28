@php
/** @var \App\Models\Registration $record */
    $record = $getRecord();
    $items = $record->fieldValues
        ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $record->event_id)
        ->map(function ($fv) {
            $label = $fv->field->label ?: \Illuminate\Support\Str::title(str_replace('_', ' ', (string)($fv->field->name ?? '')));
            $value = (string) ($fv->value ?? '');

            if (($fv->field->type ?? null) === 'image' && $value !== '') {
                $path = ltrim($value, '/');
                $html = '<a href="' . e(route('event.image', $path)) . '" target="_blank" rel="noopener">View Foto</a>';
            } else {
                $html = e($value ?: '—');
            }

            return [
                'label' => $label,
                'value' => $value,
                'html'  => $html,
            ];
        })
        ->values();
@endphp

@if ($items->isEmpty())
  <span class="rf-badge">Tidak ada jawaban</span>
@else
  <dl class="rf-fieldlist">
    @foreach ($items as $item)
      <dt>{{ $item['label'] }}</dt>
      <dd>{!! $item['html'] !!}</dd>
    @endforeach
  </dl>
@endif
