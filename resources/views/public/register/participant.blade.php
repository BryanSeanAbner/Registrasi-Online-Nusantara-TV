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
      <span id="total-count" class="text-sm font-medium text-gray-900">Total: {{ $total ?? ($registrations->count() ?? 0) }}</span>
    </div>

    @php
      $list = $registrations ?? collect();

      $fields = ($list->isNotEmpty()
          ? $list
              ->flatMap(function ($reg) {
                  return $reg->values
                      ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $reg->event_id)
                      ->map(fn ($fv) => $fv->field);
              })
              ->unique(fn ($field) => $field->id)
          : ($event->formFields ?? collect())
      )
      ->map(function ($field) {
          $label = $field->label ?: \Illuminate\Support\Str::title(str_replace('_', ' ', (string)($field->name ?? '')));
          return [
              'id'    => $field->id,
              'label' => $label,
              'type'  => $field->type ?? null,
          ];
      })
      ->values();

      $formatValue = function (array $field, $value) {
          $value = (string) ($value ?? '');
          if (($field['type'] ?? null) === 'image' && $value !== '') {
              $path = ltrim($value, '/');
              return '<a href="' . e(route('event.image', $path)) . '" target="_blank" rel="noopener">View Foto</a>';
          }
          return e($value !== '' ? $value : '-');
      };
    @endphp
    <div class="overflow-x-auto">
      <div class="min-w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">#</th>
            @foreach ($fields as $f)
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600" data-field-id="{{ $f['id'] }}" data-type="{{ $f['type'] ?? '' }}">{{ $f['label'] }}</th>
            @endforeach
            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Check-in</th>
          </tr>
        </thead>
        <tbody id="participants-tbody" class="divide-y divide-gray-100 bg-white">
            @foreach ($list as $reg)
              @php
                  $record = $reg;
                  $indexed = $record->values
                      ->filter(fn ($fv) => $fv->field && $fv->field->event_id === $record->event_id)
                      ->keyBy(fn ($fv) => $fv->field->id);
              @endphp
              <tr class="hover:bg-gray-50" data-reg-id="{{ $reg->id }}" data-code="{{ $reg->code }}">
                <td class="px-6 py-3 text-sm text-gray-700">{{ $loop->iteration }}</td>
                @foreach ($fields as $f)
                    @php $fv = $indexed->get($f['id']); @endphp
                    <td class="px-6 py-3 text-sm text-gray-500">{!! $formatValue($f, $fv->value ?? null) !!}</td>
                @endforeach
                <td class="px-6 py-3 text-sm text-gray-700 checkin-cell" id="ci-{{ $reg->id }}">{{ optional($reg->checked_in_at)->format('d M Y H:i') ?? 'Belum' }}</td>
              </tr>
            @endforeach
        </tbody>
        </table>
      </div>
    </div>
    @if($list->isEmpty())
      <p id="empty-message" class="mt-3 text-gray-600 text-center">Belum ada peserta terdaftar.</p>
    @endif
  </div>
