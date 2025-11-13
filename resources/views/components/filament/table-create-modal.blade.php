@props(['id' => 'create-table'])

<x-filament::modal
  id="{{ $id }}"
  width="md"
  icon="heroicon-o-table-cells"
  heading="Tambah Meja"
  wire:submit.prevent="saveCreateTable"
>
    <x-filament::input.wrapper 
      prefix="Label Meja" 
      inline-prefix 
      :valid="! $errors->has('createTableForm.label')"
      style="margin-bottom: 6px;"
    >
      <x-filament::input
        id="createTableLabel"
        type="text"
        wire:model.defer="createTableForm.label"
        placeholder="cth: Garuda, VIP-A, Regular"
        autofocus
      />
    </x-filament::input.wrapper>
    @error('createTableForm.label')
      <p style="font-size: 11px; color: #ff6467;" style="margin-bottom: 12px;">{{ $message }}</p>
    @enderror

    <x-filament::input.wrapper 
      prefix="Kapasitas" 
      inline-prefix 
      :valid="! $errors->has('createTableForm.capacity')"
      style="margin-bottom: 6px;"
    >
      <x-filament::input
        id="createTableCapacity"
        type="number"
        min="1"
        step="1"
        inputmode="numeric"
        wire:model.defer="createTableForm.capacity"
        placeholder="cth: 10"
      />
    </x-filament::input.wrapper>
    @error('createTableForm.capacity')
      <p style="font-size: 11px; color: #ff6467;">{{ $message }}</p>
    @enderror

    <x-slot name="footer">
      <div class="fi-modal-footer-actions">
        <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: '{{ $id }}' })">Batal</x-filament::button>
        <x-filament::button color="primary" type="submit" wire:loading.attr="disabled">Simpan</x-filament::button>
      </div>
    </x-slot>
</x-filament::modal>
