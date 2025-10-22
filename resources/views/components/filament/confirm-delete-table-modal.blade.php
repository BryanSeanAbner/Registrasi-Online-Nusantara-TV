@props(['id' => 'confirm-delete-table', 'label' => null])

<x-filament::modal
  id="{{ $id }}"
  width="sm"
  icon="heroicon-o-trash"
  heading="Hapus Meja"
>
  <div>
    Meja <strong>{{ $label ?? 'ini' }}</strong> dan semua kursinya akan dihapus.
    Tindakan ini tidak dapat dibatalkan.
  </div>

  <x-slot name="footer">
    <div class="fi-modal-footer-actions">
      <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: '{{ $id }}' })">Batal</x-filament::button>
      <x-filament::button color="danger" type="button" wire:click="confirmDeleteTable" wire:loading.attr="disabled">Hapus</x-filament::button>
    </div>
  </x-slot>
</x-filament::modal>
