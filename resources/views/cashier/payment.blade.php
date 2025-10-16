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
                            <h5 class="card-title mb-0"><i class="bi bi-wallet2"></i> Proses Pembayaran</h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="formFinalPayment" action="{{ route('cashier.payment.process', $sale) }}"
                                method="POST">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="customer_name"
                                            placeholder="Nama pelanggan" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">No. Meja</label>
                                        <input type="text" class="form-control" name="table_number"
                                            placeholder="Opsional">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Tipe Pesanan <span class="text-danger">*</span></label>
                                        <select class="form-select" name="order_type" required>
                                            <option value="dine_in" selected>Dine In</option>
                                            <option value="take_away">Take Away</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Catatan Transaksi</label>
                                        <textarea class="form-control" name="notes" rows="2" placeholder="Catatan untuk seluruh transaksi (opsional)"></textarea>
                                    </div>
                                    <hr class="my-3">
                                    <div class="col-12">
                                        <label class="form-label">Metode Pembayaran <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" name="payment_method" id="paymentMethodSelect" required>
                                            <option value="cash">Tunai (Cash)</option>
                                            <option value="qris">QRIS</option>
                                            <option value="edc">EDC</option>
                                        </select>
                                    </div>

                                    <hr class="my-3">
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
                                    <hr class="my-3">

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
                                        <p class="text-muted p-3 border rounded">Silakan proses pembayaran di mesin
                                            EDC/QRIS
                                            yang tersedia.</p>
                                    </div>
                                </div>
                            </form>
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
            const subtotal = {{ $sale->subtotal }};
            const taxPercentage = {{ $taxPercentage ?? 0 }};
            let finalTotalAmount = subtotal + (subtotal * (taxPercentage / 100));
            const taxAmountDisplay = document.getElementById('taxAmountDisplay');
            const taxRow = document.getElementById('taxRow');

            const paymentMethodSelect = document.getElementById('paymentMethodSelect');
            const cashFields = document.getElementById('cashPaymentFields');
            const edcFields = document.getElementById('edcPaymentFields');
            const cashReceivedInput = document.getElementById('cashReceived');
            const cashChangeInput = document.getElementById('cashChange');
            const finalPaymentBtn = document.getElementById('btnProcessFinalPayment');
            const form = document.getElementById('formFinalPayment');
            const formatCurrency = (value) => 'Rp ' + (value || 0).toLocaleString('id-ID');

            // Elemen baru untuk diskon
            const discountTypeSelect = document.getElementById('discountType');
            const discountValueInput = document.getElementById('discountValue');
            const discountRow = document.getElementById('discountRow');
            const discountAmountDisplay = document.getElementById('discountAmountDisplay');
            const finalTotalDisplay = document.getElementById('finalTotalDisplay');

            // Fungsi pusat untuk menghitung ulang semua total
            function recalculateTotal() {
                let discountAmount = 0;
                const discountType = discountTypeSelect.value;
                const discountValue = parseFloat(discountValueInput.value) || 0;

                if (discountType === 'percentage' && discountValue > 0) {
                    discountAmount = subtotal * (discountValue / 100);
                    discountRow.style.display = 'flex';
                } else if (discountType === 'fixed' && discountValue > 0) {
                    discountAmount = discountValue;
                    discountRow.style.display = 'flex';
                } else {
                    discountRow.style.display = 'none';
                }

                discountAmount = Math.min(subtotal, discountAmount);

                const totalAfterDiscount = subtotal - discountAmount;

                // Hitung pajak dari total setelah diskon
                const taxAmount = totalAfterDiscount * (taxPercentage / 100);

                // Hitung total akhir
                finalTotalAmount = totalAfterDiscount + taxAmount;
                taxAmountDisplay.textContent = formatCurrency(taxAmount);
                taxRow.style.display = taxAmount > 0 ? 'flex' : 'none';
                discountAmountDisplay.textContent = '- ' + formatCurrency(discountAmount);
                finalTotalDisplay.textContent = formatCurrency(finalTotalAmount);
                cashReceivedInput.dispatchEvent(new Event('input'));
            }

            // Event listener untuk input diskon
            discountTypeSelect.addEventListener('change', function() {
                discountValueInput.disabled = !this.value;
                if (this.value) {
                    discountValueInput.focus();
                } else {
                    discountValueInput.value = '';
                }
                recalculateTotal();
            });
            discountValueInput.addEventListener('input', recalculateTotal);


            // --- Sisa JavaScript Anda yang sudah ada ---
            paymentMethodSelect.addEventListener('change', function() {
                if (this.value === 'cash') {
                    cashFields.style.display = 'block';
                    edcFields.style.display = 'none';
                } else {
                    cashFields.style.display = 'none';
                    edcFields.style.display = 'block';
                }
            });

            cashReceivedInput.addEventListener('input', function() {
                const received = parseFloat(this.value) || 0;
                const change = received - finalTotalAmount;
                cashChangeInput.value = formatCurrency(change >= 0 ? change : 0);
            });

            finalPaymentBtn.addEventListener('click', function() {
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const paymentMethod = paymentMethodSelect.value;
                const cashReceived = parseFloat(cashReceivedInput.value) || 0;

                if (paymentMethod === 'cash' && cashReceived < finalTotalAmount) {
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

            function submitTransaction() {
                showLoading('Menyimpan transaksi...');
                const formData = new FormData(form);
                const dataToSend = {};
                formData.forEach((value, key) => dataToSend[key] = value);

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

                                preConfirm: () => {
                                    window.open(
                                        "{{ route('cashier.print.customer', ['sale' => '__SALE_ID__']) }}"
                                        .replace('__SALE_ID__', data.sale_id), '_blank'
                                    );
                                    return false; // <-- Ini kuncinya!
                                },

                                preDeny: () => {
                                    window.open(
                                        "{{ route('cashier.print.kitchen', ['sale' => '__SALE_ID__']) }}"
                                        .replace('__SALE_ID__', data.sale_id), '_blank'
                                    );
                                    return false; // <-- Ini kuncinya!
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
                        Swal.fire('Error!', 'Tidak dapat terhubung ke server. Silakan coba lagi.', 'error');
                        console.error('Fetch Error:', error);
                    });
            }

            const voidBtn = document.getElementById('btnVoidTransaction');
            if (voidBtn) {
                voidBtn.addEventListener('click', function() {
                    const url = this.dataset.url;

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
                                            // Arahkan kembali ke halaman POS utama
                                            window.location.href =
                                                "{{ route('cashier.index') }}";
                                        });
                                    } else {
                                        Swal.fire('Gagal!', data.message ||
                                            'Gagal membatalkan transaksi.', 'error');
                                    }
                                })
                                .catch(error => {
                                    hideLoading();
                                    Swal.fire('Error!', 'Tidak dapat terhubung ke server.',
                                        'error');
                                });
                        }
                    });
                });
            }

            // Jalankan kalkulasi awal begitu halaman/diskon load
            recalculateTotal();
        });
    </script>
@endpush
