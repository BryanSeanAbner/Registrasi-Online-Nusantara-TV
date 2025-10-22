<?php

namespace App\Filament\Resources\SeatTables\Pages;

use App\Filament\Resources\SeatTables\SeatTableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeatTables extends ListRecords
{
    protected static string $resource = SeatTableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
