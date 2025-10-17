@props(['id' => 'edit-table'])

<x-filament::modal
  id="{{ $id }}"
  width="md"
  icon="heroicon-o-pencil-square"
  heading="Edit Meja"
  wire:submit.prevent="saveEditTable"
  :autofocus="false"
>
    <x-filament::input.wrapper 
      prefix="Label Meja" 
      inline-prefix 
      :valid="! $errors->has('editTableForm.label')"
      style="margin-bottom: 6px;"
    >
      <x-filament::input
        id="editTableLabel"
        type="text"
        wire:model.defer="editTableForm.label"
        placeholder="cth: Garuda, VIP-A, Regular"
      />
    </x-filament::input.wrapper>
    @error('editTableForm.label')
      <p style="font-size: 11px; color: #ff6467;" style="margin-bottom: 12px;">{{ $message }}</p>
    @enderror

    <x-filament::input.wrapper 
      prefix="Kapasitas" 
      inline-prefix 
      :valid="! $errors->has('editTableForm.capacity')"
      style="margin-bottom: 6px;"
    >
      <x-filament::input
        id="editTableCapacity"
        type="number"
        min="1"
        step="1"
        inputmode="numeric"
        wire:model.defer="editTableForm.capacity"
        placeholder="cth: 10"
      />
    </x-filament::input.wrapper>
    @error('editTableForm.capacity')
      <p style="font-size: 11px; color: #ff6467;">{{ $message }}</p>
    @enderror

    <x-slot name="footer">
      <div class="fi-modal-footer-actions">
        <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: '{{ $id }}' })">Batal</x-filament::button>
        <x-filament::button color="primary" type="submit" wire:loading.attr="disabled">Simpan</x-filament::button>
      </div>
    </x-slot>
</x-filament::modal>

