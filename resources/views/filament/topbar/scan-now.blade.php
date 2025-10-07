@php
    $scanUrl  = route('scan.page');
    $siteUrl  = url('/');
@endphp

<div class="flex items-center gap-2">
    <x-filament::button
        tag="a"
        href="{{ $scanUrl }}"
        color="danger"
        icon="heroicon-o-qr-code"
        class="!rounded-full"
        target="_blank"
    >
        Scan Now
    </x-filament::button>

    {{-- <x-filament::button
        tag="a"
        href="{{ $siteUrl }}"
        target="_blank"
        color="danger"
        icon="heroicon-o-globe-alt"
        class="!rounded-full"
    >
        View Website
    </x-filament::button> --}}
</div>
