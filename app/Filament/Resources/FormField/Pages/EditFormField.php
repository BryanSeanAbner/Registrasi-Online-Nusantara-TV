<?php

namespace App\Filament\Resources\FormField\Pages;

use App\Filament\Resources\FormField\FormFieldResource;
use Filament\Resources\Pages\EditRecord;

class EditFormField extends EditRecord
{
    protected static string $resource = FormFieldResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

