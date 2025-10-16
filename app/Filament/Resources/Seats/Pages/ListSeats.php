<?php

namespace App\Filament\Resources\Seats\Pages;

use App\Filament\Resources\Seats\SeatResource;
use App\Models\Event;
use App\Models\Seat;
use App\Models\SeatTable;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSeats extends ListRecords
{
    protected static string $resource = SeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generateSeats')
                ->label('Generate Seats')
                ->icon('heroicon-o-sparkles')
                ->form([
                    Select::make('event_id')
                        ->label('Event')
                        ->options(fn () => Event::orderBy('starts_at', 'desc')->pluck('title', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('table_id')
                        ->label('Meja (opsional)')
                        ->options(fn (callable $get) => ($get('event_id')) ? SeatTable::where('event_id', $get('event_id'))->orderBy('label')->pluck('label', 'id') : [])
                        ->searchable()
                        ->preload()
                        ->native(false),
                    TextInput::make('section')
                        ->label('Section')
                        ->default('Main')
                        ->required(),
                    TextInput::make('rows')
                        ->label('Rows (mis. A-B)')
                        ->default('A-B')
                        ->required(),
                    TextInput::make('cols')
                        ->label('Cols (mis. 1-3)')
                        ->default('1-3')
                        ->required(),
                    TextInput::make('type')
                        ->label('Type (mis. regular)')
                        ->default('regular')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $eventId = (int) $data['event_id'];
                    $section = (string) $data['section'];
                    $type = (string) $data['type'];
                    $tableId = $data['table_id'] ? (int) $data['table_id'] : null;
                    [$rowStart, $rowEnd] = explode('-', strtoupper((string) $data['rows']));
                    [$colStart, $colEnd] = explode('-', (string) $data['cols']);

                    $created = 0; $skipped = 0; $capBlocked = 0;

                    // Enforce capacity when a table is selected
                    $remainingCapacity = PHP_INT_MAX;
                    if ($tableId) {
                        $table = SeatTable::find($tableId);
                        if ($table && $table->capacity) {
                            $current = Seat::where('event_id', $eventId)->where('table_id', $tableId)->count();
                            $remainingCapacity = max(0, (int) $table->capacity - $current);
                            if ($remainingCapacity <= 0) {
                                Notification::make()
                                    ->title('Generate Seats')
                                    ->body('Meja ini sudah mencapai kapasitas. Tidak ada kursi yang dibuat.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                        }
                    }

                    $requested = (ord($rowEnd) - ord($rowStart) + 1) * ((int) $colEnd - (int) $colStart + 1);

                    for ($r = ord($rowStart); $r <= ord($rowEnd); $r++) {
                        $rowLabel = chr($r);
                        for ($c = (int) $colStart; $c <= (int) $colEnd; $c++) {
                            if ($created >= $remainingCapacity) { break 2; }
                            $label = $rowLabel . '-' . $c;
                            $seat = Seat::firstOrCreate(
                                ['event_id' => $eventId, 'label' => $label],
                                [
                                    'table_id' => $tableId,
                                    'section' => $section,
                                    'row'     => $rowLabel,
                                    'col'     => $c,
                                    'type'    => $type,
                                    'status'  => 'available',
                                ]
                            );
                            if ($seat->wasRecentlyCreated) { $created++; } else { $skipped++; }
                        }
                    }

                    if ($tableId && $remainingCapacity !== PHP_INT_MAX) {
                        $capBlocked = max(0, $requested - ($created + $skipped));
                    }

                    $summary = "Created: {$created}\nSkipped (exists): {$skipped}";
                    if ($capBlocked > 0) {
                        $summary .= "\nBlocked by capacity: {$capBlocked}";
                    }

                    Notification::make()
                        ->title('Generate Seats')
                        ->body($summary)
                        ->success()
                        ->send();
                })
                ->modalHeading('Generate seats grid')
                ->modalSubmitActionLabel('Generate')
                ->requiresConfirmation(),
        ];
    }
}



