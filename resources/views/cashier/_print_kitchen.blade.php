<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Struk Kitchen</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 3mm;
        }

        body {
            font-family: monospace;
            font-size: 12px;
            width: 74mm;
            margin: 0;
            padding: 2mm;
            line-height: 1.3;
        }

        .receipt {
            border: 2px solid black;
            padding: 5mm;
            margin-bottom: 5mm;
            page-break-after: always;
        }

        .header {
            border-bottom: 2px solid black;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }

        .order-type {
            font-size: 16px;
            font-weight: bold;
            background: black;
            color: white;
            padding: 2mm;
            display: inline-block;
        }

        .order-info {
            font-size: 10px;
            text-align: right;
            margin-top: 2mm;
        }

        .table-box {
            text-align: center;
            border: 2px solid black;
            padding: 3mm;
            margin: 3mm 0;
            background: #f0f0f0;
        }

        .table-label {
            font-size: 10px;
        }

        .table-number {
            font-size: 24px;
            font-weight: bold;
            margin-top: 1mm;
        }

        .item-qty {
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            margin: 3mm 0;
        }

        .item-name {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            border: 2px solid black;
            padding: 3mm;
            margin: 2mm 0;
        }

        .notes-box {
            background: #ffffcc;
            border: 1px solid black;
            padding: 2mm;
            margin: 2mm 0;
            font-size: 10px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .divider {
            border-top: 1px dashed black;
            margin: 3mm 0;
        }

        .customer-box {
            background: #f5f5f5;
            border: 1px solid #999;
            padding: 2mm;
            font-size: 10px;
            margin: 2mm 0;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            border-top: 1px solid black;
            padding-top: 2mm;
            margin-top: 3mm;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>
    @php
        use Carbon\Carbon;
        $items = $itemsToPrint ?? ($sale->items ?? []);
        $userName = $sale->user->name ?? '-';
        $orderTypeText = ($sale->order_type ?? 'dine_in') === 'take_away' ? 'TAKE AWAY' : 'DINE-IN';
        $showTable = strtolower($sale->order_type ?? 'dine_in') == 'dine_in' && !empty($sale->table_number);
        $showQueue = strtolower($sale->order_type ?? 'dine_in') == 'take_away' && !empty($sale->queue_number ?? null);
        $trxCode = $sale->transaction_code ?? '-';
        $custName = $sale->customer_name ?? '-';
    @endphp

    @forelse($items as $index => $item)
        <div class="receipt">
            <!-- HEADER -->
            <div class="header">
                <div class="order-type">{{ $orderTypeText }}</div>
                <div class="order-info">
                    #{{ $trxCode }}<br>
                    {{ Carbon::parse($sale->created_at ?? now())->format('d/m H:i') }}
                </div>
            </div>

            <!-- TABLE/QUEUE -->
            @if ($showTable)
                <div class="table-box">
                    <div class="table-label">MEJA</div>
                    <div class="table-number">{{ $sale->table_number }}</div>
                </div>
            @endif

            @if ($showQueue)
                <div class="table-box">
                    <div class="table-label">NO. ANTRIAN</div>
                    <div class="table-number">{{ $sale->queue_number }}</div>
                </div>
            @endif

            <!-- ITEM -->
            <div class="item-qty">{{ $item->quantity }}x</div>
            <div class="item-name">{{ strtoupper($item->menuItem->name ?? '-') }}</div>

            <!-- MODIFIERS -->
            @if ($item->selectedModifiers && $item->selectedModifiers->count())
                <div class="notes-box">
                    <div class="notes-title">MODIFIER:</div>
                    @foreach ($item->selectedModifiers as $mod)
                        - {{ $mod->modifier->name ?? '-' }}<br>
                    @endforeach
                </div>
            @endif

            <!-- NOTES -->
            @if (!empty($item->notes))
                <div class="notes-box">
                    <div class="notes-title">CATATAN:</div>
                    {{ $item->notes }}
                </div>
            @endif

            <div class="divider"></div>

            <!-- CUSTOMER INFO -->
            <div class="customer-box">
                <div>Pelanggan: {{ $custName }}</div>
                <div>Kasir: {{ $userName }}</div>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                {{ Carbon::parse($sale->created_at ?? now())->format('d/m/Y H:i:s') }}<br>
                Item {{ $index + 1 }}/{{ count($items) }}
            </div>
        </div>
    @empty
        <div style="text-align:center; padding:20px;">
            Tidak ada item untuk dicetak
        </div>
    @endforelse

    <script>
        // Small delay before print to ensure content is loaded
        setTimeout(function() {
            window.print();
        }, 100);
    </script>
</body>

</html>
