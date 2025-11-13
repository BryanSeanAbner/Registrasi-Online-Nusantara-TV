@props(['id' => 'edit-seat', 'isTaken' => false])

<x-filament::modal 
  id="{{ $id }}"
  width="md"
  icon="heroicon-o-pencil-square"
  heading="{{ $isTaken ? 'Kursi Terisi' : 'Edit Kursi' }}"
  wire:submit.prevent="saveSeat"
  :autofocus="false"
>
    <x-filament::input.wrapper 
      prefix="Label" 
      inline-prefix 
      :valid="! $errors->has('seatForm.label')"
      style="margin-bottom: 6px;"
    >
      <x-filament::input
        id="seatLabel"
        type="text" 
        wire:model.defer="seatForm.label" 
        placeholder="cth: Garuda-1"
        :disabled="$isTaken"
      />
    </x-filament::input.wrapper>
    @error('seatForm.label')
      <p style="font-size: 11px; color: #ff6467;">{{ $message }}</p>
    @enderror

    <x-filament::input.wrapper 
      prefix="Status" 
      inline-prefix 
      :valid="! $errors->has('seatForm.status')"
    >
      <x-filament::input.select id="seatStatus" wire:model.defer="seatForm.status" :disabled="$isTaken">
        <option value="available">Tersedia</option>
        <option value="blocked">Diblok</option>
        <option value="maintenance">Maintenance</option>
      </x-filament::input.select>
    </x-filament::input.wrapper>
    @error('seatForm.status')
      <p style="font-size: 11px; color: #ff6467;" style="margin-top: 6px;">{{ $message }}</p>
    @enderror

    <x-slot name="footer">
      <div class="fi-modal-footer-actions">
        @if ($isTaken)
          <x-filament::button color="danger" 
            type="button"
            x-on:click="$dispatch('open-modal', { id: 'confirm-release-seat' })"
          >Lepas Kursi</x-filament::button>
          <x-filament::button color="gray" 
            type="button"
            x-on:click="$dispatch('close-modal', { id: '{{ $id }}' })"
          >Tutup</x-filament::button>
        @else
          <x-filament::button color="danger" 
            type="button"
            x-on:click="$dispatch('open-modal', { id: 'confirm-delete-seat' })"
          >Hapus</x-filament::button>
          <x-filament::button color="gray" 
            type="button"
            x-on:click="$dispatch('close-modal', { id: '{{ $id }}' })"
          >Batal</x-filament::button>
          <x-filament::button color="primary" type="submit" wire:loading.attr="disabled">Simpan</x-filament::button>
        @endif
      </div>
    </x-slot>
</x-filament::modal>

<x-filament::modal 
  id="confirm-delete-seat"
  width="sm"
  icon="heroicon-o-trash"
  heading="Hapus Kursi"
>
  <div>
    Apakah Anda yakin ingin menghapus kursi ini? Aksi ini tidak dapat dibatalkan.
  </div>

  <x-slot name="footer">
    <div class="fi-modal-footer-actions">
      <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: 'confirm-delete-seat' })">Batal</x-filament::button>
      <x-filament::button color="danger" type="button" wire:click="deleteSeat" wire:loading.attr="disabled">Hapus</x-filament::button>
    </div>
  </x-slot>
</x-filament::modal>

<x-filament::modal 
  id="confirm-release-seat"
  width="sm"
  icon="heroicon-o-exclamation-triangle"
  heading="Lepas Kursi?"
>
  <div>
    Kursi ini sudah dipilih peserta. Lepas kursi dari peserta?
  </div>

  <x-slot name="footer">
    <div class="fi-modal-footer-actions">
      <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: 'confirm-release-seat' })">Batal</x-filament::button>
      <x-filament::button color="danger" type="button" wire:click="unassignSeat" wire:loading.attr="disabled">Lepas</x-filament::button>
    </div>
  </x-slot>
</x-filament::modal>
