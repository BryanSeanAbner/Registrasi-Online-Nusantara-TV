<?php

namespace App\Filament\Pages;

use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatTable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
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

    public function mount(): void
    {
        $this->activeEventId = (int) (session('active_event_id') ?: 0);

        $eventId = $this->activeEventId;
        if (! $eventId) {
            $this->tables = [];
            $this->unassignedSections = [];
            return;
        }

        // Load tables with seats and assignment flag
        $tables = SeatTable::with(['seats' => function ($q) use ($eventId) {
            $q->where('event_id', $eventId)->with('assignment');
        }])->where('event_id', $eventId)->orderBy('label')->get();

        $this->tables = $tables->map(function (SeatTable $t) {
            return [
                'id'       => $t->id,
                'label'    => $t->label,
                'capacity' => $t->capacity,
                'seats'    => $t->seats->map(fn (Seat $s) => [
                    'id'      => $s->id,
                    'label'   => $s->label,
                    'status'  => $s->assignment ? 'taken' : ($s->status ?? 'available'),
                    'section' => $s->section,
                ])->all(),
            ];
        })->all();

        // Seats without table, group by section
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
}

