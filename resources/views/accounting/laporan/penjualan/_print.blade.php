<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle ?? 'Laporan Penjualan' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            background: white;
            color: #000;
            line-height: 1.4;
        }

        @page {
            size: A4 portrait;
            margin: 25mm 15mm 25mm 15mm;
        }

        body {
            padding: 0;
            margin: 0;
        }

        .page {
            width: 100%;
            max-width: 180mm;
            margin: 25px auto 0 auto;
        }

        /* ===== HEADER ===== */
        .header {
            text-align: center;
            margin-bottom: 22px;
            padding-bottom: 16px;
            border-bottom: 2px solid #000;
        }

        .header h2 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 9pt;
            margin: 4px 0;
            line-height: 1.3;
        }

        .header h3 {
            font-size: 13pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 0;
            text-transform: uppercase;
        }

        /* ===== META INFO ===== */
        .meta-info {
            margin-bottom: 18px;
            font-size: 9pt;
            line-height: 1.5;
        }

        .meta-info-left {
            text-align: left;
        }

        /* ===== DATA TABLE ===== */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            font-size: 8pt;
        }

        table.data-table th,
        table.data-table td {
            border: 0.5pt solid #333;
            padding: 4px 3px;
            vertical-align: top;
        }

        table.data-table th {
            background: #e0e0e0;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
            padding: 8px 3px;
        }

        table.data-table tbody tr {
            page-break-inside: avoid;
        }

        table.data-table tfoot td {
            font-weight: bold;
            background: #f0f0f0;
            padding: 8px 3px;
            border-top: 1.5pt solid #000;
        }

        /* Column widths */
        .col-no {
            width: 6%;
            text-align: center;
        }

        .col-date {
            width: 10%;
            text-align: center;
        }

        .col-trx {
            width: 14%;
        }

        .col-item {
            width: 35%;
        }

        .col-qty {
            width: 8%;
            text-align: center;
        }

        .col-price {
            width: 13%;
            text-align: right;
        }

        .col-subtotal {
            width: 14%;
            text-align: right;
        }

        /* ===== ITEM DETAILS ===== */
        .item-name {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        .item-modifier {
            font-size: 7pt;
            font-style: italic;
            color: #555;
            display: block;
            padding-left: 8px;
            margin-top: 3px;
        }

        .item-notes {
            font-size: 7pt;
            font-style: italic;
            color: #666;
            display: block;
            padding-left: 8px;
            margin-top: 5px;
        }

        /* ===== SUMMARY TABLE ===== */
        .summary-table {
            width: 100%;
            margin-bottom: 22px;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .summary-table td {
            padding: 8px 8px;
            border: 0.5pt solid #999;
        }

        .summary-table .label {
            font-weight: bold;
            width: 70%;
            background: #f5f5f5;
        }

        .summary-table .value {
            text-align: right;
            background: #f5f5f5;
            width: 30%;
        }

        .summary-table .total-row .label,
        .summary-table .total-row .value {
            background: #e0e0e0;
            border-top: 1.5pt solid #000;
            font-weight: bold;
            font-size: 10pt;
            padding: 10px 8px;
        }

        /* ===== SIGNATURE SECTION ===== */
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 33.33%;
            padding: 0 5px;
            vertical-align: top;
        }

        .signature-box {
            text-align: center;
            font-size: 8pt;
        }

        .signature-box .title {
            margin-bottom: 50px;
            line-height: 1.3;
            font-weight: bold;
        }

        .signature-box .name {
            margin-top: 14px;
            padding-top: 5px;
            font-weight: bold;
            font-size: 8pt;
        }

        .signature-box .position {
            font-size: 7pt;
            color: #666;
            margin-top: 4px;
        }

        /* ===== FOOTER ===== */
        .footer-note {
            margin-top: 22px;
            font-size: 7pt;
            font-style: italic;
            color: #666;
            text-align: center;
            border-top: 0.5pt solid #ccc;
            padding-top: 12px;
            page-break-inside: avoid;
        }

        /* ===== TEXT ALIGNMENT ===== */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        /* ===== PRINT OPTIMIZATION ===== */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page {
                padding: 0;
                box-shadow: none;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h2>{{ $settings['store_name'] ?? 'NAMA PERUSAHAAN' }}</h2>
            <p>{{ $settings['store_address'] ?? 'Alamat Perusahaan' }}</p>
            <p>Telp: {{ $settings['store_phone'] ?? '-' }}</p>
            <h3>{{ $reportTitle }}</h3>
        </div>

        <!-- Meta Information -->
        <div class="meta-info">
            <div class="meta-info-left">
                <strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }}<br>
                <strong>Dicetak Oleh:</strong> {{ auth()->user()->name ?? 'Admin' }}
            </div>
        </div>

        <!-- Data Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-date">Tanggal</th>
                    <th class="col-trx">No. Transaksi</th>
                    <th class="col-item">Nama Menu</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-price">Harga Jual</th>
                    <th class="col-subtotal">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                    $grandTotal = 0;
                @endphp
                @forelse($sales as $sale)
                    @foreach ($sale->items as $item)
                        @php
                            $grandTotal += $item->subtotal;
                        @endphp
                        <tr>
                            <td class="col-no">{{ $no++ }}</td>
                            <td class="col-date">{{ $sale->created_at->format('d/m/Y') }}</td>
                            <td class="col-trx">{{ $sale->transaction_code }}</td>
                            <td class="col-item">
                                <span class="item-name">{{ $item->menuItem->name ?? '-' }}</span>
                                @if ($item->selectedModifiers->isNotEmpty())
                                    @foreach ($item->selectedModifiers as $mod)
                                        <span class="item-modifier">+ {{ $mod->modifier->name }}</span>
                                    @endforeach
                                @endif
                                @if ($item->notes)
                                    <span class="item-notes">Cat: {{ $item->notes }}</span>
                                @endif
                            </td>
                            <td class="col-qty">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                            <td class="col-price">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="col-subtotal">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data penjualan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right">TOTAL</td>
                    <td class="col-subtotal">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Summary Table -->
        <table class="summary-table">
            <tr>
                <td class="label">Total Transaksi</td>
                <td class="value">{{ number_format($summary->total_transactions, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Penjualan Kotor</td>
                <td class="value">Rp {{ number_format($summary->total_subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Diskon</td>
                <td class="value">- Rp {{ number_format($summary->total_discount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Total Pajak</td>
                <td class="value">Rp {{ number_format($summary->total_tax, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total Penjualan Bersih</td>
                <td class="value">Rp {{ number_format($summary->total_revenue, 0, ',', '.') }}</td>
            </tr>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-box">
                            <div class="title">Mengetahui<br>Manajer Penjualan</div>
                            <div class="name">____________________</div>
                            <div class="position">(Nama Lengkap)</div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-box">
                            <div class="title">Menyetujui<br>Direktur</div>
                            <div class="name">____________________</div>
                            <div class="position">(Nama Lengkap)</div>
                        </div>
                    </td>
                    <td>
                        <div class="signature-box">
                            <div class="title">Dibuat Oleh<br>Staff Accounting</div>
                            <div class="name">____________________</div>
                            <div class="position">(Nama Lengkap)</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            * Laporan ini dicetak secara otomatis dan sah tanpa tanda tangan basah
        </div>
    </div>
</body>

</html>
