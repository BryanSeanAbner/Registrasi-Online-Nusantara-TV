<?php

namespace App\Filament\Resources\Registration\Tables;

use App\Models\Registration;
use App\Models\FormField;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class RegistrationsTableExporter extends Exporter
{
    protected static ?string $model = Registration::class;

    public static function getColumns(): array
    {
        $columns = [
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
        ];

        $activeEventId = session('active_event_id');

        $fieldsQuery = FormField::query();
        if ($activeEventId) {
            $fieldsQuery->where('event_id', $activeEventId);
        }

        $fields = $fieldsQuery->orderBy('sort_order')->orderBy('id')->get();

        foreach ($fields as $field) {
            $columns[] = ExportColumn::make('field_' . $field->id)
                ->label($field->label)
                ->state(function (Registration $record) use ($field): string {
                    $fv = $record->fieldValues->firstWhere('field_id', $field->id);

                    if (!$fv) {
                        return '';
                    }

                    $value = $fv->value;

                    if ($value === null || $value === '') {
                        $json = $fv->value_json ?? null;
                        if (is_array($json)) {
                            return implode(', ', array_map('strval', $json));
                        }

                        if ($fv->value_number !== null) {
                            return (string) $fv->value_number;
                        }
                    }

                    return (string) ($value ?? '');
                });
        }

        return $columns;
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
