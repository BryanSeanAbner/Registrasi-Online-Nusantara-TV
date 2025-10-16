@props(['id' => 'create-table'])

<x-filament::modal
  id="{{ $id }}"
  width="md"
  icon="heroicon-o-table-cells"
  heading="Tambah Meja"
  wire:submit.prevent="saveCreateTable"
>
    <x-filament::input.wrapper prefix="Label Meja" inline-prefix style="margin-bottom: 15px;">
      <x-filament::input
        id="createTableLabel"
        type="text"
        wire:model.defer="createTableForm.label"
        placeholder="cth: Garuda, VIP-A, Regular"
        autofocus
      />
    </x-filament::input.wrapper>

    <x-filament::input.wrapper prefix="Kapasitas" inline-prefix>
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

    <x-slot name="footer">
      <div class="fi-modal-footer-actions">
        <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: '{{ $id }}' })">Batal</x-filament::button>
        <x-filament::button color="primary" type="submit" wire:loading.attr="disabled">Simpan</x-filament::button>
      </div>
    </x-slot>
</x-filament::modal>
