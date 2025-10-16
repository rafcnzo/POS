<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Kitchen - Per Item</title>
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
            font-size: 13px;
            font-weight: bold;
            line-height: 1.4;
            padding: 8px;
            width: 80mm;
            margin: 0 auto;
            background: white;
        }

        .receipt {
            border: 2px solid #000;
            padding: 8px;
            margin-bottom: 15px;
            page-break-after: always;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #000;
        }

        .order-type {
            font-size: 14px;
            background: #000;
            color: white;
            padding: 3px 10px;
        }

        .order-type.takeaway {
            background: #ff6600;
        }

        .order-info {
            font-size: 11px;
            text-align: right;
        }

        .table-section {
            text-align: center;
            padding: 8px 0;
            margin: 6px 0;
            border: 2px solid #000;
            background: #f5f5f5;
        }

        .table-section.hidden {
            display: none;
        }

        .table-label {
            font-size: 11px;
            margin-bottom: 2px;
        }

        .table-number {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .queue-section {
            text-align: center;
            padding: 8px 0;
            margin: 6px 0;
            border: 2px solid #ff6600;
            background: #fff3e6;
        }

        .queue-section.hidden {
            display: none;
        }

        .queue-label {
            font-size: 11px;
            margin-bottom: 2px;
        }

        .queue-number {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #ff6600;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .item-main {
            text-align: center;
            margin: 10px 0;
        }

        .item-qty {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .item-name {
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px;
            border: 2px solid #000;
            background: #fff;
        }

        .item-notes {
            text-align: left;
            padding: 6px;
            background: #fffacd;
            border: 1px solid #000;
            margin: 6px 0;
        }

        .notes-title {
            font-size: 11px;
            margin-bottom: 3px;
        }

        .notes-content {
            font-size: 10px;
            font-weight: normal;
            line-height: 1.5;
        }

        .customer-info {
            font-size: 10px;
            margin: 6px 0;
            padding: 5px;
            background: #f9f9f9;
            border: 1px solid #999;
        }

        .customer-info.takeaway {
            background: #fff3e6;
            border-color: #ff6600;
        }

        .customer-info div {
            margin-bottom: 2px;
        }

        .footer {
            text-align: center;
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #000;
            font-size: 9px;
            font-weight: normal;
        }

        @media print {
            body {
                width: 80mm;
            }

            .receipt {
                border: 2px solid #000;
            }
        }
    </style>
</head>

<body>
    @php
        use Carbon\Carbon;
        $count = 0;
        $items = $sale->items ?? [];
        $userName = $sale->user->name ?? '-';
        $orderType = strtoupper($sale->order_type ?? 'dine_in');
        $orderTypeText = ($sale->order_type ?? 'dine_in') === 'take_away' ? 'TAKE AWAY' : 'DINE-IN';
        $orderTypeClass = ($sale->order_type ?? 'dine_in') === 'take_away' ? 'order-type takeaway' : 'order-type';
        $showTable = (strtolower($sale->order_type) == 'dine_in') && !empty($sale->table_number);
        $showQueue = (strtolower($sale->order_type) == 'take_away') && !empty($sale->queue_number ?? null);
        $orderDate = Carbon::parse($sale->created_at ?? now())->format('d/m/Y H:i');
        $orderShortDate = Carbon::parse($sale->created_at ?? now())->format('d/m H:i');
        $custName = $sale->customer_name ?? '-';
        $trxCode = $sale->transaction_code ?? '-';
    @endphp

    @foreach($items as $index => $item)
        <div class="receipt">
            <div class="header">
                <div class="{{ $orderTypeClass }}">
                    {{ $orderTypeText }}
                </div>
                <div class="order-info">
                    #{{ $trxCode }}<br>
                    {{ $orderShortDate }}
                </div>
            </div>

            <div class="table-section{{ $showTable ? '' : ' hidden' }}">
                <div class="table-label">MEJA</div>
                <div class="table-number">{{ $sale->table_number ?? '-' }}</div>
            </div>

            <div class="queue-section{{ $showQueue ? '' : ' hidden' }}">
                <div class="queue-label">NO. ANTRIAN</div>
                <div class="queue-number">{{ $sale->queue_number ?? '-' }}</div>
            </div>

            <div class="item-main">
                <div class="item-qty">{{ $item->quantity }}x</div>
                <div class="item-name">
                    {{ strtoupper($item->menuItem->name ?? '-') }}
                </div>
            </div>

            @if($item->selectedModifiers && $item->selectedModifiers->count())
                <div class="item-notes">
                    <div class="notes-title">MODIFIER:</div>
                    <div class="notes-content">
                        @foreach($item->selectedModifiers as $mod)
                            - {{ $mod->modifier->name ?? '-' }}<br>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(!empty($item->notes))
                <div class="item-notes">
                    <div class="notes-title">CATATAN:</div>
                    <div class="notes-content">
                        {!! nl2br(e($item->notes)) !!}
                    </div>
                </div>
            @endif

            <div class="divider"></div>

            <div class="customer-info {{ ($sale->order_type??'') == 'take_away' ? 'takeaway' : '' }}">
                <div>Pelanggan: {{ $custName }}</div>
                <div>Kasir: {{ $userName }}</div>
            </div>

            <div class="footer">
                {{ Carbon::parse($sale->created_at ?? now())->format('d/m/Y H:i:s') }} | Item {{ $index+1 }}/{{ count($items) }}
            </div>
        </div>
    @endforeach
</body>
<script>    
    window.onload = function() {
        window.print();
    }
</script>
</html>
