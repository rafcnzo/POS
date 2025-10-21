@extends('app')

@section('style')
<style>
    /* Data Card General */
    .data-card {
        border: 1px solid #eaeaea;
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(44, 62, 80, 0.06);
        transition: box-shadow 0.2s;
        margin-bottom: 24px;
    }

    .data-card:hover {
        box-shadow: 0 4px 18px rgba(44, 62, 80, 0.09);
    }

    .data-card-header {
        border-bottom: 1px solid #f1f1f1;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: linear-gradient(90deg, #f5f8fd 0%, #fdfaf5 100%);
    }

    .data-card-title {
        display: flex;
        align-items: center;
        font-weight: 600;
        font-size: 1.10em;
    }

    .data-card-title i {
        margin-right: 8px;
        font-size: 1.3em;
        color: #5596f6;
    }

    .data-card-title span {
        margin-right: 10px;
    }

    .data-card-title small {
        font-size: 0.95em;
        color: #888 !important;
        margin-left: 12px;
    }

    .data-card-actions {
        font-size: 1em;
        color: #2c3e50;
        background: #ecf8fa;
        padding: 6px 16px;
        border-radius: 20px;
        display: inline-block;
        font-weight: 500;
    }

    .data-card-body {
        padding: 0px 0 12px 0;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
        overflow: hidden;
    }

    /* Table Styling */
    .data-card .table {
        margin-bottom: 0;
        background: #fff;
    }

    .data-card .table th, .data-card .table td {
        vertical-align: middle;
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        font-size: 1em;
    }

    .data-card .table thead th {
        background: #f2f5fa;
        color: #32435b;
        font-weight: bold;
        border-top: none;
        font-size: 1.02em;
        border-bottom: 2px solid #e2eaf1;
    }

    .data-card .table-hover tbody tr:hover {
        background: #f6fcff;
        transition: background 0.15s;
    }

    .data-card .table tbody td.ps-3,
    .data-card .table thead th.ps-3 {
        padding-left: 1.2rem !important;
    }
    .data-card .table tbody td.pe-3,
    .data-card .table thead th.pe-3 {
        padding-right: 1.2rem !important;
    }

    .data-card .table tbody tr td.text-danger {
        color: #f4516c !important;
    }

    .data-card .btn-pay {
        font-size: 0.95em;
        padding: 5px 18px;
        border-radius: 20px;
        background: linear-gradient(90deg, #5596f6 0%, #5fd7e7 100%);
        color: #fff;
        border: none;
        transition: background 0.18s, box-shadow 0.18s;
        box-shadow: 0 2px 8px rgba(85,150,246,0.07);
        font-weight: 600;
    }
    .data-card .btn-pay:hover, .data-card .btn-pay:focus {
        background: linear-gradient(90deg, #50b3fa 0%, #32e9c2 100%);
        color: #fff !important;
        box-shadow: 0 2px 14px rgba(50,233,194,0.13);
    }
</style>

@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon"><i class="bi bi-credit-card-2-front"></i></div>
                        <div>
                            <h1 class="page-title">Pembayaran Supplier (Tempo)</h1>
                            <p class="page-subtitle">Catat pembayaran untuk Purchase Order dengan metode Tempo</p>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($suppliers as $supplier)
                @php
                    $supplierPOs = $groupedPOs->get($supplier->id);
                @endphp

                @if ($supplierPOs && $supplierPOs->isNotEmpty())
                    <div class="data-card mb-4">
                        <div class="data-card-header bg-light">
                            <div class="data-card-title">
                                <i class="bi bi-truck"></i>
                                <span>{{ $supplier->name }}</span>
                                {{-- Tampilkan Jatuh Tempo Supplier --}}
                                <small class="text-muted ms-3">
                                    Jatuh Tempo:
                                    @if ($supplier->jatuh_tempo1)
                                        Tgl {{ $supplier->jatuh_tempo1->format('d') }}
                                        @if ($supplier->jatuh_tempo2)
                                            & {{ $supplier->jatuh_tempo2->format('d') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </small>
                            </div>
                            <div class="data-card-actions fw-bold">
                                Total Tagihan: Rp {{ number_format($supplierPOs->sum('outstanding_amount'), 0, ',', '.') }}
                            </div>
                        </div>
                        <div class="data-card-body p-0"> {{-- p-0 agar tabel mepet --}}
                            <div class="table-responsive">
                                <table class="table table-hover table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">No. PO</th>
                                            <th>Tgl Order</th>
                                            <th class="text-end">Total PO</th>
                                            <th class="text-end">Sudah Dibayar</th>
                                            <th class="text-end text-danger">Sisa Tagihan</th>
                                            <th>Status Bayar</th>
                                            <th class="text-center pe-3">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($supplierPOs as $po)
                                            <tr>
                                                <td class="ps-3 fw-medium">{{ $po->po_number }}</td>
                                                <td>{{ $po->order_date->format('d/m/Y') }}</td>
                                                <td class="text-end">{{ number_format($po->total_amount, 0, ',', '.') }}
                                                </td>
                                                <td class="text-end">{{ number_format($po->paid_amount, 0, ',', '.') }}</td>
                                                <td class="text-end text-danger fw-bold">
                                                    {{ number_format($po->outstanding_amount, 0, ',', '.') }}</td>
                                                <td>
                                                    @if ($po->payment_status == 'lunas')
                                                        <span class="badge bg-success">Lunas</span>
                                                    @elseif($po->payment_status == 'sebagian_dibayar')
                                                        <span class="badge bg-warning text-dark">Sebagian</span>
                                                    @else
                                                        <span class="badge bg-danger">Belum Dibayar</span>
                                                    @endif
                                                </td>
                                                <td class="text-center pe-3">
                                                    <button type="button" class="btn btn-primary btn-sm btn-pay"
                                                        data-po-id="{{ $po->id }}"
                                                        data-po-number="{{ $po->po_number }}"
                                                        data-supplier-name="{{ $po->supplier->name }}"
                                                        data-total-amount="{{ $po->total_amount }}"
                                                        data-outstanding-amount="{{ $po->outstanding_amount }}"
                                                        data-bs-toggle="tooltip" title="Catat Pembayaran">
                                                        <i class="bi bi-cash-coin"></i> Bayar
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($groupedPOs->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i> Tidak ada Purchase Order Tempo yang perlu dibayar saat ini.
                </div>
            @endif

        </div>
    </div>

    <div class="modal fade" id="modalBayarSupplier" tabindex="-1" aria-labelledby="modalBayarSupplierLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formBayarSupplier" data-url="{{ route('acc.suppliers.payments.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="purchase_order_id" id="payment_po_id">

                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon"><i class="bi bi-cash-coin"></i></div>
                            <h5 class="modal-title" id="modalBayarSupplierLabel">Catat Pembayaran Supplier</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup"><i
                                class="bi bi-x"></i></button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formBayarAlert"></div>

                        <div class="mb-3 p-3 bg-light rounded border">
                            <dl class="row mb-0" style="font-size: 0.9em;">
                                <dt class="col-sm-4">Supplier</dt>
                                <dd class="col-sm-8" id="modal_supplier_name">-</dd>
                                <dt class="col-sm-4">No. PO</dt>
                                <dd class="col-sm-8 fw-bold" id="modal_po_number">-</dd>
                                <dt class="col-sm-4">Total Tagihan</dt>
                                <dd class="col-sm-8" id="modal_total_amount">Rp 0</dd>
                                <dt class="col-sm-4">Sisa Tagihan</dt>
                                <dd class="col-sm-8 text-danger fw-bold" id="modal_outstanding_amount">Rp 0</dd>
                            </dl>
                        </div>

                        <div class="form-group-custom">
                            <label for="payment_date" class="form-label-custom required">
                                <i class="bi bi-calendar-event"></i> Tanggal Bayar
                            </label>
                            <input type="date" class="form-control-custom" id="payment_date" name="payment_date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="amount" class="form-label-custom required">
                                <i class="bi bi-currency-dollar"></i> Jumlah Bayar (Rp)
                            </label>
                            <input type="number" class="form-control-custom" id="amount" name="amount"
                                min="1" step="any" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="payment_method" class="form-label-custom required">
                                <i class="bi bi-credit-card"></i> Metode Bayar
                            </label>
                            <select class="form-control-custom" id="payment_method" name="payment_method" required>
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash">Tunai (Cash)</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                        <div class="form-group-custom">
                            <label for="reference_number" class="form-label-custom">
                                <i class="bi bi-hash"></i> No. Referensi
                            </label>
                            <input type="text" class="form-control-custom" id="reference_number"
                                name="reference_number" placeholder="Opsional (No. Cek/Transfer)">
                        </div>

                        <div class="form-group-custom">
                            <label for="proof_file" class="form-label-custom"><i class="bi bi-file-earmark-arrow-up"></i>
                                Upload Bukti (Opsional)</label>
                            <input type="file" class="form-control-custom" id="proof_file" name="proof_file"
                                accept=".jpg,.jpeg,.png,.pdf">
                            <small class="form-text text-muted">Format: JPG, PNG, PDF. Maks: 2MB.</small>
                        </div>

                        <div class="form-group-custom">
                            <label for="payment_notes" class="form-label-custom">
                                <i class="bi bi-pencil-square"></i> Catatan
                            </label>
                            <textarea class="form-control-custom" id="payment_notes" name="notes" rows="2" placeholder="Opsional"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal"><i
                                class="bi bi-x"></i> Batal</button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanPembayaran"><i
                                class="bi bi-check"></i> Simpan Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formatCurrency = (value) => 'Rp ' + (value || 0).toLocaleString('id-ID');
            const modalBayarElement = document.getElementById('modalBayarSupplier');
            const modalBayar = modalBayarElement ? new bootstrap.Modal(modalBayarElement) : null;
            const formBayar = document.getElementById('formBayarSupplier');
            const alertBayar = document.getElementById('formBayarAlert');
            const btnSimpanPembayaran = document.getElementById('btnSimpanPembayaran');

            // Listener untuk tombol "Bayar" di tabel
            document.querySelectorAll('.btn-pay').forEach(button => {
                button.addEventListener('click', function() {
                    if (!formBayar || !modalBayar) return;

                    const poId = this.dataset.poId;
                    const poNumber = this.dataset.poNumber;
                    const supplierName = this.dataset.supplierName;
                    const totalAmount = parseFloat(this.dataset.totalAmount);
                    const outstandingAmount = parseFloat(this.dataset.outstandingAmount);

                    // Reset form & alert
                    formBayar.reset();
                    alertBayar.innerHTML = '';
                    alertBayar.style.display = 'none';
                    btnSimpanPembayaran.disabled = false;
                    btnSimpanPembayaran.innerHTML = '<i class="bi bi-check"></i> Simpan Pembayaran';

                    // Isi data ke modal
                    document.getElementById('payment_po_id').value = poId;
                    document.getElementById('modal_supplier_name').textContent = supplierName ||
                    '-';
                    document.getElementById('modal_po_number').textContent = poNumber || '-';
                    document.getElementById('modal_total_amount').textContent = formatCurrency(
                        totalAmount);
                    document.getElementById('modal_outstanding_amount').textContent =
                        formatCurrency(outstandingAmount);

                    // Set input amount default & max
                    const amountInput = document.getElementById('amount');
                    amountInput.value = outstandingAmount; // Default bayar lunas
                    amountInput.max = outstandingAmount; // Maksimal sisa tagihan
                    amountInput.min = 1; // Minimal bayar 1 rupiah

                    // Set tanggal default
                    document.getElementById('payment_date').valueAsDate = new Date();

                    modalBayar.show();
                });
            });

            // Listener untuk submit form pembayaran
            formBayar?.addEventListener('submit', function(e) {
                e.preventDefault();

                // Konfirmasi dengan swal sebelum proses
                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    text: 'Apakah Anda yakin ingin mencatat pembayaran supplier ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btnSimpanPembayaran.disabled = true;
                        btnSimpanPembayaran.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

                        const formData = new FormData(formBayar);
                        const url = formBayar.dataset.url;

                        Swal.fire({
                            title: 'Menyimpan pembayaran...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    // Jangan set Content-Type manual saat pakai FormData
                                    'X-CSRF-TOKEN': formBayar.querySelector('input[name="_token"]').value,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            })
                            .then(async response => {
                                let data;
                                try {
                                    data = await response.json();
                                } catch (err) {
                                    data = {
                                        status: 'error',
                                        message: 'Respon server tidak valid.'
                                    };
                                }

                                Swal.close();
                                btnSimpanPembayaran.disabled = false;
                                btnSimpanPembayaran.innerHTML = '<i class="bi bi-check"></i> Simpan Pembayaran';

                                if (response.ok && data.status === 'success') {
                                    modalBayar.hide();
                                    Swal.fire({
                                        title: 'Berhasil',
                                        text: data.message,
                                        icon: 'success'
                                    }).then(() => location.reload());
                                } else {
                                    // Tampilkan error di swal
                                    const errorMsg = data.errors ?
                                        Object.values(data.errors).map(e => e[0]).join('<br>') :
                                        (data.message || 'Gagal menyimpan data.');
                                    Swal.fire({
                                        title: 'Gagal!',
                                        html: errorMsg,
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.close();
                                btnSimpanPembayaran.disabled = false;
                                btnSimpanPembayaran.innerHTML = '<i class="bi bi-check"></i> Simpan Pembayaran';
                                Swal.fire({
                                    title: 'Error jaringan',
                                    text: error.message,
                                    icon: 'error'
                                });
                            });
                    }
                });
            });

            // Reset alert saat modal ditutup
            modalBayarElement?.addEventListener('hidden.bs.modal', function() {
                alertBayar.innerHTML = '';
                alertBayar.style.display = 'none';
                formBayar.reset();
            });

            // Inisialisasi tooltip (jika pakai)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

        }); // Akhir DOMContentLoaded
    </script>
@endpush
