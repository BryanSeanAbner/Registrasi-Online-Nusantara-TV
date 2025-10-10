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
      @endphp
      
      <div class="rounded-2xl shadow-xl border border-gray-200 overflow-hidden" style="@if($bgPath) background-image: url('{{ route('event.image', $bg) }}'); background-size: cover; background-position: center; @else background: linear-gradient(135deg, rgba(59,130,246,0.1) 0%, rgba(147,197,253,0.2) 100%); @endif">
        <div class="flex flex-col lg:flex-row">
          <!-- Left Section - Contact Information -->
          <div class="flex-1 p-8">
            <div class="p-8 mx-4 my-4">
              <!-- Logo -->
              <div class="mb-8 flex justify-center">
                @php
                    $brand = $event->brand ?? [];
                    $logoPath = $brand['logo'] ?? null;
                    $logo = '';

                    if ($logoPath) {
                      $logo = ltrim($logoPath, '/');
                    }
                @endphp
                @if($logoPath)
                  <img src="{{ route('event.image', $logo) }}" alt="Logo" class="object-contain rounded-lg bg-white max-w-[200px] sm:max-w-[240px] md:max-w-[280px] max-h-[120px]" />
                @else
                  <div class="w-16 h-16 bg-blue-900 rounded-lg flex items-center justify-center">
                    <span class="text-white text-2xl font-bold">ntv</span>
                  </div>
                @endif
              </div>
              
              <!-- Contact Details -->
              <div class="space-y-6">
                <!-- Email -->
                <div class="flex items-start space-x-4">
                  <div class="w-6 h-6 mt-1">
                    <svg class="w-full h-full text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-gray-500 text-sm">Email</p>
                    <p class="text-black font-medium">nusantaratv@gmail.com</p>
                  </div>
                </div>
                
                <!-- Phone -->
                <div class="flex items-start space-x-4">
                  <div class="w-6 h-6 mt-1">
                    <svg class="w-full h-full text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-gray-500 text-sm">No Telepon</p>
                    <p class="text-black font-medium">+62 857-7734-9636</p>
                  </div>
                </div>
                
                <!-- Location -->
                <div class="flex items-start space-x-4">
                  <div class="w-6 h-6 mt-1">
                    <svg class="w-full h-full text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-gray-500 text-sm">Lokasi</p>
                    <p class="text-black font-medium">Jl. Pulomas Selatan Kav. Blok, Kota Jakarta Timur 13210</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Section - Registration Form Card -->
          <div class="flex-1 p-8">
            <div class="rounded-2xl overflow-hidden h-full bg-gray-500/50">
              <!-- Card Header -->
              <div class="px-8 pt-6 pb-2">
                <h1 class="text-2xl font-bold text-white text-center">Daftar  {{ $event->title }}</h1>
              </div>
              
              <!-- Card Body -->
              <div class="px-8 pb-6">
                <form method="post" enctype="multipart/form-data" class="space-y-6">
                  @csrf

                  @foreach($fields as $field)
                    <div>
                      <label for="{{ $field->name }}" class="block text-sm font-medium text-white mb-2">
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
                                 placeholder="Masukkan {{ $field->label }}"
                                 @required($field->is_required)
                                 class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                          @break

                        @case('textarea')
                          <textarea id="{{ $field->name }}"
                                    name="{{ $field->name }}"
                                    rows="3"
                                    placeholder="Masukkan {{ $field->label }}"
                                    @required($field->is_required)
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                          @break

                        @case('select')
                          <select id="{{ $field->name }}"
                                  name="{{ $field->name }}"
                                  @required($field->is_required)
                                  class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">-- pilih --</option>
                            @foreach(($field->meta['options'] ?? []) as $opt)
                              <option value="{{ $opt }}">{{ $opt }}</option>
                            @endforeach
                          </select>
                          @break

                        @case('checkbox')
                          <div class="space-y-2">
                            @foreach(($field->meta['options'] ?? []) as $opt)
                              <label class="flex items-center space-x-2 text-gray-700">
                                <input type="checkbox" name="{{ $field->name }}[]" value="{{ $opt }}" class="rounded text-blue-600 focus:ring-blue-500">
                                <span>{{ $opt }}</span>
                              </label>
                            @endforeach
                          </div>
                          @break

                        @case('radio')
                          @php
                              $options = json_decode($field->meta, true)['options'] ?? [];
                          @endphp
                          <div class="space-y-2">
                            @foreach($options as $opt)
                              <label class="flex items-center space-x-2 text-gray-700">
                                <input type="radio" name="{{ $field->name }}" value="{{ $opt }}" class="text-blue-600 focus:ring-blue-500">
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
                                 class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                          @break
                      @endswitch
                    </div>
                  @endforeach

                  <div class="pt-4">
                    <button type="submit"
                            class="w-full rounded-lg bg-blue-500 px-6 py-3 text-base font-semibold text-white shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
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
  </section>
@endsection