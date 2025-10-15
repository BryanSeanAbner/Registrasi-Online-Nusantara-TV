<?php

namespace App\Filament\Resources\Registration\Pages;

use App\Filament\Resources\Registration\RegistrationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListRegistrations extends ListRecords
{
    protected static string $resource = RegistrationResource::class;

    protected function getHeaderActions(): array
{
    return [
        Action::make('create_manual')
            ->label('Daftarkan Manual')
            ->icon('heroicon-o-user-plus')
            ->button()
            ->color('primary')
            ->url(fn () => static::getResource()::getUrl('create')),
        Action::make('status_wa')
            ->label('Status WA')
            ->icon('heroicon-o-chat-bubble-bottom-center-text')
            ->button()
            ->color('warning')
            ->modalHeading('Daftar Failed Jobs WA')
            ->modalWidth('3xl')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalContent(function () {
                $failed = DB::table('failed_jobs')->orderBy('failed_at', 'DESC')->take(10)->get();
                return view('filament.components.failed-jobs-list', [
                    'failed' => $failed,
                ]);
            }),
    ];
}
}
