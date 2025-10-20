{{-- resources/views/accounting/laporan/stok/_print_mutasi.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            padding: 20px;
        }

        /* Header with Logo */
        .document-header {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        .header-left {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
        }
        .header-left img {
            max-width: 70px;
            max-height: 70px;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        .company-details {
            font-size: 8pt;
            color: #666;
            line-height: 1.5;
        }

        /* Report Title */
        .report-title {
            text-align: center;
            margin: 20px 0 15px 0;
        }
        .report-title h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #c41e3a;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .report-subtitle {
            font-size: 8pt;
            color: #666;
        }

        /* Filter Info Box */
        .filter-box {
            background: #f8f9fa;
            padding: 10px 15px;
            margin-bottom: 15px;
            border-left: 4px solid #007bff;
            font-size: 8pt;
        }
        .filter-box table {
            width: 100%;
            border: none;
        }
        .filter-box td {
            padding: 3px 5px;
            border: none;
            background: transparent;
        }
        .filter-box .label {
            font-weight: bold;
            width: 120px;
            color: #333;
        }
        .filter-box .value {
            color: #555;
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #495057;
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #333;
        }
        .data-table td {
            padding: 6px 5px;
            border: 1px solid #dee2e6;
            font-size: 8pt;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        /* Detail Row */
        .detail-row {
            background-color: #f0f0f0 !important;
        }
        .detail-row td {
            padding: 4px 8px !important;
            font-size: 7pt;
            border-top: none !important;
        }
        .detail-table {
            width: 100%;
            margin: 5px 0;
        }
        .detail-table td {
            padding: 2px 5px;
            border: none;
            background: transparent;
        }
        .detail-label {
            font-weight: bold;
            width: 100px;
        }

        /* Alignment */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
            white-space: nowrap;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .badge-in {
            background-color: #28a745;
            color: white;
        }
        .badge-out {
            background-color: #dc3545;
            color: white;
        }

        /* Text Colors */
        .text-success { color: #28a745; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
        .text-muted { color: #6c757d; }

        /* Footer */
        .document-footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            font-size: 8pt;
        }
        .footer-info {
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            width: 50%;
        }
        .footer-right {
            display: table-cell;
            width: 50%;
            text-align: right;
        }

        /* Signature */
        .signature-section {
            margin-top: 30px;
            text-align: right;
        }
        .signature-box {
            display: inline-block;
            text-align: center;
            min-width: 200px;
        }
        .signature-box p {
            margin-bottom: 60px;
            font-size: 9pt;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 9pt;
            font-weight: bold;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 10pt;
        }

        /* Page Settings */
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <!-- Document Header with Logo -->
    <div class="document-header">
        <div class="header-left">
            @if(isset($settings['store_logo']) && $settings['store_logo'])
                <img src="{{ public_path('storage/' . $settings['store_logo']) }}" alt="Logo">
            @else
                <div style="width: 70px; height: 70px; background: #f0f0f0; border: 2px solid #ddd; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #999;">
                    LOGO
                </div>
            @endif
        </div>
        <div class="header-right">
            <div class="company-name">{{ $settings['store_name'] ?? 'Nama Perusahaan' }}</div>
            <div class="company-details">
                {{ $settings['store_address'] ?? 'Alamat Perusahaan' }}<br>
                Telp: {{ $settings['store_phone'] ?? '-' }} | Email: {{ $settings['store_email'] ?? '-' }}
            </div>
        </div>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        <h1>Mutasi Stock</h1>
        <div class="report-subtitle">
            Periode: {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d F Y') }}
        </div>
    </div>

    <!-- Filter Information -->
    <div class="filter-box">
        <table>
            <tr>
                <td class="label">Jenis Mutasi:</td>
                <td class="value">
                    @if($filters['movement_type'] === 'in')
                        <strong>Barang Masuk</strong>
                    @elseif($filters['movement_type'] === 'out')
                        <strong>Barang Keluar</strong>
                    @else
                        <strong>Semua Mutasi</strong>
                    @endif
                </td>
                <td class="label">Filter Item:</td>
                <td class="value">
                    @if($filters['item_id'])
                        <strong>Item Tertentu</strong>
                    @else
                        <strong>Semua Item</strong>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 9%;">Tanggal</th>
                <th style="width: 11%;">No. Mutasi</th>
                <th style="width: 7%;">Jenis</th>
                <th style="width: 10%;">Gudang Asal</th>
                <th style="width: 10%;">Gudang Tujuan</th>
                <th style="width: 49%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentRef = null;
                $rowNum = 0;
            @endphp

            @forelse($movements as $i => $movement)
                @php
                    $isNewTransaction = $currentRef !== $movement->reference;
                    if ($isNewTransaction) {
                        $rowNum++;
                        $currentRef = $movement->reference;
                    }
                @endphp

                @if($isNewTransaction)
                <tr>
                    <td class="text-center">{{ $rowNum }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($movement->movement_date)->format('d/m/Y') }}</td>
                    <td class="text-center"><strong>{{ $movement->reference }}</strong></td>
                    <td class="text-center">
                        <span class="badge {{ $movement->movement_direction === 'in' ? 'badge-in' : 'badge-out' }}">
                            {{ $movement->movement_direction === 'in' ? 'IN' : 'OUT' }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($movement->movement_direction === 'in')
                            {{ $movement->description }}
                        @else
                            Gudang
                        @endif
                    </td>
                    <td class="text-center">
                        @if($movement->movement_direction === 'in')
                            Gudang
                        @else
                            {{ $movement->description }}
                        @endif
                    </td>
                    <td>{{ $movement->description }}</td>
                </tr>
                @endif

                <!-- Detail Row -->
                <tr class="detail-row">
                    <td colspan="7">
                        <table class="detail-table">
                            <tr>
                                <td class="detail-label">Kode Item:</td>
                                <td style="width: 10%;">{{ str_pad($movement->item_id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td class="detail-label">Nama Item:</td>
                                <td style="width: 25%;">{{ $movement->name }}</td>
                                <td class="detail-label">Qty:</td>
                                <td style="width: 8%;" class="text-right">
                                    @if($movement->movement_direction === 'in')
                                        <span class="text-success">{{ number_format($movement->quantity, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-danger">{{ number_format($movement->quantity, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="detail-label">Satuan:</td>
                                <td style="width: 8%;">
                                    <span class="badge {{ $movement->item_type === 'ingredient' ? 'badge-success' : 'badge-info' }}">
                                        {{ $movement->item_type === 'ingredient' ? 'PCS' : 'UNIT' }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        Tidak ada data mutasi stok untuk filter yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="document-footer">
        <div class="footer-info">
            <div class="footer-left">
                <strong>Total Transaksi:</strong> {{ count($movements->groupBy('reference')) }} transaksi<br>
                <strong>Total Item:</strong> {{ count($movements) }} item
            </div>
            <div class="footer-right">
                Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </div>
        </div>
    </div>

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-box">
            <p>Mengetahui,</p>
            <div class="signature-line">
                Manager/Pimpinan
            </div>
        </div>
    </div>
</body>
</html>
