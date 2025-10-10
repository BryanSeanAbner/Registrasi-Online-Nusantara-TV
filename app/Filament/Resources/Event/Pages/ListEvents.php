<?php

namespace App\Filament\Resources\Event\Pages;

use App\Filament\Resources\Event\EventResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
