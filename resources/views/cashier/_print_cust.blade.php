<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk POS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 10px;
            width: 80mm;
            margin: 0 auto;
            background: white;
        }

        .receipt {
            border: 1px solid #000;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: #000;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            font-weight: bold;
            overflow: hidden;
        }

        .logo img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .company-info {
            font-size: 11px;
            line-height: 1.3;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .info-section {
            margin-bottom: 8px;
        }

        .info-row {
            display: flex;
            margin-bottom: 2px;
        }

        .info-label {
            width: 80px;
            display: inline-block;
        }

        .info-value {
            flex: 1;
        }

        .type-badge {
            float: right;
            font-weight: bold;
            font-size: 14px;
        }

        .items-table {
            width: 100%;
            margin: 10px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .item-qty {
            width: 30px;
        }

        .item-name {
            flex: 1;
            padding: 0 10px;
            word-break: break-all;
        }

        .item-price {
            text-align: right;
            min-width: 70px;
        }

        .item-note {
            font-size: 10px;
            color: #888;
            margin-left: 40px;
            margin-bottom: 2px;
            font-style: italic;
        }

        .item-modifier {
            font-size: 11px;
            color: #555;
            margin-left: 40px;
            margin-bottom: 2px;
        }

        .summary {
            margin-top: 15px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .summary-label {
            font-weight: bold;
        }

        .summary-value {
            text-align: right;
            min-width: 70px;
        }

        .total-row {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }

        .total-row .summary-label,
        .total-row .summary-value {
            font-size: 14px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }

        .thank-you {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .custom-text {
            font-size: 10px;
            color: #666;
            white-space: pre-line;
        }

        @media print {
            body {
                width: 80mm;
            }

            .receipt {
                border: none;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        {{-- Header/Logo/Info --}}
        <div class="header">
            <div class="logo">
                @if (($settings['store_logo'] ?? null) && file_exists(public_path('storage/' . $settings['store_logo'])))
                    <img src="{{ asset('storage/' . $settings['store_logo']) }}" alt="Logo">
                @else
                    {{ mb_substr($settings['store_name'] ?? 'Toko', 0, 2) }}
                @endif
            </div>
            <div class="company-info">
                <div>{{ $settings['store_name'] ?? '-' }}</div>
                <div>{{ $settings['store_address'] ?? '-' }}</div>
                <div>{{ $settings['store_phone'] ?? '-' }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- Info Section --}}
        <div class="info-section">
            <div class="info-row">
                <span class="info-label">No #</span>
                <span class="info-value">: {{ $sale->transaction_code ?? '-' }}</span>
                <span class="type-badge">
                    @php
                        if (($sale->order_type ?? '') == 'dine_in') {
                            echo 'DINE-IN';
                        } elseif (($sale->order_type ?? '') == 'take_away') {
                            echo 'TAKE-AWAY';
                        } else {
                            echo strtoupper($sale->order_type ?? '-');
                        }
                    @endphp
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">No. Meja</span>
                <span class="info-value">: {{ $sale->table_number ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir</span>
                <span class="info-value">: {{ $sale->user->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span class="info-value">:
                    {{ isset($sale->created_at) ? $sale->created_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
            @if (!empty($sale->customer_name))
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value">: {{ $sale->customer_name }}</span>
                </div>
            @endif
            @if (!empty($sale->notes))
                <div class="info-row">
                    <span class="info-label">Catatan</span>
                    <span class="info-value">{{ $sale->notes }}</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Items --}}
        <div class="items-table">
            @foreach ($sale->items as $item)
                <div class="item-row">
                    <span class="item-qty">{{ $item->quantity }}</span>
                    <span class="item-name">{{ $item->menuItem->name ?? '-' }}</span>
                    <span class="item-price">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                {{-- Modifiers --}}
                @if ($item->selectedModifiers && count($item->selectedModifiers))
                    @foreach ($item->selectedModifiers as $modifier)
                        <div class="item-modifier">
                            + {{ $modifier->modifier->name ?? '-' }} @if ($modifier->price > 0)
                                ({{ number_format($modifier->price, 0, ',', '.') }})
                            @endif
                        </div>
                    @endforeach
                @endif
                {{-- Notes per item --}}
                @if (!empty($item->notes))
                    <div class="item-note">
                        Catatan: {{ $item->notes }}
                    </div>
                @endif
            @endforeach
        </div>

        {{-- Summary --}}
        <div class="summary">
            <div class="summary-row">
                <span class="summary-label">SUB TOTAL</span>
                <span class="summary-value">{{ number_format($sale->subtotal ?? 0, 0, ',', '.') }}</span>
            </div>
            {{-- Discount / tambahan: edit as needed --}}
            @if (isset($sale->discount_amount) && $sale->discount_amount > 0)
                <div class="summary-row">
                    <span class="summary-label">Diskon</span>
                    <span class="summary-value">-{{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            @if (isset($sale->additional) && $sale->additional > 0)
                <div class="summary-row">
                    <span class="summary-label">Tambahan</span>
                    <span class="summary-value">{{ number_format($sale->additional, 0, ',', '.') }}</span>
                </div>
            @endif
            @if (isset($sale->tax_amount) && $sale->tax_amount > 0)
                <div class="summary-row">
                    <span class="summary-label">Pajak</span>
                    <span class="summary-value">{{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                </div>
            @endif

            <div class="divider"></div>

            <div class="summary-row total-row">
                <span class="summary-label">TOTAL</span>
                <span class="summary-value">{{ number_format($sale->total_amount ?? 0, 0, ',', '.') }}</span>
            </div>
            {{-- Payments --}}
            @php
                $payment = $sale->payments->first();

                $cashReceived = $payment->cash_received ?? 0;
                $changeAmount = $payment->change_amount ?? 0;

                $tunai = $payment->payment_method == 'cash' ? $cashReceived : 0;
                $nontunai = $payment->payment_method != 'cash' ? $sale->total_amount : 0;
            @endphp
            <div class="summary-row">
                <span class="summary-label">Tunai</span>
                <span class="summary-value">{{ number_format($cashReceived, 0, ',', '.') }}</span>
            </div>
            @if ($nontunai > 0)
                <div class="summary-row">
                    <span class="summary-label">Non Tunai</span>
                    <span class="summary-value">{{ number_format($nontunai, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="summary-row">
                <span class="summary-label">Kembali</span>
                <span class="summary-value">{{ number_format($changeAmount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <div class="thank-you">
                {{ $settings['receipt_footer_title'] ?? 'TERIMAKASIH' }}
            </div>
            <div class="custom-text">
                {{ $settings['receipt_footer_text'] ?? 'Terima Kasih Atas Kunjungan Anda' }}
            </div>
        </div>
    </div>
</body>
<script>
    // Otomatis membuka dialog print saat halaman dimuat
    window.onload = function() {
        window.print();
    }
</script>

</html>
