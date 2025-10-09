<?php

namespace App\Filament\Resources\Scan\Pages;

use App\Filament\Resources\Scan\ScanResource;
use Filament\Resources\Pages\ListRecords;

class ListScans extends ListRecords
{
    protected static string $resource = ScanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
