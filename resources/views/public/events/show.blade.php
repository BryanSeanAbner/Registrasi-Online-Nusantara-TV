@extends('public.shared.layout')

@section('content')
  <section class="px-4 py-12">
    <div class="mx-auto w-full max-w-md">
      {{-- Back --}}
      <a href="{{ url('/') }}" class="mb-4 inline-flex items-center text-xs text-gray-500 hover:text-blue-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
          <path fill-rule="evenodd" d="M20.25 12a.75.75 0 0 1-.75.75H7.56l4.22 4.22a.75.75 0 0 1-1.06 1.06l-5.5-5.5a.75.75 0 0 1 0-1.06l5.5-5.5a.75.75 0 0 1 1.06 1.06L7.56 11.25H19.5a.75.75 0 0 1 .75.75Z" clip-rule="evenodd"/>
        </svg>
        Kembali
      </a>

      <article class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        {{-- Header compact --}}
        <div class="mb-4 flex items-center gap-3">
          {{-- Badge tanggal kecil (nyambung ke list) --}}
          @if($event->starts_at)
            <div class="shrink-0 text-center">
              <div class="rounded-md border bg-gray-50 px-2.5 py-1.5 leading-tight">
                <div class="text-[10px] uppercase tracking-wide text-gray-500">
                  {{ $event->starts_at->timezone(config('app.timezone'))->format('D') }}
                </div>
                <div class="text-lg font-bold text-gray-900">
                  {{ $event->starts_at->format('d') }}
                </div>
                <div class="text-[10px] text-gray-500">
                  {{ $event->starts_at->format('M Y') }}
                </div>
              </div>
              <div class="mt-0.5 text-[11px] text-gray-500">
                {{ $event->starts_at->format('H:i') }}
              </div>
            </div>
          @endif

          <div class="min-w-0">
            <h1 class="truncate text-lg font-semibold text-gray-900">{{ $event->title }}</h1>

            @if($event->venue)
              <p class="mt-1 flex items-center gap-1.5 text-[13px] text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                  <path fill-rule="evenodd" d="M11.54 22.35a.75.75 0 0 0 .92 0C14.5 20.74 20 16.28 20 10.5 20 6.36 16.64 3 12.5 3S5 6.36 5 10.5c0 5.78 5.5 10.24 6.54 11.85ZM12.5 12.75a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd"/>
                </svg>
                <span class="line-clamp-1">{{ $event->venue }}</span>
              </p>
            @endif

            @if($event->starts_at)
              <p class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" viewBox="0 0 24 24" fill="currentColor">
                  <path fill-rule="evenodd" d="M12 2.25a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5Zm.75 5.25a.75.75 0 0 0-1.5 0v5.19c0 .3.18.57.46.69l3.75 1.6a.75.75 0 0 0 .58-1.38l-3.29-1.4V7.5Z" clip-rule="evenodd"/>
                </svg>
                {{ $event->starts_at->timezone(config('app.timezone'))->format('l, d M Y · H:i') }}
              </p>
            @endif
          </div>
        </div>

        {{-- Brand strip opsional (tipis) --}}
        @php $brand = $event->brand ?? []; @endphp
        @if(!empty($brand['primary']))
          <div class="mb-4 h-1 w-full rounded-full" style="background: {{ $brand['primary'] }}"></div>
        @endif

        {{-- Deskripsi (opsional) --}}
        @if(!empty($event->description))
          <div class="prose prose-sm max-w-none text-gray-700">
            {!! nl2br(e($event->description)) !!}
          </div>
        @endif

        {{-- CTA --}}
        <div class="mt-6">
          <a href="{{ route('register.create', $event->slug) }}"
             class="inline-flex items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            Daftar Sekarang
          </a>
        </div>
      </article>
    </div>
  </section>
@endsection

