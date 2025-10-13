<?php

namespace App\Filament\Resources\FormField\Pages;

use App\Filament\Resources\FormField\FormFieldResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListFormFields extends ListRecords
{
    protected static string $resource = FormFieldResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

