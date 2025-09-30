@extends('public.shared.layout')

@section('content')
  <section class="px-4 py-12">
    <div class="mx-auto w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <h1 class="mb-8 text-2xl font-bold text-gray-900">Daftar: {{ $event->title }}</h1>

      <form method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @foreach($fields as $field)
          <div>
            <label for="{{ $field->name }}" class="mb-2 block text-sm font-medium text-gray-700">
              {{ $field->label }}
              @if(!$field->is_required)
                <span class="text-gray-400">(opsional)</span>
              @endif
            </label>

            @switch($field->type)
              @case('text')
              @case('email')
              @case('numeric')
              @case('date')
              @case('datetime')
              @case('phone')
              @case('url')
                <input id="{{ $field->name }}"
                       name="{{ $field->name }}"
                       type="{{ $field->type === 'numeric' ? 'number' : ($field->type === 'phone' ? 'text' : $field->type) }}"
                       @required($field->is_required)
                       class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                @break

              @case('textarea')
                <textarea id="{{ $field->name }}"
                          name="{{ $field->name }}"
                          rows="3"
                          @required($field->is_required)
                          class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                @break

              @case('select')
                <select id="{{ $field->name }}"
                        name="{{ $field->name }}"
                        @required($field->is_required)
                        class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500">
                  <option value="">-- pilih --</option>
                  @foreach(($field->meta['options'] ?? []) as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                  @endforeach
                </select>
                @break

              @case('checkbox')
                @foreach(($field->meta['options'] ?? []) as $opt)
                  <label class="flex items-center space-x-2">
                    <input type="checkbox" name="{{ $field->name }}[]" value="{{ $opt }}">
                    <span>{{ $opt }}</span>
                  </label>
                @endforeach
                @break

              @case('radio')
                @php
                    $options = json_decode($field->meta, true)['options'] ?? [];
                @endphp
                @foreach($options as $opt)
                  <label class="flex items-center space-x-2">
                    <input type="radio" name="{{ $field->name }}" value="{{ $opt }}">
                    <span>{{ $opt }}</span>
                  </label>
                @endforeach
                @break

              @case('image')
                <input id="{{ $field->name }}"
                       name="{{ $field->name }}"
                       type="file"
                       accept="image/*"
                       @required($field->is_required)
                       class="w-full rounded-lg border-gray-300 px-4 py-3 text-base shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                @break
            @endswitch
          </div>
        @endforeach
        <div class="pt-4">
          <button type="submit"
                  class="w-full rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
            Daftar Sekarang
          </button>
        </div>
      </form>
    </div>
  </section>
@endsection
