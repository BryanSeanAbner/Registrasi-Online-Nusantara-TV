<?php

namespace App\Filament\Resources\ScanResource\Pages;

use App\Filament\Resources\ScanResource\ScanResource;
use App\Models\Event;
use Filament\Resources\Pages\Page;

class ChooseEvent extends Page
{
    protected static string $resource = ScanResource::class;

    protected string $view = 'filament.resources.scan-resource.pages.choose-event';

    public function getViewData(): array
    {
        return [
            'events' => Event::query()
                ->orderByDesc('starts_at')
                ->orderBy('title')
                ->get(['id', 'title', 'starts_at', 'ends_at', 'venue']),
        ];
    }
}
