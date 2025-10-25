@extends('app')

@section('style')
    <style>
        .payment-summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .payment-summary-item:last-child {
            border-bottom: none;
        }

        .item-details h6 {
            margin-bottom: 0.25rem;
        }

        .item-price {
            font-weight: 500;
        }

        .summary-total {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .checkout-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 1.2rem 0.7rem 1.2rem;
            background-color: #f7fafd;
            border-bottom: 1px solid #e9ecef;
            font-size: 1.13rem;
            font-weight: 500;
        }

        .checkout-total:last-child {
            border-bottom: none;
        }

        .checkout-total span {
            display: inline-block;
        }

        .checkout-total .total-value {
            font-weight: 700;
            color: #2e4a71;
            font-size: 1.15em;
            letter-spacing: 0.3px;
        }

        /* Deposit applied (i.e. green highlight row) */
        #depositAppliedRow {
            background-color: #e8f8ef !important;
            color: #18975c !important;
            font-weight: 600;
            border-left: 4px solid #18bb6d;
        }

        #depositAppliedRow .total-value {
            color: #18975c !important;
        }

        /* Harus dibayar row (make it slightly bolder/larger) */
        .checkout-total[style*="1.4rem"] {
            background: #ffe8e6 !important;
            color: #e25433 !important;
            font-size: 1.26rem !important;
            font-weight: 700;
            border-top: 2px solid #fdc3b1;
            border-bottom: 2px solid #fdc3b1;
        }

        .checkout-total[style*="1.4rem"] .total-value {
            color: #c93313 !important;
            font-size: 1.35em;
            font-weight: 900;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Halaman Pembayaran</h1>
                            <p class="page-subtitle">Selesaikan transaksi dan proses pembayaran</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h5>
                        </div>
                        <div class="card-body p-4">
                            @forelse($sale->items as $item)
                                <div class="payment-summary-item">
                                    <div class="item-details">
                                        <h6>{{ $item->menuItem->name }}</h6>
                                        @if ($item->selectedModifiers->isNotEmpty())
                                            @foreach ($item->selectedModifiers as $saleModifier)
                                                <div class="ms-3"><small class="text-muted">+
                                                        {{ $saleModifier->modifier->name }}</small></div>
                                            @endforeach
                                        @endif
                                        @if ($item->notes)
                                            <div class="ms-3 fst-italic"><small class="text-info">Catatan:
                                                    {{ $item->notes }}</small></div>
                                        @endif
                                        <small class="text-muted">{{ $item->quantity }} x Rp
                                            {{ number_format($item->price, 0, ',', '.') }}</small>
                                    </div>
                                    <div class="item-price">
                                        <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-muted">Keranjang kosong.</p>
                            @endforelse
                            <hr>
                            <div class="d-flex justify-content-between" id="discountRow" style="display: none;">
                                <span class="text-danger">Diskon</span>
                                <span class="text-danger" id="discountAmountDisplay">- Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2" id="taxRow"
                                @if (empty($taxAmount) || $taxAmount == 0) style="display: none;" @endif>
                                <span class="text-primary">Pajak</span>
                                <span class="text-primary" id="taxAmountDisplay">
                                    @if (!empty($taxAmount) && $taxAmount > 0)
                                        Rp {{ number_format($taxAmount, 0, ',', '.') }}
                                    @else
                                        Rp 0
                                    @endif
                                </span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between summary-total mt-3">
                                <span>TOTAL</span>
                                <span id="finalTotalDisplay">Rp
                                    {{ number_format($sale->total_amount ?? $sale->subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-wallet2"></i> Proses Pembayaran
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="formFinalPayment" action="{{ route('cashier.payment.process', $sale) }}"
                                method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            Nama Pelanggan <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="customer_name"
                                            placeholder="Nama pelanggan" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">No. Meja</label>
                                        <input type="text" class="form-control" name="table_number"
                                            placeholder="Opsional">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">
                                            Tipe Pesanan <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" name="order_type" required>
                                            <option value="dine_in" selected>Dine In</option>
                                            <option value="take_away">Take Away</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Catatan Transaksi</label>
                                        <textarea class="form-control" name="notes" rows="2" placeholder="Catatan untuk seluruh transaksi (opsional)"></textarea>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-3">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">
                                            Metode Pembayaran <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" name="payment_method" id="paymentMethodSelect" required>
                                            <option value="cash">Tunai (Cash)</option>
                                            <option value="qris">QRIS</option>
                                            <option value="edc">EDC</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="reservationSelect" class="form-label">Gunakan Deposit Reservasi?</label>
                                        <select class="form-select" id="reservationSelect" name="reservation_id">
                                            <option value="">-- Tidak Pakai Deposit --</option>
                                            @foreach ($activeReservations as $res)
                                                <option value="{{ $res->id }}"
                                                    data-deposit="{{ $res->deposit_amount }}"
                                                    data-customer-name="{{ $res->customer_name }}"
                                                    data-table-number="{{ $res->table_number ?? '' }}">
                                                    {{ $res->customer_name }} (Meja: {{ $res->table_number ?? 'N/A' }}) -
                                                    Deposit: Rp {{ number_format($res->deposit_amount, 0, ',', '.') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div id="depositInfo" class="form-text"
                                            style="display: none; color: green; font-weight: bold;"></div>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-3">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Diskon Transaksi</label>
                                        <div class="input-group">
                                            <select class="form-select" id="discountType" name="discount_type"
                                                style="flex-grow: 0; width: 120px;">
                                                <option value="">Tidak ada</option>
                                                <option value="fixed">Rupiah (Rp)</option>
                                                <option value="percentage">Persen (%)</option>
                                            </select>
                                            <input type="number" class="form-control" id="discountValue"
                                                name="discount_value" placeholder="0" disabled>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-3">
                                    </div>

                                    <div id="cashPaymentFields" class="col-12">
                                        <div class="mb-3">
                                            <label for="cashReceived" class="form-label">Uang Diterima</label>
                                            <input type="number" class="form-control form-control-lg" id="cashReceived"
                                                name="cash_received" placeholder="0">
                                        </div>
                                        <div class="mb-3">
                                            <label for="cashChange" class="form-label">Kembalian</label>
                                            <input type="text" class="form-control form-control-lg" id="cashChange"
                                                value="Rp 0" readonly
                                                style="background-color: #e9ecef; font-weight: bold;">
                                        </div>
                                    </div>

                                    <div id="edcPaymentFields" class="col-12 text-center" style="display: none;">
                                        <p class="text-muted p-3 border rounded mb-0">
                                            Silakan proses pembayaran di mesin EDC/QRIS yang tersedia.
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="checkout-total">
                            <span>Total Tagihan:</span>
                            <span class="total-value" id="finalBillAmount">
                                {{-- Ubah agar nilainya selalu sama seperti perhitungan real-time amountDue (total sebelum deposit) --}}
                                <span id="finalBillAmountDynamic">Rp
                                    {{ number_format($sale->subtotal + $taxAmount, 0, ',', '.') }}</span>
                            </span>
                        </div>
                        <div class="checkout-total" id="depositAppliedRow" style="display: none; color: green;">
                            <span>Deposit Digunakan:</span>
                            <span class="total-value" id="depositAppliedAmount">- Rp 0</span>
                        </div>
                        <div class="checkout-total" style="font-size: 1.4rem;">
                            <span>Harus Dibayar:</span>
                            <span class="total-value" id="amountDueDisplay">
                                <span id="amountDueDisplayDynamic">Rp
                                    {{ number_format($sale->subtotal + $taxAmount, 0, ',', '.') }}</span>
                            </span>
                        </div>
                        <div class="card-footer p-3 d-flex justify-content-between">
                            <button type="button" class="btn btn-danger btn-lg" id="btnVoidTransaction"
                                data-url="{{ route('cashier.payment.void', $sale) }}">
                                <i class="bi bi-x-circle"></i> Batalkan
                            </button>
                            <button type="button" class="btn btn-success btn-lg" id="btnProcessFinalPayment">
                                <i class="bi bi-check-circle-fill"></i> Proses & Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data dan elemen utama
            const subtotal = {{ $sale->subtotal }};
            const taxPercentage = {{ $taxPercentage ?? 0 }};
            let discountAmount = 0;
            let depositValue = 0;
            let finalTotalAmount = subtotal;
            let amountDue = subtotal;

            // Elemen DOM
            const formatCurrency = (value) => 'Rp ' + (value || 0).toLocaleString('id-ID');
            const reservationSelect = document.getElementById('reservationSelect');
            const depositInfo = document.getElementById('depositInfo');
            const depositAppliedRow = document.getElementById('depositAppliedRow');
            const depositAppliedAmount = document.getElementById('depositAppliedAmount');
            // (tambahan): pastikan syncron ke DOM yang benar
            const finalBillAmount = document.getElementById('finalBillAmountDynamic');
            const amountDueDisplay = document.getElementById('amountDueDisplayDynamic');
            const discountTypeSelect = document.getElementById('discountType');
            const discountValueInput = document.getElementById('discountValue');
            const discountRow = document.getElementById('discountRow');
            const discountAmountDisplay = document.getElementById('discountAmountDisplay');
            const finalTotalDisplay = document.getElementById('finalTotalDisplay');
            const taxAmountDisplay = document.getElementById('taxAmountDisplay');
            const taxRow = document.getElementById('taxRow');
            const paymentMethodSelect = document.getElementById('paymentMethodSelect');
            const cashFields = document.getElementById('cashPaymentFields');
            const edcFields = document.getElementById('edcPaymentFields');
            const cashReceivedInput = document.getElementById('cashReceived');
            const cashChangeInput = document.getElementById('cashChange');
            const finalPaymentBtn = document.getElementById('btnProcessFinalPayment');
            const form = document.getElementById('formFinalPayment');
            const customerNameInput = document.querySelector('input[name="customer_name"]');
            const tableNumberInput = document.querySelector('input[name="table_number"]');
            const orderTypeSelect = document.querySelector('select[name="order_type"]');

            function recalculateTotal() {
                // 1. Hitung diskon
                const discountType = discountTypeSelect ? discountTypeSelect.value : '';
                const discountValue = discountValueInput ? (parseFloat(discountValueInput.value) || 0) : 0;
                if (discountType === 'percentage' && discountValue > 0) {
                    discountAmount = subtotal * (discountValue / 100);
                    discountRow.style.display = 'flex';
                } else if (discountType === 'fixed' && discountValue > 0) {
                    discountAmount = discountValue;
                    discountRow.style.display = 'flex';
                } else {
                    discountAmount = 0;
                    if (discountRow) discountRow.style.display = 'none';
                }
                discountAmount = Math.min(subtotal, discountAmount);

                // 2. Hitung subtotal akhir setelah diskon
                const totalAfterDiscount = subtotal - discountAmount;

                // 3. Hitung pajak dari subtotal setelah diskon
                const taxAmount = totalAfterDiscount * (taxPercentage / 100);

                // 4. Hitung total tagihan (setelah diskon dan pajak, sebelum deposit)
                finalTotalAmount = totalAfterDiscount + taxAmount;

                // 5. Update display summary (final total sebelum deposit, pajak & diskon info)
                if (taxAmountDisplay) taxAmountDisplay.textContent = formatCurrency(taxAmount);
                if (taxRow) taxRow.style.display = (taxAmount > 0 ? 'flex' : 'none');
                if (discountAmountDisplay) discountAmountDisplay.textContent = '- ' + formatCurrency(
                    discountAmount);
                if (finalTotalDisplay) finalTotalDisplay.textContent = formatCurrency(finalTotalAmount);
                if (finalBillAmount) finalBillAmount.textContent = formatCurrency(finalTotalAmount);

                // 6. Hitung dan update jumlah yang harus dibayar (setelah deposit)
                amountDue = Math.max(0, finalTotalAmount - depositValue);
                if (amountDueDisplay) amountDueDisplay.textContent = formatCurrency(amountDue);

                // 7. Update kolom kembalian jika pembayaran cash
                if (cashReceivedInput) cashReceivedInput.dispatchEvent(new Event('input'));
            }

            // Event: Diskon berubah (tipe/value)
            if (discountTypeSelect) {
                discountTypeSelect.addEventListener('change', function() {
                    discountValueInput.disabled = !this.value;
                    if (this.value) {
                        discountValueInput.focus();
                    } else {
                        discountValueInput.value = '';
                    }
                    recalculateTotal();
                });
            }
            if (discountValueInput) {
                discountValueInput.addEventListener('input', recalculateTotal);
            }

            // Event: Ganti reservasi (deposit)
            if (reservationSelect) {
                reservationSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    depositValue = parseFloat(selectedOption?.dataset.deposit || 0);
                    if (depositValue > 0) {
                        if (depositInfo) {
                            depositInfo.textContent =
                                `Deposit sebesar ${formatCurrency(depositValue)} akan digunakan.`;
                            depositInfo.style.display = 'block';
                        }
                        if (depositAppliedAmount) depositAppliedAmount.textContent =
                            `- ${formatCurrency(depositValue)}`;
                        if (depositAppliedRow) depositAppliedRow.style.display = 'flex';

                        if (customerNameInput) customerNameInput.value = selectedOption.dataset
                            .customerName || '';
                        if (tableNumberInput) tableNumberInput.value = selectedOption.dataset.tableNumber ||
                            '';
                        if (orderTypeSelect) orderTypeSelect.value = 'dine_in'; // Otomatis set ke Dine In
                    } else {
                        if (depositInfo) depositInfo.style.display = 'none';
                        if (depositAppliedRow) depositAppliedRow.style.display = 'none';
                        if (customerNameInput) customerNameInput.value = '';
                        if (tableNumberInput) tableNumberInput.value = '';
                        if (orderTypeSelect) orderTypeSelect.value = 'dine_in'; // Default dine_in
                    }
                    recalculateTotal();
                });
            } else {
                depositValue = 0;
                recalculateTotal();
            }

            // Event: Ganti metode pembayaran
            if (paymentMethodSelect) {
                paymentMethodSelect.addEventListener('change', function() {
                    if (this.value === 'cash') {
                        if (cashFields) cashFields.style.display = 'block';
                        if (edcFields) edcFields.style.display = 'none';
                    } else {
                        if (cashFields) cashFields.style.display = 'none';
                        if (edcFields) edcFields.style.display = 'block';
                    }
                });
                paymentMethodSelect.dispatchEvent(new Event('change'));
            }

            // Event: Input uang diterima (cash) => update kembalian
            if (cashReceivedInput && cashChangeInput) {
                cashReceivedInput.addEventListener('input', function() {
                    const received = parseFloat(this.value) || 0;
                    const change = received - amountDue;
                    cashChangeInput.value = formatCurrency(change >= 0 ? change : 0);
                });
            }

            // Tombol Proses & Simpan
            if (finalPaymentBtn) {
                finalPaymentBtn.addEventListener('click', function() {
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : '';
                    const cashReceived = cashReceivedInput ? (parseFloat(cashReceivedInput.value) || 0) : 0;

                    if (paymentMethod === 'cash' && cashReceived < amountDue) {
                        Swal.fire('Uang Kurang', 'Jumlah uang yang diterima kurang dari total tagihan.',
                            'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Konfirmasi Transaksi',
                        text: 'Anda yakin ingin memproses transaksi ini?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Proses!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitTransaction();
                        }
                    });
                });
            }

            function submitTransaction() {
                showLoading('Menyimpan transaksi...');
                const formData = new FormData(form);
                const dataToSend = {};

                formData.forEach((value, key) => dataToSend[key] = value);

                dataToSend.discount_type = discountTypeSelect ? discountTypeSelect.value : null;
                dataToSend.discount_value = discountValueInput ? (discountValueInput.value || 0) : 0;
                dataToSend.discount_amount = discountAmount;
                dataToSend.tax_amount = finalTotalAmount - (subtotal - discountAmount);
                dataToSend.deposit_amount = depositValue;
                dataToSend.amount_due = amountDue;
                dataToSend.final_total = finalTotalAmount;

                fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(dataToSend)
                    })
                    .then(response => response.json())
                    .then(data => {
                        hideLoading();
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Transaksi Berhasil!',
                                text: data.message,
                                icon: 'success',
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonText: '<i class="bi bi-printer"></i> Cetak Struk Pelanggan',
                                denyButtonText: '<i class="bi bi-receipt"></i> Cetak Struk Dapur',
                                cancelButtonText: 'Selesai',

                                preConfirm: async () => {
                                    try {
                                        await handleCustomerReceipt(data.sale_id);
                                        toastr.success('Struk pelanggan telah dicetak!');
                                    } catch (err) {
                                        Swal.showValidationMessage(err.message || 'Gagal mencetak struk pelanggan');
                                    }
                                    return false;
                                },

                                preDeny: async () => {
                                    try {
                                        await handleSmartPrint(data.sale_id);
                                        toastr.success('Pesanan telah dikirim ke dapur/bar!');
                                    } catch (err) {
                                        Swal.showValidationMessage(err.message || 'Gagal mencetak pesanan dapur');
                                    }
                                    return false;
                                }

                            }).then((result) => {
                                if (result.dismiss === Swal.DismissReason.cancel) {
                                    window.location.href = "{{ route('cashier.index') }}";
                                }
                            });

                        } else {
                            Swal.fire('Gagal!', data.message || 'Terjadi kesalahan di server.', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        Swal.fire('Error!', 'Tidak dapat terhubung ke server.', 'error');
                    });
            }

            const voidBtn = document.getElementById('btnVoidTransaction');
            if (voidBtn) {
                voidBtn.addEventListener('click', function() {
                    const url = this.dataset.url;

                    // Ganti ke withAuth untuk Batalkan Transaksi (dengan password otorisasi)
                    withAuth(function() {
                        Swal.fire({
                            title: 'Batalkan Transaksi?',
                            text: "Anda yakin ingin membatalkan transaksi ini? Stok akan dikembalikan ke semula.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, batalkan!',
                            cancelButtonText: 'Tidak'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showLoading('Membatalkan transaksi...');
                                fetch(url, {
                                        method: 'POST',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        },
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        hideLoading();
                                        if (data.status === 'success') {
                                            Swal.fire({
                                                title: 'Dibatalkan!',
                                                text: data.message,
                                                icon: 'success'
                                            }).then(() => {
                                                window.location.href =
                                                    "{{ route('cashier.index') }}";
                                            });
                                        } else {
                                            Swal.fire('Gagal!', data.message ||
                                                'Gagal membatalkan transaksi.',
                                                'error');
                                        }
                                    })
                                    .catch(error => {
                                        hideLoading();
                                        Swal.fire('Error!',
                                            'Tidak dapat terhubung ke server.',
                                            'error');
                                    });
                            }
                        });
                    });
                });
            }

            // Kalkulasi awal on load
            recalculateTotal();
        });

        // ========== QZ TRAY CONFIGURATION ==========
        let qzInstance = null;

        async function initQZ() {
            if (qzInstance) return qzInstance;

            try {
                if (!qz.websocket.isActive()) {
                    await qz.websocket.connect();
                    console.log("QZ Tray connected successfully");
                }
                qzInstance = qz;
                return qzInstance;
            } catch (err) {
                console.error("QZ Tray connection failed:", err);
                throw new Error("Tidak dapat terhubung ke QZ Tray. Pastikan aplikasi QZ Tray sudah berjalan.");
            }
        }

        async function printWithQZ(printerName, htmlContent) {
            try {
                await initQZ();

                const config = qz.configs.create(printerName, {
                    scaleContent: false,
                    rasterize: false,
                    interpolation: 'bicubic',
                    margins: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0
                    }
                });

                const data = [{
                    type: 'pixel',
                    format: 'html',
                    flavor: 'plain',
                    data: htmlContent
                }];

                await qz.print(config, data);
                console.log(`Print sent to ${printerName}`);
                return true;
            } catch (err) {
                console.error("Print error:", err);
                throw err;
            }
        }

        // ========== SMART PRINT HANDLER (Kitchen & Bar) ==========
        async function handleSmartPrint(saleId) {
            try {
                Swal.fire({
                    title: 'Mencetak...',
                    text: 'Mengirim ke printer dapur/bar',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const response = await fetch(`/cashier/print/smart/${saleId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                const result = await response.json();

                if (!result.success || !result.jobs || result.jobs.length === 0) {
                    throw new Error(result.message || 'Tidak ada print jobs tersedia');
                }

                // Connect QZ Tray
                await initQZ();

                // Print each job
                for (const job of result.jobs) {
                    await printWithQZ(job.printer, job.html);
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Pesanan telah dikirim ke printer dapur/bar',
                    timer: 2000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error('Smart print error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mencetak',
                    html: `
                <p>${error.message}</p>
                <small class="text-muted">Pastikan QZ Tray sudah berjalan dan printer terhubung.</small>
            `,
                    confirmButtonText: 'OK'
                });
            }
        }

        // ========== CUSTOMER RECEIPT HANDLER ==========
        async function handleCustomerReceipt(saleId) {
            try {
                Swal.fire({
                    title: 'Mencetak Struk...',
                    text: 'Mengirim ke printer kasir',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const response = await fetch(`/cashier/print/customer/${saleId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Gagal mendapatkan data struk');
                }

                // Connect QZ Tray
                await initQZ();

                // Print customer receipt
                await printWithQZ(result.printer, result.html);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Struk telah dicetak',
                    timer: 2000,
                    showConfirmButton: false
                });

            } catch (error) {
                console.error('Customer receipt print error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mencetak Struk',
                    html: `
                <p>${error.message}</p>
                <small class="text-muted">Pastikan QZ Tray sudah berjalan dan printer terhubung.</small>
            `,
                    confirmButtonText: 'OK'
                });
            }
        }

        // ========== AUTO DISCONNECT QZ ON PAGE UNLOAD ==========
        window.addEventListener('beforeunload', () => {
            if (qz.websocket.isActive()) {
                qz.websocket.disconnect();
            }
        });
    </script>
@endpush
