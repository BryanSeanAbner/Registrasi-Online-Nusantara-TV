<?php

namespace App\Filament\Resources\Registration\Tables;

use App\Models\Registration;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class RegistrationsTableExporter extends Exporter
{
    protected static ?string $model = Registration::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('event.title')
                ->label('Event'),
            ExportColumn::make('code')
                ->label('Kode'),
            ExportColumn::make('status')
                ->label('Status'),
            ExportColumn::make('seatAssignment.seat.label')
                ->label('Kursi'),
            ExportColumn::make('checked_in_at')
                ->label('Check-in'),
            ExportColumn::make('approver.name')
                ->label('Disetujui Oleh'),
            ExportColumn::make('created_at')
                ->label('Tanggal Daftar'),
            ExportColumn::make('form_answers')
                ->label('Form Jawaban')
                ->state(function (Registration $record): string {
                    $answers = [];
                    foreach ($record->fieldValues as $fieldValue) {
                        $answers[] = $fieldValue->field->label . ': ' . $fieldValue->value;
                    }
                    return implode("\n", $answers);
                }),
        ];
    }

    public function getFileName(Export $export): string
    {
        return 'Daftar Registrasi - ' . now()->format('d M Y');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return 'Export data registrasi telah selesai dan siap untuk diunduh.';
    }
}