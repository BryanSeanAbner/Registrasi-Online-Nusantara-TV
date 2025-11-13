<?php
namespace App\Exports;

use App\Models\FormField;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrationsExport implements FromCollection, WithHeadings, WithMapping, WithDrawings, WithColumnWidths, WithStyles
{
    protected $registrations;
    protected $qrPaths = [];
    protected $fields;

    public function __construct($registrations)
    {
        $this->registrations = $registrations;
        $this->fields = $this->resolveFields();
    }

    public function collection()
    {
        return $this->registrations;
    }

    public function headings(): array
    {
        $base = [
            'Event',
            'Code',
            'QR Code',
            'Status',
            'Check-in',
            'Kursi',
            'Created at',
        ];

        $dynamic = $this->fields->pluck('label')->all();

        return array_merge($base, $dynamic);
    }

    public function map($registration): array
    {
        if ($registration->code) {
            $dir = storage_path('app/temp_qr');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $filePath = $dir . '/' . $registration->code . '.png';
            QrCode::format('png')
                ->size(150)
                ->generate(route('ticket.show', ['code' => $registration->code]), $filePath);

            $this->qrPaths[$registration->id] = $filePath;
        }

        $row = [
            optional($registration->event)->title ?? '-',
            $registration->code ?? '-',
            '',
            ucfirst($registration->status ?? '-'),
            $registration->checked_in_at ? $registration->checked_in_at->format('d M Y H:i') : 'Belum',
            optional($registration->seatAssignment?->seat)->label ?? '-',
            optional($registration->created_at)->format('d M Y H:i') ?? '-',
        ];

        // Dynamic field columns
        foreach ($this->fields as $field) {
            $fv = $registration->fieldValues->firstWhere('field_id', $field->id);
            if (!$fv) {
                $row[] = '';
                continue;
            }

            $value = $fv->value;

            if ($value === null || $value === '') {
                $json = $fv->value_json ?? null;
                if (is_array($json)) {
                    $row[] = implode(', ', array_map('strval', $json));
                    continue;
                }

                if ($fv->value_number !== null) {
                    $row[] = (string) $fv->value_number;
                    continue;
                }
            }

            $row[] = (string) ($value ?? '');
        }

        return $row;
    }

    public function drawings()
    {
        $drawings = [];
        $row = 2; 

        foreach ($this->registrations as $registration) {
            if (isset($this->qrPaths[$registration->id])) {
                $drawing = new Drawing();
                $drawing->setName('QR Code');
                $drawing->setDescription('QR Code');
                $drawing->setPath($this->qrPaths[$registration->id]);
                $drawing->setHeight(80);
                $drawing->setCoordinates('C' . $row); 
                $drawings[] = $drawing;
            }
            $row++;
        }

        return $drawings;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 18,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 10,
            'G' => 20,
        ];

        $startIndex = 8; // H
        $count = $this->fields->count();
        for ($i = 0; $i < $count; $i++) {
            $letter = $this->columnLetter($startIndex + $i);
            $widths[$letter] = 30;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $lastIndex  = 7 + $this->fields->count();
        $lastLetter = $this->columnLetter($lastIndex);

        $sheet->getStyle('A1:' . $lastLetter . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $lastLetter . '1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:' . $lastLetter . '1')->getAlignment()->setVertical('center');

        $sheet->getStyle('A2:G' . ($this->registrations->count() + 1))
            ->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A2:' . $lastLetter . ($this->registrations->count() + 1))
            ->getAlignment()->setVertical('center');

        // Dynamic columns (H..last): wrap + left align
        $startIndex = 8; // H
        for ($i = $startIndex; $i <= $lastIndex; $i++) {
            $letter = $this->columnLetter($i);
            $sheet->getStyle($letter)->getAlignment()->setWrapText(true);
            $sheet->getStyle($letter)->getAlignment()->setHorizontal('left');
        }

        foreach (range(2, $this->registrations->count() + 1) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(85);
        }

        return [];
    }

    protected function resolveFields()
    {
        $eventIds = collect($this->registrations)->pluck('event_id')->filter()->unique()->values();

        $query = FormField::query();
        if ($eventIds->isNotEmpty()) {
            $query->whereIn('event_id', $eventIds);
        }

        return $query->orderBy('sort_order')->orderBy('id')->get();
    }

    protected function columnLetter(int $index): string
    {
        // 1 -> A, 2 -> B, ... 26 -> Z, 27 -> AA, etc.
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26);
        }
        return $letter;
    }
}
