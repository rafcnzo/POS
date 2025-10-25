<html>
<head>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 3mm;
            }
            body {
                font-size: 14px;
                width: 74mm;
                margin: 0;
                padding: 2mm;
                line-height: 1.3;
            }
        }
    </style>
</head>
<body>
    @php
        use Carbon\Carbon;
        $items = $itemsToPrint ?? ($sale->items ?? []);
        $userName = $sale->user->name ?? '-';
        $trxCode = $sale->transaction_code ?? '-';
        $custName = $sale->customer_name ?? '-';
        $settings = $settings ?? [];
    @endphp

    <div class="invoice-pos">
        <center id="top">
            <div class="logo" style="margin-bottom: 4px;">
               <h2>
                   @if(!empty($Kitchen))
                       <span style="font-size:11px; display:block; margin-top:1mm;">(KITCHEN)</span>
                   @elseif(!empty($Bar))
                       <span style="font-size:11px; display:block; margin-top:1mm;">(BAR)</span>
                   @endif
               </h2>
            </div>
        </center>

        <table style="width:100%; margin-bottom: 10px; font-size:14px">
            <tr>
                <td>No Transaksi</td>
                <td>:</td>
                <td>{{ $trxCode }}</td>
            </tr>
            <tr>
                <td>Order Type</td>
                <td>:</td>
                <td>
                    @php
                        if (isset($sale->order_type)) {
                            if ($sale->order_type === 'dine_in') {
                                $orderType = 'Dine In';
                            } elseif ($sale->order_type === 'take_away') {
                                $orderType = 'Take Away';
                            } else {
                                $orderType = $sale->order_type;
                            }
                        } else {
                            $orderType = '-';
                        }
                    @endphp
                    {{ $orderType }}
                </td>
            </tr>
            <tr>
                <td>No Meja</td>
                <td>:</td>
                <td>{{ $sale->table_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>
                    @php
                        $trxDate = isset($sale->created_at) ? Carbon::parse($sale->created_at)->format('d/m/Y H:i') : '-';
                    @endphp
                    {{ $trxDate }}
                </td>
            </tr>
            <tr>
                <td style="width:40%;">Kasir</td>
                <td style="width:4%;">:</td>
                <td>{{ $userName }}</td>
            </tr>
            
        </table>

        <div class="bot">
            <table class="table-item" style="width:100%;">
                <thead>
                    <tr>
                        <th style="text-align:left; width:60%;">Nama Item</th>
                        <th style="width:10%;">&nbsp;</th>
                        <th style="text-align:center; width:20%;">Qty</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            {{ $item->menuItem->name ?? '-' }}
                            @if (!empty($item->selectedModifiers) && count($item->selectedModifiers))
                                @foreach ($item->selectedModifiers as $modifier)
                                    <br><span style="font-size:13px; padding-left:8px;">+ {{ $modifier->modifier->name ?? '-' }}</span>
                                @endforeach
                            @endif
                            @if (!empty($item->notes))
                                <br><span style="font-size:13px; padding-left:8px; font-style:italic;">{{ $item->notes }}</span>
                            @endif
                        </td>
                        <td>&nbsp;</td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>Total Print</td>
                        <td></td>
                        <td style="text-align:center;">{{ count($items) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <script>
        setTimeout(function() {
            window.print();
        }, 100);
    </script>
</body>
</html>
