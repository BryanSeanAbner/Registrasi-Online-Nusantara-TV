<?php

namespace App\Exports;

use App\Models\Registration;
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

    public function __construct($registrations)
    {
        $this->registrations = $registrations;
    }

    public function collection()
    {
        return $this->registrations;
    }

    public function headings(): array
    {
        return [
            'Event',
            'Code',
            'QR Code', 
            'Status',
            'Check-in',
            'Kursi',
            'Created at',
            'Form Answer',
        ];
    }

    public function map($registration): array
    {
        $answers = [];
        foreach ($registration->fieldValues as $fieldValue) {
            $answers[] = $fieldValue->field->label . ': ' . $fieldValue->value;
        }

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

        return [
            optional($registration->event)->title ?? '-',
            $registration->code ?? '-',
            '', 
            ucfirst($registration->status ?? '-'),
            $registration->checked_in_at ? $registration->checked_in_at->format('d M Y H:i') : 'Belum',
            optional($registration->seatAssignment?->seat)->label ?? '-',
            optional($registration->created_at)->format('d M Y H:i') ?? '-',
            implode("\n", $answers),
        ];
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
        return [
            'A' => 18, 
            'B' => 25, 
            'C' => 20, 
            'D' => 15, 
            'E' => 15, 
            'F' => 10, 
            'G' => 20, 
            'H' => 50, 
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:H1')->getAlignment()->setVertical('center');

        $sheet->getStyle('A2:G' . ($this->registrations->count() + 1))
            ->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A2:H' . ($this->registrations->count() + 1))
            ->getAlignment()->setVertical('center');

        $sheet->getStyle('H')->getAlignment()->setWrapText(true);
        $sheet->getStyle('H')->getAlignment()->setHorizontal('left');

        foreach (range(2, $this->registrations->count() + 1) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(85);
        }

        return [];
    }
}
