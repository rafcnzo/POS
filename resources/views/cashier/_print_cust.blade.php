<div id="invoice-POS">

    <center id="top">
        <div class="logo">
            @if(isset($logoBase64) && $logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" style="width:80px; height:80px; display:block; margin:0 auto;">
            @endif
        </div>
        <div class="info">
            <h2 style="font-size:26px;">{{ $settings['store_name'] ?? '-' }}</h2>
            <p>
                {{ $settings['store_address'] ?? '-' }}</br>
                {{ $settings['store_phone'] ?? '-' }}</br>
            </p>
        </div><!--End Info-->
    </center><!--End InvoiceTop-->

    <div id="bot">

        <div id="table">
            <table>
                <tr class="tabletitle">
                    <td class="item">
                        <h2 style="font-size:18px;">Item</h2>
                    </td>
                    <td class="Hours">
                        <h2 style="font-size:18px;">Qty</h2>
                    </td>
                    <td class="Rate">
                        <h2 style="font-size:18px;">Sub Total</h2>
                    </td>
                </tr>

                @foreach ($sale->items as $item)
                <tr class="service">
                    <td class="tableitem">
                        <p class="itemtext" style="font-size:18px;">
                            {{ $item->menuItem->name ?? '-' }}
                            @if (!empty($item->selectedModifiers) && count($item->selectedModifiers))
                                @foreach ($item->selectedModifiers as $modifier)
                                    <br>
                                    <span style="font-size:15px; padding-left:10px;">
                                        + {{ $modifier->modifier->name ?? '-' }}
                                        @if ($modifier->price > 0)
                                            ({{ number_format($modifier->price, 0, ',', '.') }})
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                            @if (!empty($item->notes))
                                <br><span style="font-size:15px; padding-left:10px; font-style:italic;">{{ $item->notes }}</span>
                            @endif
                        </p>
                    </td>
                    <td class="tableitem">
                        <p class="itemtext" style="font-size:18px;">{{ $item->quantity }}</p>
                    </td>
                    <td class="tableitem">
                        <p class="itemtext" style="font-size:18px;">{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                    </td>
                </tr>
                @endforeach

                <tr class="tabletitle">
                    <td></td>
                    <td class="Rate">
                        <h2 style="font-size:17px;">Subtotal</h2>
                    </td>
                    <td class="payment">
                        <h2 style="font-size:17px;">{{ number_format($sale->subtotal, 0, ',', '.') }}</h2>
                    </td>
                </tr>
                @if($sale->discount_amount > 0)
                <tr class="tabletitle">
                    <td></td>
                    <td class="Rate">
                        <h2 style="font-size:17px;">Diskon</h2>
                    </td>
                    <td class="payment">
                        <h2 style="font-size:17px;">-{{ number_format($sale->discount_amount, 0, ',', '.') }}</h2>
                    </td>
                </tr>
                @endif
                @if($sale->tax_amount > 0)
                <tr class="tabletitle">
                    <td></td>
                    <td class="Rate">
                        <h2 style="font-size:17px;">Pajak</h2>
                    </td>
                    <td class="payment">
                        <h2 style="font-size:17px;">{{ number_format($sale->tax_amount, 0, ',', '.') }}</h2>
                    </td>
                </tr>
                @endif
                <tr class="tabletitle">
                    <td></td>
                    <td class="Rate">
                        <h2 style="font-size:17px;">Total</h2>
                    </td>
                    <td class="payment">
                        <h2 style="font-size:17px;">{{ number_format($sale->total_amount, 0, ',', '.') }}</h2>
                    </td>
                </tr>
            </table>
        </div><!--End Table-->

        <center id="top">
            <p class="legal" style="margin-bottom:0; font-size:20px;">
                <strong>{{ $settings['receipt_footer_title'] ?? 'TERIMAKASIH' }}</strong>
            </p>
            <p class="legal" style="margin-top:2px; font-size:18px;">
                {{ $settings['receipt_footer_text'] ?? 'Terima Kasih Atas Kunjungan Anda' }}
            </p>
        </center>

    </div><!--End InvoiceBot-->
</div><!--End Invoice-->
