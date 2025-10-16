@props(['id' => 'edit-seat'])

<x-filament::modal 
  id="{{ $id }}"
  width="md"
  icon="heroicon-o-pencil-square"
  heading="Edit Kursi"
  wire:submit.prevent="saveSeat"
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
      />
    </x-filament::input.wrapper>
    @error('seatForm.label')
      <p class="fi-fo-field-wrp-error-message" style="margin-bottom: 12px;">{{ $message }}</p>
    @enderror

    <x-filament::input.wrapper 
      prefix="Status" 
      inline-prefix 
      :valid="! $errors->has('seatForm.status')"
    >
      <x-filament::input.select id="seatStatus" wire:model.defer="seatForm.status">
        <option value="available">Tersedia</option>
        <option value="blocked">Diblok</option>
        <option value="maintenance">Maintenance</option>
      </x-filament::input.select>
    </x-filament::input.wrapper>
    @error('seatForm.status')
      <p class="fi-fo-field-wrp-error-message" style="margin-top: 6px;">{{ $message }}</p>
    @enderror

    <x-slot name="footer">
      <div class="fi-modal-footer-actions">
        <x-filament::button color="danger" 
          type="button"
          x-on:click="$dispatch('open-modal', { id: 'confirm-delete-seat' })"
        >Hapus</x-filament::button>
        <x-filament::button color="gray" 
          type="button"
          x-on:click="$dispatch('close-modal', { id: '{{ $id }}' })"
        >Batal</x-filament::button>
        <x-filament::button color="primary" type="submit" wire:loading.attr="disabled">Simpan</x-filament::button>
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
