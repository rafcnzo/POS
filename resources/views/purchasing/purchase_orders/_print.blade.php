<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            padding: 15px;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header Section */
        .header-table {
            margin-bottom: 0;
        }

        .header-table td {
            padding: 5px 10px;
            border: 1px solid #000;
            vertical-align: top;
        }

        .company-info {
            width: 55%;
        }

        .company-info strong {
            font-size: 11px;
        }

        .po-title-cell {
            width: 45%;
            text-align: center;
            vertical-align: middle;
            border-left: 1px solid #000;
        }

        .po-title {
            font-size: 32px;
            font-weight: bold;
            color: #6495ED;
            letter-spacing: 3px;
        }

        .date-po-info {
            padding: 8px 10px;
            border: 1px solid #000;
            border-top: none;
        }

        .date-po-table {
            float: right;
            border-collapse: collapse;
        }

        .date-po-table td {
            padding: 3px 10px;
            border: 1px solid #000;
            font-size: 10px;
        }

        .date-po-table .label-cell {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }

        /* Vendor and Ship To Section */
        .vendor-ship-table {
            margin-top: 0;
        }

        .vendor-ship-table td {
            padding: 6px 10px;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 10px;
        }

        .section-header {
            background-color: #5B8ED6;
            color: white;
            font-weight: bold;
            padding: 4px 10px !important;
            text-align: left;
            font-size: 10px;
        }

        .vendor-section {
            width: 50%;
        }

        .ship-section {
            width: 50%;
        }

        /* Shipping Details Section */
        .shipping-details-table {
            margin-top: 0;
        }

        .shipping-details-table td {
            padding: 4px 10px;
            border: 1px solid #000;
            font-size: 10px;
            text-align: left;
        }

        .shipping-header {
            background-color: #5B8ED6;
            color: white;
            font-weight: bold;
            text-align: left;
            width: 25%;
        }

        /* Items Table */
        .items-table {
            margin-top: 0;
        }

        .items-table th {
            background-color: #5B8ED6;
            color: white;
            font-weight: bold;
            padding: 4px 8px;
            border: 1px solid #000;
            text-align: left;
            font-size: 10px;
        }

        .items-table td {
            padding: 4px 8px;
            border: 1px solid #000;
            font-size: 10px;
        }

        .item-number {
            width: 12%;
            text-align: left;
        }

        .description {
            width: 37%;
            text-align: left;
        }

        .qty {
            width: 10%;
            text-align: center;
        }

        .unit-price {
            width: 15%;
            text-align: right;
        }

        .total {
            width: 15%;
            text-align: right;
        }

        .empty-row td {
            height: 18px;
            padding: 4px 8px;
        }

        .dash-value {
            text-align: center;
        }

        /* Comments Section */
        .comments-total-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }

        .comments-section {
            width: 100%;
            border-collapse: collapse;
        }

        .comments-section td {
            padding: 4px 10px;
            border: 1px solid #000;
            vertical-align: top;
            font-size: 10px;
        }

        .comments-header {
            background-color: #BEBEBE;
            font-weight: bold;
        }

        .comments-cell {
            height: 70px;
            vertical-align: top;
        }

        /* Totals Section */
        .totals-section {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-section td {
            padding: 3px 10px;
            border: 1px solid #000;
            font-size: 10px;
        }

        .totals-label {
            text-align: right;
            font-weight: bold;
            width: 50%;
        }

        .totals-value {
            text-align: right;
            width: 50%;
        }

        .grand-total-row {
            font-weight: bold;
        }

        .grand-total-row .totals-label {
            font-size: 11px;
        }

        .grand-total-row .totals-value {
            font-size: 11px;
        }

        /* Footer */
        .footer-section {
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
            clear: both;
        }

        @media print {
            body {
                padding: 10px;
            }

            /* Force background colors to print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .section-header,
            .shipping-header,
            .items-table th,
            .comments-header {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>
    @php
        // Convert $setting (Eloquent collection) to key-value array
        $settings = [];
        foreach ($setting as $s) {
            $settings[$s->key] = $s->value;
        }
    @endphp

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="company-info" rowspan="2">
                @if (!empty($settings['store_logo']))
                    <img src="{{ asset('storage/' . $settings['store_logo']) }}" alt="Logo"
                        style="height:40px; margin-bottom:4px;"><br>
                @endif
                <strong>{{ $settings['store_name'] ?? '[Company Name]' }}</strong><br>
                {{ $settings['store_address'] ?? '[Street Address]' }}<br>
                Phone: {{ $settings['store_phone'] ?? '[000] 000-0000' }}<br>
                @if (!empty($settings['store_website']))
                    Website: {{ $settings['store_website'] }}
                @else
                    Website:
                @endif
            </td>
            <td class="po-title-cell">
                <div class="po-title">PURCHASE ORDER</div>
            </td>
        </tr>
        <tr>
            <td class="date-po-info">
                <table class="date-po-table">
                    <tr>
                        <td class="label-cell">DATE</td>
                        <td>{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">PO #</td>
                        <td>{{ $purchaseOrder->po_number }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Vendor and Ship To Section -->
    <table class="vendor-ship-table">
        <tr>
            <td class="section-header vendor-section">VENDOR</td>
            <td class="section-header ship-section">SHIP TO</td>
        </tr>
        <tr>
            <td>
                {{ optional($purchaseOrder->supplier)->name ?? '[Company Name]' }}<br>
                {{ optional($purchaseOrder->supplier)->contact_person ?? '[Contact or Department]' }}<br>
                @if (optional($purchaseOrder->supplier)->address)
                    {{ $purchaseOrder->supplier->address }}<br>
                @else
                    [Street Address]<br>
                @endif

                @if (optional($purchaseOrder->supplier)->phone)
                    Phone: {{ $purchaseOrder->supplier->phone }}
                @else
                    Phone: [000] 000-0000
                @endif
            </td>
            <td>
                {{ optional($purchaseOrder->user)->name ?? '[Name]' }}<br>
                {{ $settings['store_name'] ?? '[Company Name]' }}<br>
                {{ $settings['store_address'] ?? '[Street Address]' }}<br>
                Phone: {{ optional($purchaseOrder->user)->telepon ?? '[Phone]' }}
            </td>
        </tr>
    </table>

    <!-- Shipping Details Section -->
    <table class="shipping-details-table">
        <tr>
            <td class="shipping-header">REQUISITIONER</td>
            <td class="shipping-header">SHIP VIA</td>
            <td class="shipping-header">F.O.B.</td>
            <td class="shipping-header">SHIPPING TERMS</td>
        </tr>
        <tr>
            <td>{{ optional($purchaseOrder->user)->name ?? '' }}</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="item-number">ITEM #</th>
                <th class="description">DESCRIPTION</th>
                <th class="qty">QTY</th>
                <th class="unit-price">UNIT PRICE</th>
                <th class="total">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
                $displayRows = 11;
                $rowCount = 0;
            @endphp
            @foreach ($purchaseOrder->items as $item)
                @php
                    $itemTotal = $item->quantity * $item->price;
                    $totalAmount += $itemTotal;
                    $rowCount++;
                @endphp
                <tr>
                    <td class="item-number">{{ $item->ingredient_id }}</td>
                    <td class="description">{{ optional($item->ingredient)->name ?? '' }}</td>
                    <td class="qty">
                        {{ number_format($item->quantity, 0) }} {{ optional($item->ingredient)->unit ?? '' }} 
                    </td>
                    <td class="unit-price">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="total">Rp {{ number_format($itemTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            @for ($i = $rowCount; $i < $displayRows; $i++)
                <tr class="empty-row">
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="dash-value">-</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Comments and Totals Section -->
    <table class="comments-total-wrapper">
        <tr>
            <td style="width: 60%; vertical-align: top; padding: 0; border: none;">
                <table class="comments-section">
                    <tr>
                        <td class="comments-header">Comments or Special Instructions</td>
                    </tr>
                    <tr>
                        <td class="comments-cell">
                            {{ $purchaseOrder->notes ?? '' }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%; vertical-align: top; padding: 0; border: none;">
                <table class="totals-section">
                    <tr>
                        <td class="totals-label">SUBTOTAL</td>
                        <td class="totals-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="totals-label">TAX</td>
                        <td class="totals-value dash-value"></td>
                    </tr>
                    <tr>
                        <td class="totals-label">SHIPPING</td>
                        <td class="totals-value dash-value"></td>
                    </tr>
                    <tr>
                        <td class="totals-label">OTHER</td>
                        <td class="totals-value dash-value"></td>
                    </tr>
                    <tr class="grand-total-row">
                        <td class="totals-label">TOTAL</td>
                        <td class="totals-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer-section">
        If you have any questions about this purchase order, please contact<br>
        {{ $purchaseOrder->user->name ?? '[Name]' }}
        @php
            $phone = $purchaseOrder->user->telepon ?? '[Phone #]';
            $email = $purchaseOrder->user->email ?? '[E-mail]';
        @endphp
        <br>
        {{ $phone }}
        <br>
        {{ $email }}
    </div>
</body>
<script>
    // Otomatis membuka dialog print saat halaman dimuat
    window.onload = function() {
        window.print();
    }
</script>

</html>
