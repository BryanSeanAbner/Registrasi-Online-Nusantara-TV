<?php

namespace App\Filament\Resources\Event\Pages;

use App\Filament\Resources\Event\EventResource;
use App\Services\ShortLinkService;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $originalSlug = $this->record->slug ?? null;
        $newSlug = $data['slug'] ?? null;

        if ($originalSlug && $newSlug && $originalSlug !== $newSlug) {
            $svc = app(ShortLinkService::class);
            $longUrl = $svc->eventRegisterUrl($newSlug);
            $short = $svc->shortenTinyURL($longUrl);

            $data['short_link'] = $short ?: null;
        }

        return $data;
    }
}
