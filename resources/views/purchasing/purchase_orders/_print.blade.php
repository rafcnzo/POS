<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Request - {{ $purchaseOrder->po_number ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 10mm 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            width: 100%;
            padding-left: 5mm;
            padding-right: 5mm;
            padding-top: 5mm;
            padding-bottom: 5mm;
        }

        /* Header Section */
        .header-container {
            width: 100%;
            margin-bottom: 2px;
            position: relative;
            height: 38px;
        }

        .logo-box {
            position: absolute;
            top: 0;
            left: 0;
            width: 38px;
            height: 38px;
        }

        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .company-name {
            position: absolute;
            top: 8px;
            left: 48px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: normal;
            line-height: 1.2;
            width: 60px;
            word-break: break-word;
        }

        .document-title {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .document-number {
            position: absolute;
            top: 6px;
            right: 0;
            margin-right: 50px;
            font-size: 7px;
        }

        .document-number strong {
            font-weight: bold;
            margin-right: 6px;
        }

        /* Main Table - All in One */
        .main-table {
            width: 96.5%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 10px;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            font-size: 7px;
            padding: 2px 3px;
            vertical-align: middle;
        }

        .main-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
            line-height: 1;
            padding: 1.5px 1.5px;
            font-size: 6px;
        }

        .info-row td {
            height: 14px;
            font-size: 7.5px;
        }

        .info-label {
            font-weight: bold;
            width: 11%;
            padding-left: 3px;
        }

        .info-value {
            padding-left: 3px;
        }

        .info-empty {
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
        }

        .item-row td {
            height: 13.5px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
            padding-right: 3px;
        }

        .total-row {
            background-color: #f5f5f5;
        }

        .total-row td {
            font-weight: bold;
            padding: 2px 3px;
            height: 14px;
            font-size: 6.5px;
        }

        .signature-table {
            border-collapse: collapse;
            width: 100%;
        }

        .signature-table td {
            border: 1px solid #000;
        }

        .signature-row-label td {
            height: 16px;
            vertical-align: top;
            padding: 2px;
            font-weight: bold;
            font-size: 7px;
            border-bottom: none;
        }

        .signature-row-value td {
            height: 22px;
            vertical-align: bottom;
            padding: 2px;
            font-size: 7px;
            line-height: 1.1;
            border-top: none;
            font-weight: bold;
        }

        .notes-section {
            width: 95.6%;
            border: 1px solid #000;
            padding: 2px 4px;
            height: 24px;
            margin-top: 2px;
            overflow: hidden;
        }

        .notes-label {
            font-weight: bold;
            font-size: 6.5px;
            margin-bottom: 1px;
        }

        .notes-content {
            font-size: 6px;
            line-height: 1.1;
        }
    </style>
</head>

<body>
    <div class="header-container">
        <div class="logo-box">
            @if (isset($settings['store_logo']) && file_exists(public_path('storage/' . $settings['store_logo'])))
                @php
                    $logoPath = public_path('storage/' . $settings['store_logo']);
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $logoExt = pathinfo($logoPath, PATHINFO_EXTENSION);
                    $logoMime = $logoExt === 'png' ? 'png' : 'jpeg';
                @endphp
                <img src="data:image/{{ $logoMime }};base64,{{ $logoData }}" alt="Logo">
            @endif
        </div>

        <div class="company-name">
            {{ strtoupper($settings['store_name'] ?? 'MIZUMI ONSEN') }}
        </div>

        <div class="document-title">
            PURCHASE REQUEST
        </div>

        <div class="document-number">
            <strong>No :</strong> {{ $purchaseOrder->po_number ?? 'PO-TEST-001' }}
        </div>
    </div>

    <table class="main-table">
        <tr class="info-row">
            <td class="info-label" colspan="1">Date of Request :</td>
            <td class="info-value" colspan="4">
                {{ $purchaseOrder->created_at ? $purchaseOrder->created_at->format('d/m/Y') : '' }}
            </td>
            <td class="info-empty" colspan="4" style="border: none;"></td>
        </tr>
        <tr class="info-row">
            <td class="info-label" rowspan="2">Departement :</td>
            <td class="info-value" colspan="4" style="height: 8px;"></td>
            <td class="info-empty" colspan="3" style="border: none;"></td>
        </tr>
        <tr class="info-row">
            <td class="info-value" colspan="4" style="height: 8px;"></td>
            <td class="info-empty" colspan="3" style="border: none;"></td>
        </tr>
        <tr>
            <th style="width: 2.5%;">No.</th>
            <th style="width: 5%;">Qty<br>Required</th>
            <th style="width: 3.5%;">Unit</th>
            <th style="width: 5%;">Stock on<br>Hand</th>
            <th style="width: 44%;">Article & Description</th>
            <th style="width: 7.5%;">Unit<br>Price</th>
            <th style="width: 7.5%;">Amount</th>
            <th style="width: 10%;">Vendor</th>
            <th style="width: 15%;">Notes</th>
        </tr>

        @php $totalAmount = 0; @endphp
        @forelse($purchaseOrder->items as $index => $item)
            @if ($index < 10)
                <tr class="item-row">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->quantity ?? '' }}</td>
                    <td class="text-center">{{ $item->ingredient->unit ?? '' }}</td>
                    <td class="text-center">{{ $item->stock_on_hand ?? '' }}</td>
                    <td style="padding-left: 2px;">{{ $item->ingredient->name ?? ($item->description ?? '') }}</td>
                    <td class="text-right">{{ $item->unit_price ? number_format($item->unit_price, 0, ',', '.') : '' }}
                    </td>
                    <td class="text-right">
                        {{ $item->quantity && $item->unit_price ? number_format($item->quantity * $item->unit_price, 0, ',', '.') : '' }}
                    </td>
                    <td class="text-center">{{ $purchaseOrder->supplier->name ?? '' }}</td>
                    <td style="padding-left: 2px; font-size: 5.5px;">{{ $item->notes ?? '' }}</td>
                </tr>
                @php $totalAmount += $item->quantity * $item->unit_price; @endphp
            @endif
        @empty
        @endforelse

        @for ($i = count($purchaseOrder->items); $i < 10; $i++)
            <tr class="item-row">
                <td class="text-center">{{ $i + 1 }}</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        @endfor

        <tr class="total-row">
            <td colspan="6" class="text-right" style="border-right: none;">Total</td>
            <td class="text-right" style="border-right: none;">
                {{ $totalAmount > 0 ? number_format($totalAmount, 0, ',', '.') : '' }}
            </td>
            <td colspan="2" style="border-left: none;">&nbsp;</td>
        </tr>

        <tr class="signature-row-label">
            <td colspan="2">Request By :</td>
            <td colspan="2">&nbsp;</td>
            <td colspan="1">Check By :</td>
            <td colspan="1">&nbsp;</td>
            <td colspan="2">Approved By :</td>
            <td colspan="1">&nbsp;</td>
        </tr>

        <tr class="signature-row-value">
            <td colspan="2">
                Date :<br>
                {{ $purchaseOrder->created_at ? $purchaseOrder->created_at->format('d/m/Y') : '' }}<br>
                {{ $purchaseOrder->user->name ?? 'Kasir Test' }}
            </td>
            <td colspan="2">&nbsp;</td>
            <td colspan="1">Date :</td>
            <td colspan="1">&nbsp;</td>
            <td colspan="2">Date :</td>
            <td colspan="1">&nbsp;</td>
        </tr>
    </table>

    <div class="notes-section">
        <div class="notes-label">Catatan :</div>
        <div class="notes-content">{{ $purchaseOrder->notes ?? '' }}</div>
    </div>
</body>

</html>
