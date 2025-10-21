<?php

namespace App\Exports;

use App\Models\Sale;
use App\Models\Payroll;
use App\Models\SaleItem;
use App\Models\EnergyCost;
use App\Models\Extra;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitAndLossExport implements FromCollection, WithHeadings, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): Collection
    {
        // 1. Kalkulasi semua metrik seperti di controller
        $totalRevenue = Sale::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'completed')->sum('total_amount');

        $saleItems = SaleItem::whereHas('sale', function ($query) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate])
                  ->where('status', 'completed');
        })->with('menuItem.ingredients')->get();

        $totalHpp = $saleItems->sum(function ($item) {
            if (!$item->menuItem) return 0;
            return $item->menuItem->getCostPrice() * $item->quantity;
        });

        $totalPayroll = Payroll::whereBetween('tanggal_pembayaran', [$this->startDate, $this->endDate])
            ->where('status_pembayaran', 'dibayar')->sum('nominal_gaji');

        $totalEnergyCost = EnergyCost::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('cost');
        $totalExtrasCost = Extra::whereBetween('created_at', [$this->startDate, $this->endDate])->sum('harga');

        $totalOperationalExpenses = $totalPayroll + $totalEnergyCost + $totalExtrasCost;
        $grossProfit = $totalRevenue - $totalHpp;
        $netProfit   = $grossProfit - $totalOperationalExpenses;

        // 2. Susun data menjadi koleksi (Collection) untuk Excel
        return new Collection([
            ['Keterangan' => 'Total Pendapatan', 'Jumlah' => $totalRevenue],
            ['Keterangan' => '(-) Total HPP (Harga Pokok Penjualan)', 'Jumlah' => $totalHpp],
            ['Keterangan' => 'Laba Kotor', 'Jumlah' => $grossProfit],
            ['Keterangan' => '', 'Jumlah' => ''], // Baris kosong sebagai pemisah
            ['Keterangan' => 'Beban Operasional:', 'Jumlah' => ''],
            ['Keterangan' => '  - Beban Gaji (Payroll)', 'Jumlah' => $totalPayroll],
            ['Keterangan' => '  - Beban Energi', 'Jumlah' => $totalEnergyCost],
            ['Keterangan' => '  - Beban Tambahan (Extras)', 'Jumlah' => $totalExtrasCost],
            ['Keterangan' => 'Total Beban Operasional', 'Jumlah' => $totalOperationalExpenses],
            ['Keterangan' => '', 'Jumlah' => ''], // Baris kosong
            ['Keterangan' => 'Laba Bersih', 'Jumlah' => $netProfit],
        ]);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ini akan menjadi header kolom di file Excel
        return [
            'Keterangan',
            'Jumlah',
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Membuat baris header menjadi Bold
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);

        // Membuat baris Laba Kotor dan Laba Bersih menjadi Bold
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('B3')->getFont()->setBold(true);
        $sheet->getStyle('A11')->getFont()->setBold(true);
        $sheet->getStyle('B11')->getFont()->setBold(true);

        // Mengatur lebar kolom
        $sheet->getColumnDimension('A')->setWidth(45);
        $sheet->getColumnDimension('B')->setWidth(25);

        // Format kolom 'Jumlah' sebagai angka dengan pemisah ribuan
        $sheet->getStyle('B2:B11')->getNumberFormat()->setFormatCode('#,##0');
    }
}
