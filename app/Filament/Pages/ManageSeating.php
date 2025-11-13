<?php

namespace App\Filament\Pages;

use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use UnitEnum;

class ManageSeating extends Page
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string | UnitEnum | null $navigationGroup = 'Event Management';
    protected static ?string $navigationLabel = 'Seats & Tables';
    protected static ?string $title = 'Seats & Tables';

    protected string $view = 'filament.pages.manage-seating';

    public array $tables = [];
    public array $unassignedSections = [];
    public ?int $activeEventId = null;

    // Modal & form state
    public bool $isSeatModalOpen = false;
    public ?int $selectedSeatId = null;
    public bool $seatIsTaken = false;
    public array $seatForm = [
        'label' => '',
        'section' => '',
        'status' => 'available',
    ];

    // Create table modal state
    public array $createTableForm = [
        'label' => '',
        'capacity' => null,
    ];

    // Edit table modal state
    public ?int $editingTableId = null;
    public array $editTableForm = [
        'label' => '',
        'capacity' => null,
    ];

    // Delete table confirmation state
    public ?int $selectedTableId = null;
    public ?string $selectedTableLabel = null;

    public function mount(): void
    {
        $this->activeEventId = (int) (session('active_event_id') ?: 0);
        $this->loadData();
    }

    private function loadData(): void
    {
        $eventId = $this->activeEventId;
        if (! $eventId) {
            $this->tables = [];
            $this->unassignedSections = [];
            return;
        }

        $tables = SeatTable::with(['seats' => function ($q) use ($eventId) {
            $q->where('event_id', $eventId)->with('assignment');
        }])->where('event_id', $eventId)->orderBy('label')->get();

        $this->tables = $tables->map(function (SeatTable $t) {
            $seatCount = $t->seats->count();
            $capacity = $t->capacity;
            return [
                'id'       => $t->id,
                'label'    => $t->label,
                'capacity' => $capacity,
                'count'    => $seatCount,
                'is_full'  => (! is_null($capacity)) ? ($seatCount >= (int) $capacity) : false,
                'seats'    => $t->seats->map(fn (Seat $s) => [
                    'id'      => $s->id,
                    'label'   => $s->label,
                    'status'  => $s->assignment ? 'taken' : ($s->status ?? 'available'),
                    'section' => $s->section,
                ])->all(),
            ];
        })->all();

        $unassigned = Seat::where('event_id', $eventId)
            ->whereNull('table_id')
            ->with('assignment')
            ->get();

        $this->unassignedSections = $unassigned->groupBy(fn ($s) => $s->section ?: 'No Section')
            ->map(fn ($group, $section) => [
                'section' => (string) $section,
                'seats'   => $group->map(fn (Seat $s) => [
                    'id'     => $s->id,
                    'label'  => $s->label,
                    'status' => $s->assignment ? 'taken' : ($s->status ?? 'available'),
                ])->all(),
            ])->values()->all();
    }

    public function openSeatModal(int $seatId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->selectedSeatId = $seatId;

        $seat = Seat::with('assignment')
            ->where('event_id', $this->activeEventId)
            ->findOrFail($seatId);

        $this->seatForm = [
            'label' => (string) $seat->label,
            'section' => (string) ($seat->section ?? ''),
            'status' => (string) ($seat->status ?: 'available'),
        ];

        $this->seatIsTaken = (bool) $seat->assignment;

        // Open Filament modal
        $this->dispatch('open-modal', id: 'edit-seat');
    }

    public function saveSeat(): void
    {
        if (! $this->selectedSeatId) {
            return;
        }

        $validated = $this->validate(
            [
                'seatForm.label' => ['required', 'string', 'max:255'],
                'seatForm.section' => ['nullable', 'string', 'max:255'],
                'seatForm.status' => ['required', 'in:available,blocked,maintenance'],
            ],
            messages: [],
            attributes: [
                'seatForm.label' => 'label',
                'seatForm.section' => 'section',
                'seatForm.status' => 'status',
            ]
        );

        $seat = Seat::with('assignment')
            ->where('event_id', $this->activeEventId)
            ->findOrFail($this->selectedSeatId);

        if ($seat->assignment) {
            Notification::make()
                ->title('Kursi sudah terisi dan tidak bisa diedit')
                ->danger()
                ->send();
            return;
        }

        $seat->update([
            'label' => $this->seatForm['label'],
            'section' => $this->seatForm['section'] ?: null,
            'status' => $this->seatForm['status'],
        ]);

        $this->selectedSeatId = null;
        $this->loadData();

        Notification::make()
            ->title('Kursi berhasil diperbarui')
            ->success()
            ->send();

        // Close modals
        $this->dispatch('close-modal', id: 'edit-seat');
        $this->dispatch('close-modal', id: 'confirm-delete-seat');
    }

    public function unassignSeat(): void
    {
        if (! $this->selectedSeatId) {
            return;
        }

        $seat = Seat::with('assignment')
            ->where('event_id', $this->activeEventId)
            ->findOrFail($this->selectedSeatId);

        if (! $seat->assignment) {
            Notification::make()
                ->title('Kursi tidak sedang terisi')
                ->warning()
                ->send();
        } else {
            $seat->assignment->delete();

            Notification::make()
                ->title('Kursi dilepaskan')
                ->success()
                ->send();
        }

        $this->seatIsTaken = false;
        $this->selectedSeatId = null;
        $this->loadData();

        $this->dispatch('close-modal', id: 'confirm-release-seat');
        $this->dispatch('close-modal', id: 'edit-seat');
    }

    public function deleteSeat(): void
    {
        if (! $this->selectedSeatId) {
            return;
        }

        $seat = Seat::with('assignment')
            ->where('event_id', $this->activeEventId)
            ->findOrFail($this->selectedSeatId);

        if ($seat->assignment) {
            Notification::make()
                ->title('Kursi sudah terisi dan tidak bisa dihapus')
                ->danger()
                ->send();
            return;
        }

        $seat->delete();

        $this->selectedSeatId = null;
        $this->loadData();

        Notification::make()
            ->title('Kursi dihapus')
            ->success()
            ->send();

        // Close modals
        $this->dispatch('close-modal', id: 'edit-seat');
        $this->dispatch('close-modal', id: 'confirm-delete-seat');
    }

    public function openCreateTableModal(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->createTableForm = [
            'label' => '',
            'capacity' => null,
        ];

        $this->dispatch('open-modal', id: 'create-table');
    }

    public function saveCreateTable(): void
    {
        $this->validate(
            [
                'createTableForm.label' => ['required', 'string', 'max:255'],
                'createTableForm.capacity' => ['required', 'integer', 'min:1'],
            ],
            messages: [],
            attributes: [
                'createTableForm.label' => 'label',
                'createTableForm.capacity' => 'capacity',
            ]
        );

        $eventId = (int) ($this->activeEventId ?? 0);
        if (! $eventId) {
            Notification::make()->title('Tidak ada Event aktif')->danger()->send();
            return;
        }

        $table = new SeatTable();
        $table->event_id = $eventId;
        $table->label = $this->createTableForm['label'];
        $table->capacity = (int) $this->createTableForm['capacity'];
        $table->save();

        $created = 0; $skipped = 0;
        $prefix = (string) $table->label;

        $resetEvery = $table->capacity < 7 ? ceil($table->capacity / 2) : 6;   
        for ($i = 1; $i <= (int) $table->capacity; $i++) {
            $col = (($i - 1) % $resetEvery) + 1;
            $row = ceil($i / $resetEvery);
            $label = $prefix . '-' . $i;
            $seat = Seat::firstOrCreate(
                ['event_id' => $eventId, 'label' => $label],
                [
                    'table_id' => $table->id,
                    'section'  => null,
                    'row'      => $row,
                    'col'      => $col,
                    'type'     => 'regular',
                    'status'   => 'available',
                ]
            );
            if ($seat->wasRecentlyCreated) { $created++; } else { $skipped++; }
        }

        $this->dispatch('close-modal', id: 'create-table');
        $this->loadData();

        Notification::make()
            ->title('Meja dibuat')
            ->body("Kursi otomatis: Created {$created}" . ($skipped ? "\nSkipped (exists): {$skipped}" : ''))
            ->success()
            ->send();
    }

    public function openEditTableModal(int $tableId): void
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $eventId = (int) ($this->activeEventId ?? 0);
        if (! $eventId) {
            Notification::make()->title('Tidak ada Event aktif')->danger()->send();
            return;
        }

        $table = SeatTable::where('event_id', $eventId)->findOrFail($tableId);

        $this->editingTableId = $table->id;
        $this->editTableForm = [
            'label' => (string) $table->label,
            'capacity' => (int) ($table->capacity ?? 0),
        ];

        $this->dispatch('open-modal', id: 'edit-table');
    }

    public function saveEditTable(): void
    {
        $tableId = (int) ($this->editingTableId ?? 0);
        if (! $tableId) { return; }

        $validated = $this->validate(
            [
                'editTableForm.label' => ['required', 'string', 'max:255'],
                'editTableForm.capacity' => ['required', 'integer', 'min:1'],
            ],
            messages: [],
            attributes: [
                'editTableForm.label' => 'label',
                'editTableForm.capacity' => 'capacity',
            ]
        );

        $eventId = (int) ($this->activeEventId ?? 0);
        if (! $eventId) {
            Notification::make()->title('Tidak ada Event aktif')->danger()->send();
            return;
        }

        $table = SeatTable::withCount('seats')
            ->where('event_id', $eventId)
            ->findOrFail($tableId);

        $newCapacity = (int) $this->editTableForm['capacity'];
        if ($table->seats_count > $newCapacity) {
            $this->addError('editTableForm.capacity', 'Kapasitas tidak boleh kurang dari jumlah kursi saat ini ('.$table->seats_count.').');
            return;
        }

        $table->label = (string) $this->editTableForm['label'];
        $table->capacity = $newCapacity;
        $table->save();

        $this->editingTableId = null;
        $this->dispatch('close-modal', id: 'edit-table');
        $this->loadData();

        Notification::make()
            ->title('Meja diperbarui')
            ->success()
            ->send();
    }

    public function openDeleteTableModal(int $tableId): void
    {
        $eventId = (int) ($this->activeEventId ?? 0);
        if (! $eventId) {
            Notification::make()->title('Tidak ada Event aktif')->danger()->send();
            return;
        }

        $table = SeatTable::where('event_id', $eventId)->findOrFail($tableId);
        $this->selectedTableId = $table->id;
        $this->selectedTableLabel = (string) $table->label;
        $this->dispatch('open-modal', id: 'confirm-delete-table');
    }

    public function confirmDeleteTable(): void
    {
        $tableId = (int) ($this->selectedTableId ?? 0);
        if (! $tableId) { return; }

        $this->deleteTable($tableId);
        $this->selectedTableId = null;
        $this->selectedTableLabel = null;
        $this->dispatch('close-modal', id: 'confirm-delete-table');
    }

    public function deleteTable(int $tableId): void
    {
        $eventId = (int) ($this->activeEventId ?? 0);
        if (! $eventId) {
            Notification::make()->title('Tidak ada Event aktif')->danger()->send();
            return;
        }

        $table = SeatTable::with(['seats' => fn ($q) => $q->with('assignment')])
            ->where('event_id', $eventId)
            ->findOrFail($tableId);

        $hasTaken = $table->seats->contains(fn (Seat $s) => (bool) $s->assignment);
        if ($hasTaken) {
            Notification::make()
                ->title('Tidak bisa menghapus')
                ->body('Ada kursi yang sudah terisi pada meja ini.')
                ->danger()
                ->send();
            return;
        }

        // Hapus semua kursi lalu hapus meja
        $table->seats()->delete();
        $table->delete();

        $this->loadData();

        Notification::make()
            ->title('Meja dihapus')
            ->body('Meja dan semua kursinya berhasil dihapus.')
            ->success()
            ->send();
    }

    public function addSeat(int $tableId): void
    {
        $eventId = (int) ($this->activeEventId ?? 0);
        if (! $eventId) {
            Notification::make()->title('Tidak ada Event aktif')->danger()->send();
            return;
        }

        $table = SeatTable::with('seats')
            ->where('event_id', $eventId)
            ->findOrFail($tableId);

        $capacity = (int) ($table->capacity ?? 0);
        $seatCount = $table->seats->count();
        if ($capacity && $seatCount >= $capacity) {
            Notification::make()
                ->title('Kapasitas penuh')
                ->body('Tidak dapat menambah kursi karena kapasitas meja sudah penuh.')
                ->danger()
                ->send();
            return;
        }

        $prefix = (string) $table->label;

        // Cari nomor kursi berikutnya yang belum digunakan (unik per event)
        $maxTry = max($capacity ?: 0, $seatCount + 5) + 10;
        $label = null;
        for ($i = 1; $i <= $maxTry; $i++) {
            $candidate = $prefix . '-' . $i;
            $exists = Seat::where('event_id', $eventId)->where('label', $candidate)->exists();
            if (! $exists) {
                $label = $candidate;
                break;
            }
        }

        if (! $label) {
            Notification::make()
                ->title('Tidak dapat menambah kursi')
                ->body('Semua nomor label kursi untuk meja ini sudah terpakai.')
                ->danger()
                ->send();
            return;
        }

        $nextIndex = $seatCount + 1;
        $resetEvery = $capacity < 7 ? ceil($capacity / 2) : 6; 
        $col = (($nextIndex - 1) % $resetEvery) + 1;
        $row = ceil($nextIndex / $resetEvery);

        Seat::create([
            'event_id' => $eventId,
            'table_id' => $table->id,
            'label'    => $label,
            'section'  => null,
            'row'      => $row,
            'col'      => $col,
            'type'     => 'regular',
            'status'   => 'available',
        ]);

        $this->loadData();

        Notification::make()
            ->title('Kursi ditambahkan')
            ->body("Label: {$label}")
            ->success()
            ->send();
    }
}
