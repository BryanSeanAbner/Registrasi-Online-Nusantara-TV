<?php

namespace App\Filament\Resources\SeatTables\Pages;

use App\Filament\Resources\SeatTables\SeatTableResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Seat;
use Filament\Notifications\Notification;

class CreateSeatTable extends CreateRecord
{
    protected static string $resource = SeatTableResource::class;

    protected function afterCreate(): void
    {
        $table = $this->record;
        $capacity = (int) ($table->capacity ?? 0);
        $labelPrefix = (string) $table->label;
        $eventId = (int) $table->event_id;
        if ($capacity <= 0) { return; }
        $created = 0; $skipped = 0;
        for ($i = 1; $i <= $capacity; $i++) {
            $label = $labelPrefix . '-' . $i;
            $seat = \App\Models\Seat::firstOrCreate(
                ['event_id' => $eventId, 'label' => $label],
                [
                    'table_id' => $table->id,
                    'section'  => null,
                    'row'      => null,
                    'col'      => null,
                    'type'     => 'regular',
                    'status'   => 'available',
                ]
            );
            if ($seat->wasRecentlyCreated) { $created++; } else { $skipped++; }
        }
        \Filament\Notifications\Notification::make()
            ->title('Meja dibuat')
            ->body("Kursi otomatis: Created {$created}" . ($skipped ? "\nSkipped (exists): {$skipped}" : ''))
            ->success()
            ->send();
    }
}
