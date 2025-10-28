@extends('public.shared.layout')

@section('content')
<section class="px-4 py-12">
  <div class="mx-auto w-full max-w-7xl 2xl:max-w-screen-2xl">
    <div class="mb-6 flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Peserta Event</h1>
        @isset($event)
          <p class="mt-1 text-sm text-gray-600">{{ $event->title }}</p>
        @endisset
      </div>
      <span class="text-sm font-medium text-gray-900">Total: {{ $total ?? ($registrations->count() ?? 0) }}</span>
    </div>

    @php
      $list = $registrations ?? collect();

      // Kumpulkan definisi field untuk header (unik per field id)
      $fields = $list
          ->flatMap(function ($reg) {
              return $reg->values
                  ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $reg->event_id)
                  ->map(fn ($fv) => $fv->field);
          })
          ->unique(fn ($field) => $field->id)
          ->map(function ($field) {
              $label = $field->label ?: \Illuminate\Support\Str::title(str_replace('_', ' ', (string)($field->name ?? '')));
              return [
                  'id'    => $field->id,
                  'label' => $label,
                  'type'  => $field->type ?? null,
              ];
          })
          ->values();

      // Formatter nilai per tipe field
      $formatValue = function (array $field, $value) {
          $value = (string) ($value ?? '');
          if (($field['type'] ?? null) === 'image' && $value !== '') {
              $path = ltrim($value, '/');
              return '<a href="' . e(route('event.image', $path)) . '" target="_blank" rel="noopener">View Foto</a>';
          }
          return e($value !== '' ? $value : '-');
      };
    @endphp

    @if($list->isEmpty())
      <p class="text-gray-600">Belum ada peserta terdaftar.</p>
    @else
      <!-- Tabel responsif -->
      <div class="overflow-x-auto">
        <div class="min-w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
          <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">#</th>
              @foreach ($fields as $f)
                  <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">{{ $f['label'] }}</th>
              @endforeach
              <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">check in</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr class="hover:bg-gray-50">
              @foreach ($list as $reg)
                @php
                    $record = $reg;
                    $indexed = $record->values
                        ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $record->event_id)
                        ->keyBy(fn ($fv) => $fv->field->id);
                @endphp
                  <td class="px-6 py-3 text-sm text-gray-700">{{ $loop->iteration }}</td>
                  @foreach ($fields as $f)
                      @php $fv = $indexed->get($f['id']); @endphp
                      <td class="px-6 py-3 text-sm text-gray-500">{!! $formatValue($f, $fv->value ?? null) !!}</td>
                  @endforeach
                  <td class="px-6 py-3 text-sm text-gray-500">{{ $reg->checked_in_at}}</td>
              @endforeach
            </tr>
          </tbody>
          </table>
        </div>
      </div>
      
    @endif
  </div>
</section>
@endsection

