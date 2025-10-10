<?php

namespace App\Filament\Resources\Event\Pages;

use App\Filament\Resources\Event\EventResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
