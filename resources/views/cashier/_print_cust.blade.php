<style>
    @media print {
        @page {
            size: 80mm auto;
            margin-top: 0;
            margin-bottom: 0;
            margin-left: -1mm;
            margin-right: 4mm;
        }

        body {
            width: 80mm;
            margin-top: -2mm;
            margin-bottom: 0;
            margin-left: -1mm;
            margin-right: 5mm;
            padding: 1.5mm 3mm 3mm 3mm;
        }
    }
</style>
<div id="invoice-POS">

    <center id="top">
        <div class="logo" style="margin-bottom: 4px;">
            @if (isset($logoBase64) && $logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo"
                    style="
                        width: 80px;
                        height: auto; 
                        display: block; 
                        margin: 0 auto;
                        filter: grayscale(1) contrast(200%); 
                        margin-bottom: 2px;
                    ">
            @endif
        </div>
        <div class="info" style="margin-top: 0;">
            <h2 style="font-size:20px; margin-bottom:2px;">{{ $settings['store_name'] ?? '-' }}</h2>
            <p style="font-size:11px; margin-top:0;">
                {{ $settings['store_address'] ?? '-' }}<br>
                {{ $settings['store_phone'] ?? '-' }}<br>
            </p>
        </div><!--End Info-->

        @php
            $trxNumber = $sale->transaction_code ?? (isset($sale->id) ? 'TRX-' . \Carbon\Carbon::parse($sale->created_at)->format('Ymd') . '-' . str_pad($sale->id, 3, '0', STR_PAD_LEFT) : '-');
            $trxDate = $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y, H.i.s') : '-';
            // Nama kasir
            $kasir = $sale->user->name ?? '-';

            $customerName = $sale->customer_name ?? '-';

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

            $tableNumber = $sale->table_number ?? '-';
        @endphp
        <table style="width:100%; margin: 2px auto 8px auto; font-size:13px;">
            <tr>
                <td style="text-align:left; width:44%;">No Transaksi</td>
                <td style="text-align:left; width:2%;">:</td>
                <td style="text-align:left;">{{ $trxNumber }}</td>
            </tr>
            <tr>
                <td style="text-align:left;">Tanggal</td>
                <td style="text-align:left;">:</td>
                <td style="text-align:left;">{{ $trxDate }}</td>
            </tr>
            <tr>
                <td style="text-align:left;">Kasir</td>
                <td style="text-align:left;">:</td>
                <td style="text-align:left;">{{ $kasir }}</td>
            </tr>
            <tr>
                <td style="text-align:left;">Customer</td>
                <td style="text-align:left;">:</td>
                <td style="text-align:left;">{{ $customerName }}</td>
            </tr>
            <tr>
                <td style="text-align:left;">Order Type</td>
                <td style="text-align:left;">:</td>
                <td style="text-align:left;">{{ $orderType }}</td>
            </tr>
            <tr>
                <td style="text-align:left;">No Meja</td>
                <td style="text-align:left;">:</td>
                <td style="text-align:left;">{{ $tableNumber }}</td>
            </tr>
        </table>
    </center><!--End InvoiceTop-->

    <div id="bot" style="margin-top:7mm;">

        <div id="table">
            <table>
                <tr class="tabletitle">
                    <td class="item">
                        <h2 style="font-size:12px;">Item</h2>
                    </td>
                    <td class="Hours" style="padding-left:10px; padding-right:5px;">
                        <h2 style="font-size:12px;">Qty</h2>
                    </td>
                    <td class="Rate" style="padding-left:0; padding-right:0;">
                        <h2 style="font-size:12px; padding-left:-8px;">Sub Total</h2>
                    </td>
                </tr>

                @foreach ($sale->items as $item)
                    <tr class="service">
                        <td class="tableitem">
                            <p class="itemtext" style="font-size:12px;">
                                {{ $item->menuItem->name ?? '-' }}
                                @if (!empty($item->selectedModifiers) && count($item->selectedModifiers))
                                    @foreach ($item->selectedModifiers as $modifier)
                                        <br>
                                        <span style="font-size:12px; padding-left:10px;">
                                            + {{ $modifier->modifier->name ?? '-' }}
                                            @if ($modifier->price > 0)
                                                (Rp. {{ number_format($modifier->price, 0, ',', '.') }})
                                            @endif
                                        </span>
                                    @endforeach
                                @endif
                                @if (!empty($item->notes))
                                    <br><span
                                        style="font-size:12px; padding-left:10px; font-style:italic;">{{ $item->notes }}</span>
                                @endif
                            </p>
                        </td>
                        <td class="tableitem" style="padding-left:10px; padding-right:5px;">
                            <p class="itemtext" style="font-size:12px;">{{ $item->quantity }}</p>
                        </td>
                        <td class="tableitem" style="padding-left:-5px;">
                            <p class="itemtext" style="font-size:12px;">
                                Rp. {{ number_format($item->subtotal, 0, ',', '.') }}
                            </p>
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="3" style="height: 18px; border: none;"></td>
                </tr>
                <tr class="tabletitle" style="margin-top: 8mm;">
                    <td></td>
                    <td class="Rate" style="text-align:right; white-space:nowrap; padding-right:0; padding-left: -2mm;">
                        <h2 style="font-size:12px; margin-left:-2mm;">
                            <span style="display:inline-block; min-width:70px; text-align:right;">Subtotal</span>
                            <span style="display:inline-block; width:18px; text-align:center;">:</span>
                        </h2>
                    </td>
                    <td class="payment" style="text-align:left;">
                        <h2 style="font-size:12px; margin-left:-2mm;">Rp. {{ number_format($sale->subtotal, 0, ',', '.') }}</h2>
                    </td>
                </tr>
                @if ($sale->discount_amount > 0)
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate" style="text-align:right; white-space:nowrap; padding-right:0; padding-left:-2mm;">
                            <h2 style="font-size:12px; margin-left:-2mm;">
                                <span style="display:inline-block; min-width:70px; text-align:right;">Diskon</span>
                                <span style="display:inline-block; width:18px; text-align:center;">:</span>
                            </h2>
                        </td>
                        <td class="payment" style="text-align:left;">
                            <h2 style="font-size:12px; margin-left:-2mm;">Rp. {{ number_format($sale->discount_amount, 0, ',', '.') }}</h2>
                        </td>
                    </tr>
                @endif
                @if ($sale->tax_amount > 0)
                    <tr class="tabletitle">
                        <td></td>
                        <td class="Rate" style="text-align:right; white-space:nowrap; padding-right:0; padding-left:-2mm;">
                            <h2 style="font-size:12px; margin-left:-2mm;">
                                <span style="display:inline-block; min-width:70px; text-align:right;">Pajak</span>
                                <span style="display:inline-block; width:18px; text-align:center;">:</span>
                            </h2>
                        </td>
                        <td class="payment" style="text-align:left;">
                            <h2 style="font-size:12px; margin-left:-2mm;">Rp. {{ number_format($sale->tax_amount, 0, ',', '.') }}</h2>
                        </td>
                    </tr>
                @endif
                <tr class="tabletitle">
                    <td></td>
                    <td class="Rate" style="text-align:right; white-space:nowrap; padding-right:0; padding-left:-2mm;">
                        <h2 style="font-size:15px; margin-left:-2mm;">
                            <span style="display:inline-block; min-width:70px; text-align:right;">Total</span>
                            <span style="display:inline-block; width:18px; text-align:center;">:</span>
                        </h2>
                    </td>
                    <td class="payment" style="text-align:left;">
                        <h2 style="font-size:12px; margin-left:-2mm;">Rp. {{ number_format($sale->total_amount, 0, ',', '.') }}</h2>
                    </td>
                </tr>
            </table>
            <p class="legal" style="margin-bottom:0; font-size:14px; text-align:center;">
                <strong>{{ $settings['receipt_footer_title'] ?? 'TERIMAKASIH' }}</strong>
            </p>
            @php
                $footerText = $settings['receipt_footer_text'] ?? 'Terima Kasih Atas Kunjungan Anda';
                // Paksa enter baris baru setiap 32 karakter
                $footerChunks = str_split($footerText, 32);
            @endphp
            <p class="legal" style="margin-top:2px; font-size:11px; text-align:center;">
                @foreach ($footerChunks as $chunk)
                    {{ $chunk }}@if (!$loop->last)
                        <br>
                    @endif
                @endforeach
            </p>
        </div><!--End Table-->

    </div><!--End InvoiceBot-->
</div><!--End Invoice-->
