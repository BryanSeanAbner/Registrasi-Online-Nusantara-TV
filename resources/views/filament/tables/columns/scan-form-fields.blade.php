@php
    /** @var \App\Models\Scan $record */
    $record = $getRecord();
    $registration = $record->registration;

    $items = collect();
    if ($registration) {
        $items = $registration->fieldValues
            ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $registration->event_id)
            ->map(function ($fv) {
                $label = $fv->field->label ?? \Illuminate\Support\Str::title(str_replace('_', ' ', (string)($fv->field->name ?? '')));
                $value = (string) ($fv->value ?? '');

                // Format ringan: link untuk URL, tel untuk nomor.
                $formatted = $value;
                if (preg_match('~^https?://~i', $value)) {
                    $formatted = '<a href="' . e($value) . '" target="_blank" rel="noopener">' . e($value) . '</a>';
                } elseif (preg_match('~^\+?\d[\d\s\-]{7,}$~', $value)) {
                    $tel = preg_replace('~\s+~', '', $value);
                    $formatted = '<a href="tel:' . e($tel) . '">' . e($value) . '</a>';
                }

                return [
                    'label' => $label,
                    'value' => $value,
                    'html'  => $formatted,
                ];
            })
            ->values();
    }

    $chips = $items->take(3);
    $extra = $items->skip(3);
@endphp

@if ($items->isEmpty())
  <span class="rf-badge">Tidak ada jawaban</span>
@else
  <div class="sp-kvlist">
    @foreach ($chips as $item)
      <span class="sp-kv">
        <span class="label">{{ $item['label'] }}:</span>
        <span class="value">{!! $item['html'] !!}</span>
      </span>
    @endforeach

    @if ($extra->isNotEmpty())
      @php
        $rest = $extra->map(fn ($i) => $i['label'] . ': ' . $i['value'])->implode("\n");
      @endphp
      <span class="sp-kv" title="{{ $rest }}">
        <span class="label">Lainnya</span>
        <span class="value">+{{ $extra->count() }}</span>
      </span>
    @endif
  </div>
@endif
