<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal',
            'Waktu',
            'Kasir',
            'Subtotal',
            'Diskon',
            'Pajak',
            'Total Akhir',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->transaction_code,
            $sale->created_at->format('d-m-Y'),
            $sale->created_at->format('H:i:s'),
            $sale->user->name ?? '-',
            $sale->subtotal,
            $sale->discount_amount,
            $sale->tax_amount,
            $sale->total_amount,
        ];
    }
}
