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
    protected $photoPaths = [];

    public function __construct($registrations)
    {
        // Pastikan relasi untuk export sudah ter-load agar bisa deteksi tipe field
        $this->registrations = $registrations->load(['event', 'seatAssignment.seat', 'fieldValues.field']);
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
            'Foto',
            'Form Answer',
        ];
    }

    public function map($registration): array
    {
        $answers = [];
        $firstPhotoPath = null;
        foreach ($registration->fieldValues as $fieldValue) {
            $label = $fieldValue->field->label;
            $value = (string) $fieldValue->value;

            // Jika ini field image, simpan untuk kolom Foto dan JANGAN masukkan ke Form Answer text
            if (($fieldValue->field->type ?? null) === 'image' && $value !== '') {
                if (!$firstPhotoPath) {
                    $rel = ltrim($value, '/');
                    $full = storage_path('app/public/' . $rel);
                    if (is_file($full)) {
                        $firstPhotoPath = $full;
                    }
                }
                continue;
            }

            // Kumpulkan jawaban teks selain image
            $answers[] = $label . ': ' . $value;
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

        // simpan path foto untuk digunakan di drawings()
        if ($firstPhotoPath) {
            $this->photoPaths[$registration->id] = $firstPhotoPath;
        }

        return [
            optional($registration->event)->title ?? '-',
            $registration->code ?? '-',
            '', 
            ucfirst($registration->status ?? '-'),
            $registration->checked_in_at ? $registration->checked_in_at->format('d M Y H:i') : 'Belum',
            optional($registration->seatAssignment?->seat)->label ?? '-',
            optional($registration->created_at)->format('d M Y H:i') ?? '-',
            '', // Kolom Foto akan diisi via drawings()
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

            if (isset($this->photoPaths[$registration->id])) {
                $p = new Drawing();
                $p->setName('Foto');
                $p->setDescription('Foto');
                $p->setPath($this->photoPaths[$registration->id]);
                $p->setHeight(80);
                // Kolom Foto = H (setelah menambah 1 kolom sebelum Form Answer)
                $p->setCoordinates('H' . $row);
                $drawings[] = $p;
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
            'H' => 18, // foto
            'I' => 50, // form answer
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);
        $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:I1')->getAlignment()->setVertical('center');

        $sheet->getStyle('A2:G' . ($this->registrations->count() + 1))
            ->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A2:I' . ($this->registrations->count() + 1))
            ->getAlignment()->setVertical('center');

        $sheet->getStyle('I')->getAlignment()->setWrapText(true);
        $sheet->getStyle('I')->getAlignment()->setHorizontal('left');

        foreach (range(2, $this->registrations->count() + 1) as $row) {
            $sheet->getRowDimension($row)->setRowHeight(85);
        }

        return [];
    }
}
