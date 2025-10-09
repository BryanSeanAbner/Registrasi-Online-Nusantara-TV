<?php

namespace App\Filament\Resources\ScanResource\Schemas;

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

