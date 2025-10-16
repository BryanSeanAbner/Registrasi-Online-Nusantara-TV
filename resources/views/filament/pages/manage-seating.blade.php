@php
  $legend = [
    ['color' => '#22c55e', 'label' => 'Tersedia'],
    ['color' => '#9ca3af', 'label' => 'Terisi'],
    ['color' => '#f59e0b', 'label' => 'Diblok'],
  ];
@endphp

<div>
  <div style="display:flex; gap:16px; align-items:center; margin-top:25px; margin-bottom:12px; flex-wrap:wrap;">
    @foreach ($legend as $item)
      <div style="display:flex; align-items:center; gap:8px;">
        <span style="display:inline-block; width:12px; height:12px; border-radius:3px; background: {{ $item['color'] }};"></span>
        <span>{{ $item['label'] }}</span>
      </div>
    @endforeach
  </div>

  <div style="margin-bottom:16px; display:flex; gap:8px; flex-wrap:wrap;">
    <x-filament::button 
      href="{{ \App\Filament\Resources\SeatTables\SeatTableResource::getUrl('index') }}"
      tag="a" 
    >
      Kelola Meja
    </x-filament::button>
    <x-filament::button 
      href="{{ \App\Filament\Resources\Seats\SeatResource::getUrl('index') }}"
      tag="a"
    >
      Kelola Kursi
    </x-filament::button>
  </div>

  @if (! $activeEventId)
    <x-filament::section>
      <div>Silakan pilih Event aktif terlebih dahulu (switcher di topbar) untuk melihat kursi dan meja.</div>
    </x-filament::section>
  @else
    @if (empty($tables) && empty($unassignedSections))
      <x-filament::section>
        <div>Belum ada Meja/Kursi pada Event ini. Gunakan "Kelola Meja" atau "Kelola Kursi" untuk menambahkan.</div>
      </x-filament::section>
    @endif

    @foreach ($tables as $table)
      <x-filament::section style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
          <div>
            <strong>Meja:</strong> {{ $table['label'] }}
            @if (!is_null($table['capacity']))
              <span style="margin-left:8px; color:#6b7280;">(Kapasitas: {{ $table['capacity'] }})</span>
            @endif
          </div>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:10px;">
          @foreach ($table['seats'] as $seat)
            @php
              $bg = match ($seat['status']) {
                'taken' => '#9ca3af',
                'blocked' => '#f59e0b',
                default => '#22c55e',
              };
              $color = '#fff';
            @endphp
            <div style="background: {{ $bg }}; color: {{ $color }}; text-align:center; padding:10px 8px; border-radius:8px; font-weight:600;">
              {{ $seat['label'] }}
            </div>
          @endforeach
        </div>
      </x-filament::section>
    @endforeach

    @foreach ($unassignedSections as $sec)
      <x-filament::section style="margin-bottom:16px;">
        <div style="margin-bottom:8px;">
          <strong>Section:</strong> {{ $sec['section'] }}
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:10px;">
          @foreach ($sec['seats'] as $seat)
            @php
              $bg = match ($seat['status']) {
                'taken' => '#9ca3af',
                'blocked' => '#f59e0b',
                default => '#22c55e',
              };
              $color = '#fff';
            @endphp
            <div style="background: {{ $bg }}; color: {{ $color }}; text-align:center; padding:10px 8px; border-radius:8px; font-weight:600;">
              {{ $seat['label'] }}
            </div>
          @endforeach
        </div>
      </x-filament::section>
    @endforeach
  @endif
</div>


