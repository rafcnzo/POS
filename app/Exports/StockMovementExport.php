<?php
// app/Exports/StockMovementExport.php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Carbon\Carbon;

class StockMovementExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithDrawings,
    WithEvents
{
    protected $movements;
    protected $title;
    protected $storeInfo;
    protected $rowNumber = 0;

    public function __construct($movements, $title, $storeInfo = [])
    {
        $this->movements = $movements;
        $this->title = $title;
        $this->storeInfo = array_merge([
            'store_name' => 'Nama Toko Default',
            'store_phone' => '-',
            'store_address' => '-',
            'store_logo' => null,
            'report_period' => null,
        ], $storeInfo);
    }

    public function collection()
    {
        return $this->movements;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'No. Referensi',
            'Nama Item',
            'Tipe Item',
            'Jenis Mutasi',
            'Jumlah',
            'Keterangan',
        ];
    }

    public function map($movement): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            Carbon::parse($movement->movement_date)->format('d/m/Y H:i'),
            $movement->reference,
            $movement->name,
            $movement->item_type === 'ingredient' ? 'Bahan Baku' : 'FFNE',
            $movement->movement_direction === 'in' ? 'Masuk' : 'Keluar',
            ($movement->movement_direction === 'in' ? '+' : '-') . number_format($movement->quantity, 0, ',', '.'),
            $movement->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // biar heading di bawah gak ketimpa styling custom dari event
        return [];
    }

    public function title(): string
    {
        return $this->title ?: 'Laporan Mutasi Stok';
    }

    public function drawings()
    {
        if (empty($this->storeInfo['store_logo'])) {
            return [];
        }

        $logoPath = public_path('storage/' . ltrim($this->storeInfo['store_logo'], '/'));

        if (!file_exists($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo Toko');
        $drawing->setDescription('Logo Toko');
        $drawing->setPath($logoPath);
        $drawing->setHeight(75);        // sedikit lebih besar biar proporsional
        $drawing->setCoordinates('B2'); // mulai dari baris pertama
        $drawing->setOffsetX(50);        // geser sedikit dari tepi kiri
        $drawing->setOffsetY(-100);       // dinaikkan sedikit (nilai negatif = naik)

        return [$drawing];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Offset untuk header toko di atas tabel
                $headerRow = 6; // data mulai di baris 6
                $lastColumn = 'H';
                $highestRow = $sheet->getHighestRow() + $headerRow - 1;

                // Geser semua data ke bawah 5 baris agar header toko muat
                $sheet->insertNewRowBefore(1, 5);

                // Judul laporan besar
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->setCellValue('A1', strtoupper($this->title));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Info toko
                $sheet->setCellValue('C2', $this->storeInfo['store_name']);
                $sheet->setCellValue('C3', $this->storeInfo['store_address']);
                $sheet->setCellValue('C4', 'Telp: ' . $this->storeInfo['store_phone']);
                if ($this->storeInfo['report_period']) {
                    $sheet->setCellValue('C5', 'Periode: ' . $this->storeInfo['report_period']);
                }

                $sheet->getStyle('C2:C5')->applyFromArray([
                    'font' => ['size' => 11],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Style header tabel
                $headerRange = "A{$headerRow}:{$lastColumn}{$headerRow}";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DCE6F1']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Border untuk seluruh isi tabel
                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                // Rata kanan kolom jumlah
                $sheet->getStyle("G{$headerRow}:G{$highestRow}")
                      ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Freeze header tabel
                $sheet->freezePane("A" . ($headerRow + 1));

                // Auto filter
                $sheet->setAutoFilter($headerRange);
            },
        ];
    }
}
