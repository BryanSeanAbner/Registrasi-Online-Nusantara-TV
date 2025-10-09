<?php

namespace App\Filament\Resources\ScanResource\Pages;

use App\Filament\Resources\ScanResource\ScanResource;
use Filament\Resources\Pages\ListRecords;

class ListScans extends ListRecords
{
    protected static string $resource = ScanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
