<?php

namespace App\Filament\Resources\Scan\Schemas;

use Filament\Schemas\Schema;

class ScanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // No form for scans
        ]);
    }
}
