@extends('public.shared.layout')

@section('content')
  <section class="px-4 py-12">
    <div class="mx-auto w-full max-w-md text-center">
      {{-- Header --}}
      <h1 class="text-xl font-semibold text-gray-900">Event</h1>
      @if($events->count())
        <div class="mt-1 text-xs text-gray-500">{{ $events->count() }} acara</div>
      @endif

      {{-- List (semua item di tengah & compact) --}}
      <div class="mt-6 space-y-4 text-left">
        @forelse ($events as $event)
          <a href="{{ route('event.show', $event->slug) }}"
             class="group block rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <div class="flex items-center gap-3">
              {{-- Badge tanggal kecil --}}
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

              {{-- Info event --}}
              <div class="min-w-0">
                <h2 class="truncate text-base font-semibold text-gray-900 group-hover:text-blue-700">
                  {{ $event->title }}
                </h2>

                @if($event->venue)
                  <p class="mt-0.5 flex items-center gap-1.5 text-[13px] text-gray-600">
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

              {{-- Arrow kecil --}}
              <svg xmlns="http://www.w3.org/2000/svg"
                   class="ml-auto hidden h-4 w-4 text-blue-700 transition -translate-x-1 group-hover:translate-x-0 sm:block"
                   viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M3.75 12a.75.75 0 0 1 .75-.75h12.69l-4.72-4.72a.75.75 0 1 1 1.06-1.06l6 6a.75.75 0 0 1 0 1.06l-6 6a.75.75 0 1 1-1.06-1.06l4.72-4.72H4.5A.75.75 0 0 1 3.75 12Z" clip-rule="evenodd"/>
              </svg>
            </div>
          </a>
        @empty
          <div class="mx-auto max-w-sm rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center text-gray-500">
            Belum ada event.
          </div>
        @endforelse
      </div>
    </div>
  </section>
@endsection