</section>
@endsection
@push('scripts')
  <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
  <script>
    (function() {
      try {
        Pusher.logToConsole = true;
        var key = "{{ env('PUSHER_APP_KEY') }}";
        var cluster = "{{ env('PUSHER_APP_CLUSTER', 'ap1') }}";
        if (!key) return;

        var pusher = new Pusher(key, {
          cluster: cluster,
          forceTLS: true,
        });

        function onCheckin(data) {
          try {
            if (!data || !data.registration_id) return;
            if (data.event_id && Number(data.event_id) !== Number({{ $event->id ?? 0 }})) return; // ignore other events

            var cell = document.getElementById('ci-' + data.registration_id);
            if (cell) {
              var t = data.checked_in_at ? new Date(data.checked_in_at) : null;
              cell.textContent = t ? formatDate(t) : 'Sudah';
              var row = cell.closest('tr');
              if (row) row.classList.add('bg-green-50');
              renumberRows();
              return;
            }

            fetch('/events/{{ $event->id ?? 0 }}/registrations/' + encodeURIComponent(data.registration_id) + '.json', {
              headers: { 'Accept': 'application/json' }
            })
            .then(function (res) { return res.ok ? res.json() : Promise.reject(res); })
            .then(function (json) {
              try {
                if (document.getElementById('ci-' + json.registration_id)) return;
                insertRow(json);
                renumberRows();
                incTotal();
              } catch (e) { console.warn('Insert row failed', e); }
            })
            .catch(function (err) { console.warn('Fetch row failed', err); });
          } catch (e) {
            console.warn('Update row failed', e);
          }
        }

        function formatDate(d) {
          var dd = String(d.getDate()).padStart(2, '0');
          var mmm = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][d.getMonth()];
          var yyyy = d.getFullYear();
          var hh = String(d.getHours()).padStart(2, '0');
          var mm = String(d.getMinutes()).padStart(2, '0');
          return dd + ' ' + mmm + ' ' + yyyy + ' ' + hh + ':' + mm;
        }

        function renumberRows() {
          try {
            var tbody = document.getElementById('participants-tbody');
            if (!tbody) return;
            Array.from(tbody.querySelectorAll('tr')).forEach(function (tr, idx) {
              var first = tr.querySelector('td');
              if (first) first.textContent = String(idx + 1);
            });
          } catch (_) { /* ignore */ }
        }

        function insertRow(json) {
          var tbody = document.getElementById('participants-tbody');
          if (!tbody) return;
          var emptyMsg = document.getElementById('empty-message');
          if (emptyMsg) emptyMsg.remove();

          var headerFields = Array.from(document.querySelectorAll('thead th[data-field-id]'))
            .map(function (th) { return { id: Number(th.getAttribute('data-field-id')), type: th.getAttribute('data-type') || '' }; });
          var valueMap = {};
          (json.values || []).forEach(function (v) { valueMap[Number(v.field_id)] = { value: v.value || '', type: v.type || '' }; });

          var tr = document.createElement('tr');
          tr.className = 'hover:bg-gray-50 bg-green-50';
          tr.setAttribute('data-reg-id', json.registration_id);
          tr.setAttribute('data-code', json.code || '');

          var tdNo = document.createElement('td');
          tdNo.className = 'px-6 py-3 text-sm text-gray-700';
          tdNo.textContent = '';
          tr.appendChild(tdNo);

          headerFields.forEach(function (hf) {
            var td = document.createElement('td');
            td.className = 'px-6 py-3 text-sm text-gray-500';
            var item = valueMap[hf.id] || { value: '', type: hf.type };
            var val = (item.value || '').toString();
            if ((item.type || '').toLowerCase() === 'image' && val) {
              var a = document.createElement('a');
              var path = val.replace(/^\/+/, '');
              a.href = '/images/' + path;
              a.target = '_blank';
              a.rel = 'noopener';
              a.textContent = 'View Foto';
              td.appendChild(a);
            } else {
              td.textContent = val !== '' ? val : '-';
            }
            tr.appendChild(td);
          });

          var tdCi = document.createElement('td');
          tdCi.className = 'px-6 py-3 text-sm text-gray-700 checkin-cell';
          tdCi.id = 'ci-' + json.registration_id;
          var t = json.checked_in_at ? new Date(json.checked_in_at) : null;
          tdCi.textContent = t ? formatDate(t) : 'Sudah';
          tr.appendChild(tdCi);

          if (tbody.firstElementChild) {
            tbody.insertBefore(tr, tbody.firstElementChild);
          } else {
            tbody.appendChild(tr);
          }
        }

        function incTotal() {
          var el = document.getElementById('total-count');
          if (!el) return;
          var m = /Total:\s*(\d+)/.exec(el.textContent || '');
          if (!m) return;
          var n = parseInt(m[1], 10);
          if (!isNaN(n)) el.textContent = 'Total: ' + (n + 1);
        }

        var chEvent = pusher.subscribe('registrations.{{ $event->id ?? 0 }}');
        chEvent.bind('RegistrationCheckedIn', onCheckin);
      } catch (e) {
        console.warn('Pusher init failed', e);
      }
    })();
  </script>
@endpush
