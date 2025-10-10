@php(/** @var \Illuminate\Support\Collection<int, \App\Models\Event> $events */ null)
<x-filament::section class="sp-mt-6">
    <x-slot name="heading">Pilih Event untuk Melihat Scans</x-slot>
    <x-slot name="description">Klik salah satu event di bawah untuk melihat daftar scan yang difilter.</x-slot>
    <x-slot name="headerEnd">
        <x-filament::button
            tag="a"
            href="{{ \App\Filament\Resources\Scan\ScanResource::getUrl('list') }}"
        >
            Lihat Semua Scan
        </x-filament::button>
    </x-slot>

    @if($events->isEmpty())
        <div class="text-sm">
            Belum ada event tersedia.
        </div>
    @else
        <div class="sp-mt-4 sp-space-y-4">
            @foreach ($events as $event)
            <a href="{{ \App\Filament\Resources\Scan\ScanResource::getUrl('by-event', ['event' => $event->id]) }}">
                <x-filament::section :heading="$event->title">
                    <div class="text-sm">
                        @if($event->starts_at || $event->ends_at)
                            <div class="fi-text-secondary">
                                <x-filament::icon icon="heroicon-o-calendar" class="h-4 w-4 inline" />
                                <span class="align-middle">
                                    {{ optional($event->starts_at)->format('d M Y H:i') ?? '-' }}
                                    @if($event->ends_at)
                                        — {{ $event->ends_at->format('d M Y H:i') }}
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if($event->venue)
                            <div class="fi-text-secondary mt-1">
                                <x-filament::icon icon="heroicon-o-map-pin" class="h-4 w-4 inline" />
                                <span class="align-middle">{{ $event->venue }}</span>
                            </div>
                        @endif
                    </div>

                    <x-slot name="footer">
                        <x-filament::button icon="heroicon-o-arrow-right">
                            Lihat Scan
                        </x-filament::button>
                    </x-slot>
                </x-filament::section>
            </a>
        @endforeach
        </div>
    @endif
</x-filament::section>
