<?php

namespace App\Filament\Resources\SeatTables\Pages;

use App\Filament\Resources\SeatTables\SeatTableResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeatTable extends EditRecord
{
    protected static string $resource = SeatTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
