@extends('public.shared.layout')

@section('content')
  <section class="min-h-screen py-12 w-full">
    <div class="w-full px-4 py-6">
      @php
          $bgRaw = $event->brand['background'] ?? null;
          $bgPath = null;
          $bg = '';
          if ($bgRaw) {
            $bgPath = asset('storage/' . ltrim($bgRaw, '/'));
            $bg = ltrim($bgRaw, '/');
          }

          $brand = $event->brand ?? [];
          $logoPath = $brand['logo'] ?? null;
          $logo = '';
          if ($logoPath) {
            $logo = ltrim($logoPath, '/');
          }

          $bgStyle = $bgPath
              ? "background-image: url('" . route('event.image', $bg) . "');"
              : "background: linear-gradient(135deg, rgba(59,130,246,0.2) 0%, rgba(147,197,253,0.35) 100%);";
      @endphp

      <div class="rounded-2xl shadow-xl border border-blue-200 overflow-hidden bg-white max-w-xl mx-auto">
        <div class="relative h-40 sm:h-56 md:h-64 bg-cover bg-center" style="{{ $bgStyle }}">
          <div class="absolute inset-0"></div>

          <div class="absolute top-4 left-4 hidden sm:block">
            @if($logoPath)
              <img src="{{ route('event.image', $logo) }}" alt="Logo" class="object-contain bg-white/95 rounded-md shadow px-3 py-2 max-w-[180px] max-h-[64px]" />
            @else
              <div class="w-14 h-14 bg-white/95 rounded-md shadow flex items-center justify-center">
                <span class="text-blue-700 text-xl font-bold">ntv</span>
              </div>
            @endif
          </div>

          @if(!$bgPath)
            <div class="relative h-full flex items-center justify-center px-6">
              <h2 class="text-white text-2xl md:text-3xl font-semibold text-center drop-shadow">{{ $event->title }}</h2>
            </div>
          @endif
        </div>
        <div class="h-2 bg-blue-600"></div>

        @if($logoPath)
          <div class="sm:hidden mt-6 px-6 flex justify-center">
            <img src="{{ route('event.image', $logo) }}" alt="Logo" class="object-contain bg-white/95 rounded-md shadow px-2 py-1 max-w-[120px] max-h-[44px]" />
          </div>
        @else
          <div class="sm:hidden mt-6 px-6 flex justify-center">
            <div class="w-16 h-16 bg-white/95 rounded-md shadow flex items-center justify-center">
              <span class="text-blue-700 text-2xl font-bold">ntv</span>
            </div>
          </div>
        @endif

        <div class="p-6 sm:p-8">
          <div class="grid grid-cols-1 gap-8">
            <div class="order-2 max-w-2xl w-full mx-auto mb-6">
              <div class="space-y-6">
                <div class="flex items-start space-x-4">
                  <div class="w-6 h-6 mt-1">
                    <svg class="w-full h-full text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-blue-700 text-sm">Email</p>
                    <p class="text-gray-900 font-medium">nusantaratv@gmail.com</p>
                  </div>
                </div>

                <div class="flex items-start space-x-4">
                  <div class="w-6 h-6 mt-1">
                    <svg class="w-full h-full text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-blue-700 text-sm">No Telepon</p>
                    <p class="text-gray-900 font-medium">+62 857-7734-9636</p>
                  </div>
                </div>

                <div class="flex items-start space-x-4">
                  <div class="w-6 h-6 mt-1">
                    <svg class="w-full h-full text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-blue-700 text-sm">Lokasi</p>
                    <p class="text-gray-900 font-medium">Jl. Pulomas Selatan Kav. Blok, Kota Jakarta Timur 13210</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="order-1 max-w-xl w-full mx-auto">
              <div class="rounded-md h-full border border-blue-200 bg-white shadow border-t-8 border-blue-600">

                <div class="px-8 pt-6 pb-2">
                  <h1 class="text-xl md:text-2xl font-semibold text-gray-900 leading-tight text-center" title="{{ $event->title }}" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    {{ $event->title }}
                  </h1>
                </div>

                <div class="px-8 pb-6">
                  <form method="post" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @foreach($fields as $field)
                      <div>
                      <label for="{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ $field->label }}
                        @if(!$field->is_required)
                          <span class="text-gray-500">(opsional)</span>
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
                                 placeholder="Masukkan {{ $field->label }}"
                                 @required($field->is_required)
                                  class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-2 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-gray-600" />
                          @break

                        @case('textarea')
                          <textarea id="{{ $field->name }}"
                                    name="{{ $field->name }}"
                                    rows="3"
                                    placeholder="Masukkan {{ $field->label }}"
                                    @required($field->is_required)
                                    class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-2 text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-gray-600"></textarea>
                          @break

                        @case('select')
                          <select id="{{ $field->name }}"
                                  name="{{ $field->name }}"
                                  @required($field->is_required)
                                  class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-2 text-gray-900 focus:outline-none focus:ring-0 focus:border-gray-600">
                            <option value="">-- pilih --</option>
                            @foreach(($field->meta['options'] ?? []) as $opt)
                              <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                          </select>
                          @break

                        @case('checkbox')
                          <div class="space-y-2">
                            @foreach(($field->meta['options'] ?? []) as $opt)
                              <label class="flex items-center space-x-2 text-gray-800">
                                <input type="checkbox" name="{{ $field->name }}[]" value="{{ $opt }}" class="rounded text-gray-700 focus:ring-gray-600">
                                <span>{{ $opt }}</span>
                              </label>
                            @endforeach
                          </div>
                          @break

                        @case('radio')
                          <div class="space-y-2">
                            @foreach(($field->meta['options'] ?? []) as $opt)
                              <label class="flex items-center space-x-2 text-gray-800">
                                <input type="radio" name="{{ $field->name }}" value="{{ $opt }}" class="text-gray-700 focus:ring-gray-600">
                                <span>{{ $opt }}</span>
                              </label>
                            @endforeach
                          </div>
                          @break

                        @case('image')
                          <input id="{{ $field->name }}"
                                 name="{{ $field->name }}"
                                 type="file"
                                 accept="image/*"
                                 @required($field->is_required)
                                  class="w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-600 focus:border-gray-600" />
                          @break
                        @endswitch
                      </div>
                    @endforeach

                    <div class="pt-4">
                      <button type="submit"
                              class="rounded-md bg-blue-600 px-6 py-2 text-base font-medium text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 transition duration-200">
                        Daftar Sekarang
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
