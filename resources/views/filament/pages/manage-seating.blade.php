@php
  $legend = [
    ['color' => '#22c55e', 'label' => 'Tersedia'],
    ['color' => '#9ca3af', 'label' => 'Terisi'],
    ['color' => '#f59e0b', 'label' => 'Diblok'],
  ];
@endphp

<div>
  <div style="display:flex; align-items:center; justify-content:space-between; margin-top:25px; margin-bottom:12px; flex-wrap:wrap; gap:12px;">
    <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
      @foreach ($legend as $item)
        <div style="display:flex; align-items:center; gap:8px;">
          <span style="display:inline-block; width:12px; height:12px; border-radius:3px; background: {{ $item['color'] }};"></span>
          <span>{{ $item['label'] }}</span>
        </div>
      @endforeach
    </div>
    @if ($activeEventId)
      <x-filament::button color="primary" icon="heroicon-o-plus" wire:click="openCreateTableModal">
        Tambah Meja
      </x-filament::button>
    @endif
  </div>

  @if (! $activeEventId)
    <x-filament::section>
      <div>Silakan pilih Event aktif terlebih dahulu (switcher di topbar) untuk melihat kursi dan meja.</div>
    </x-filament::section>
  @else
    @if (empty($tables) && empty($unassignedSections))
      <x-filament::section>
        <div>Belum ada Meja/Kursi pada Event ini. Gunakan "Tambah Meja" untuk menambahkan.</div>
      </x-filament::section>
    @endif

    @foreach ($tables as $table)
      <x-filament::section style="margin-bottom:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; gap:12px;">
          <div>
            <strong>Meja :</strong> {{ $table['label'] }}
            @if (!is_null($table['capacity']))
              <span style="margin-left:8px; color:#6b7280;">(Kapasitas: {{ $table['capacity'] }})</span>
            @endif
          </div>
          <div style="display:flex; gap:8px; align-items:center;">
            <x-filament::button 
              color="primary" 
              icon="heroicon-o-plus" 
              wire:click="addSeat({{ $table['id'] }})"
              :disabled="$table['is_full']"
              :tooltip="$table['is_full'] ? 'Kapasitas meja penuh' : 'Tambah Kursi'"
            >Tambah Kursi</x-filament::button>
            <x-filament::button 
              color="danger" 
              icon="heroicon-o-trash" 
              wire:click="openDeleteTableModal({{ $table['id'] }})"
            >Hapus Meja</x-filament::button>
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
            <div 
              x-data="{ loading: false }"
              x-on:click="loading = true; $wire.openSeatModal({{ $seat['id'] }})"
              x-on:open-modal.window="loading = false"
              x-on:close-modal.window="loading = false"
              style="background: {{ $bg }}; color: {{ $color }}; text-align:center; padding:10px 8px; border-radius:8px; font-weight:600; cursor:pointer; position:relative;"
              title="Edit {{ $seat['label'] }}"
            >
              <span x-show="!loading">{{ $seat['label'] }}</span>
              <span x-show="loading" style="display:inline-flex; align-items:center; gap:6px; justify-content:center;">
                <x-filament::loading-indicator class="h-4 w-4" />
                Loading
              </span>
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
            <div 
              x-data="{ loading: false }"
              x-on:click="loading = true; $wire.openSeatModal({{ $seat['id'] }})"
              x-on:open-modal.window="loading = false"
              x-on:close-modal.window="loading = false"
              style="background: {{ $bg }}; color: {{ $color }}; text-align:center; padding:10px 8px; border-radius:8px; font-weight:600; cursor:pointer; position:relative;"
              title="Edit {{ $seat['label'] }}"
            >
              <span x-show="!loading">{{ $seat['label'] }}</span>
              <span x-show="loading" style="display:inline-flex; align-items:center; gap:6px; justify-content:center;">
                <x-filament::loading-indicator class="h-4 w-4" />
                Loading
              </span>
            </div>
          @endforeach
        </div>
      </x-filament::section>
    @endforeach
  @endif


  <x-filament.seat-edit-modal id="edit-seat" />
  <x-filament.table-create-modal id="create-table" />
  <x-filament.confirm-delete-table-modal id="confirm-delete-table" :label="$selectedTableLabel" />
