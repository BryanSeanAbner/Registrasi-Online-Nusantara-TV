<?php

namespace App\Filament\Resources\FormField\Pages;

use App\Filament\Resources\FormField\FormFieldResource;
use App\Models\FormField as FormFieldModel;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;

class CreateFormField extends CreateRecord
{
    protected static string $resource = FormFieldResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $eventId = (int) ($data['event_id'] ?? 0);
        $desired = max(1, (int) ($data['sort_order'] ?? 1));

        if ($eventId > 0) {
            $count = (int) FormFieldModel::where('event_id', $eventId)->count();
            $desired = min($desired, $count + 1);

            DB::transaction(function () use ($eventId, $desired) {
                FormFieldModel::where('event_id', $eventId)
                    ->where('sort_order', '>=', $desired)
                    ->increment('sort_order');
            });
        }

        $data['sort_order'] = $desired;
        return $data;
    }
}
