<?php

namespace App\Filament\Resources\ScanResource\Pages;

use App\Filament\Resources\ScanResource\ScanResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class ListScansByEvent extends ListRecords
{
    protected static string $resource = ScanResource::class;

    public int|string $event;

    public function mount(): void
    {
        parent::mount();
        $this->event = (string) (request()->route('event'));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('registration', function (Builder $q) {
                    $q->where('event_id', $this->event);
                });
            });
    }
}
