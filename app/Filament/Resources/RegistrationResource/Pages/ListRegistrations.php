<?php

namespace App\Filament\Resources\RegistrationResource\Pages;

use App\Filament\Resources\RegistrationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
{
    return [
        Action::make('status_wa')
            ->label('Status WA')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->button()
            ->color('warning')
            ->modalHeading('Daftar Failed Jobs WA')
            ->modalWidth('3xl')
            ->modalContent(function () {
                $failed = DB::table('failed_jobs')->orderBy('failed_at')->take(10)->get();
                return view('filament.components.failed-jobs-list', [
                    'failed' => $failed,
                ]);
            }),
    ];
}
}