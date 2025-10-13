@php
    use App\Models\Event;
    $active = session('active_event_id');
    $events = cache()->remember('fi-topbar-events', 30, fn () => Event::orderByDesc('starts_at')->orderBy('title')->get(['id','title']));
    $panelBase = config('filament.panels.admin.path', 'admin');
    $action = url($panelBase . '/set-active-event');
@endphp

<form method="POST" action="{{ $action }}" class="hidden md:flex items-center gap-2">
    @csrf
    <x-filament::input.wrapper inline-prefix>
        <x-filament::input.select name="event_id" x-on:change="$el.form.submit()">
            <option value="">Semua Event</option>
            @foreach ($events as $e)
                <option value="{{ $e->id }}" @selected((string) $active === (string) $e->id)>{{ $e->title }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</form>
