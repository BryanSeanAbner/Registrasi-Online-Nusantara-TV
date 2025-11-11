<?php

namespace App\Filament\Resources\FormField\Pages;

use App\Filament\Resources\FormField\FormFieldResource;
use App\Models\FormField as FormFieldModel;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;

class EditFormField extends EditRecord
{
    protected static string $resource = FormFieldResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        $oldEvent = (int) $record->event_id;
        $oldOrder = (int) $record->sort_order;

        $newEvent = (int) ($data['event_id'] ?? $oldEvent);
        $desired = max(1, (int) ($data['sort_order'] ?? $oldOrder));

        if ($newEvent === $oldEvent) {
            $others = (int) FormFieldModel::where('event_id', $oldEvent)
                ->where('id', '!=', $record->id)
                ->count();
            $desired = min($desired, $others + 1);
        } else {
            $countTarget = (int) FormFieldModel::where('event_id', $newEvent)->count();
            $desired = min($desired, $countTarget + 1);
        }

        DB::transaction(function () use ($oldEvent, $oldOrder, $newEvent, $desired, $record) {
            if ($newEvent === $oldEvent) {
                if ($desired < $oldOrder) {
                    FormFieldModel::where('event_id', $oldEvent)
                        ->where('id', '!=', $record->id)
                        ->whereBetween('sort_order', [$desired, $oldOrder - 1])
                        ->increment('sort_order');
                } elseif ($desired > $oldOrder) {
                    FormFieldModel::where('event_id', $oldEvent)
                        ->where('id', '!=', $record->id)
                        ->whereBetween('sort_order', [$oldOrder + 1, $desired])
                        ->decrement('sort_order');
                }
            } else {
                FormFieldModel::where('event_id', $oldEvent)
                    ->where('sort_order', '>', $oldOrder)
                    ->decrement('sort_order');

                FormFieldModel::where('event_id', $newEvent)
                    ->where('sort_order', '>=', $desired)
                    ->increment('sort_order');
            }
        });

        $data['sort_order'] = $desired;
        return $data;
    }
}
