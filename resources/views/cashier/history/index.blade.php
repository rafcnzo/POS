@extends('app')

@section('style')
    <style>
        .form-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: #333;
        }

        .form-control,
        .input-group-text {
            border-radius: 0.375rem;
            border-color: #ced4da;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .input-group-text {
            background-color: #f8f9fa;
        }

        .btn-primary {
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
        }

        @media (max-width: 576px) {
            .form-label {
                font-size: 0.85rem;
            }

            .btn-primary {
                padding: 0.4rem 0.8rem;
            }
        }

        /* Modal Styling */
        #historyDetailModal .modal-dialog {
            max-width: 800px;
        }

        #historyDetailModal .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        #historyDetailModal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }

        #historyDetailModal .modal-header .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        #historyDetailModal .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        #historyDetailModal .modal-header .btn-close:hover {
            opacity: 1;
        }

        #historyDetailModal .modal-body {
            padding: 1.5rem;
            max-height: 70vh;
            overflow-y: auto;
        }

        /* Transaction Header Section */
        #historyDetailContent h5 {
            color: #2d3748;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        #historyDetailContent .transaction-header {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        #historyDetailContent .fw-semibold {
            color: #4a5568;
            font-weight: 600;
        }

        #historyDetailContent hr {
            border-color: #e2e8f0;
            margin: 1.5rem 0;
        }

        /* Section Headers */
        #historyDetailContent h6 {
            color: #2d3748;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
            display: inline-block;
        }

        /* Table Styling */
        #historyDetailContent .table {
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        #historyDetailContent .table thead {
            background: #f7fafc;
        }

        #historyDetailContent .table thead th {
            color: #4a5568;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.75rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #historyDetailContent .table tbody td {
            padding: 0.75rem;
            vertical-align: middle;
            border-color: #e2e8f0;
        }

        #historyDetailContent .table tbody tr:hover {
            background-color: #f7fafc;
            transition: background-color 0.2s ease;
        }

        /* Extra Modifiers Styling */
        #historyDetailContent .table td small {
            font-size: 0.8rem;
        }

        #historyDetailContent .table td div {
            font-size: 0.85rem;
            color: #718096;
            padding: 2px 0;
        }

        /* Summary Section */
        #historyDetailContent>div:not(.mb-3):not(.row) {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        #historyDetailContent>div:not(.mb-3):not(.row)>div {
            padding: 0.5rem 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e2e8f0;
        }

        #historyDetailContent>div:not(.mb-3):not(.row)>div:last-child {
            border-bottom: none;
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
            padding-top: 0.75rem;
            margin-top: 0.5rem;
            border-top: 2px solid #667eea;
        }

        /* Payment Section */
        #historyDetailContent .table.mb-0 {
            margin-bottom: 0 !important;
        }

        /* Status Badge Styling */
        .badge {
            padding: 0.35em 0.65em;
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* Loading State */
        #historyDetailLoading {
            text-align: center;
            padding: 3rem 1rem;
        }

        #historyDetailLoading .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
            color: #667eea;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            #historyDetailModal .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }

            #historyDetailModal .modal-body {
                padding: 1rem;
                max-height: 60vh;
            }

            #historyDetailContent .table {
                font-size: 0.8rem;
            }

            #historyDetailContent .table thead th,
            #historyDetailContent .table tbody td {
                padding: 0.5rem;
            }

            #historyDetailContent h5 {
                font-size: 1rem;
            }

            #historyDetailContent .row>div {
                margin-bottom: 0.5rem;
            }
        }

        /* Custom Scrollbar */
        #historyDetailModal .modal-body::-webkit-scrollbar {
            width: 8px;
        }

        #historyDetailModal .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        #historyDetailModal .modal-body::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        #historyDetailModal .modal-body::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* Animation */
        #historyDetailContent {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Print Friendly (Optional) */
        @media print {

            #historyDetailModal .modal-header,
            #historyDetailModal .btn-close {
                display: none;
            }

            #historyDetailModal .modal-body {
                max-height: none;
                overflow: visible;
            }
        }
    </style>
@endsection

@section('content')
<div class="page-content">
    <div class="container-fluid">
        
        <!-- Page Header Section -->
        <header class="page-header mb-4">
            <div class="page-header-content">
                <div class="d-flex align-items-center gap-3">
                    <div class="page-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div class="page-info">
                        <h1 class="page-title mb-1">Riwayat Penjualan</h1>
                        <p class="page-subtitle mb-0">Lihat dan telusuri riwayat transaksi penjualan</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Filter & Search Section -->
        <section class="filter-section mb-4">
            <div class="data-card">
                <div class="data-card-body p-4">
                    <form method="GET" id="filterForm">
                        <div class="row g-3">
                            <!-- Date Range Filters -->
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <label for="start_date" class="form-label">Dari Tanggal</label>
                                <input type="date" 
                                       name="start_date" 
                                       id="start_date" 
                                       class="form-control"
                                       value="{{ request('start_date') }}"
                                       aria-label="Tanggal mulai">
                            </div>

                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <label for="end_date" class="form-label">Sampai Tanggal</label>
                                <input type="date" 
                                       name="end_date" 
                                       id="end_date" 
                                       class="form-control"
                                       value="{{ request('end_date') }}"
                                       aria-label="Tanggal akhir">
                            </div>

                            <!-- Search Input -->
                            <div class="col-lg-4 col-md-8">
                                <label for="search" class="form-label">Cari Transaksi</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           name="search" 
                                           id="search" 
                                           class="form-control"
                                           placeholder="Nomor transaksi atau nama kasir" 
                                           value="{{ request('search') }}"
                                           aria-label="Pencarian transaksi">
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100" aria-label="Terapkan filter">
                                    <i class="bi bi-funnel me-1"></i>
                                    <span>Filter</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- Data Table Section -->
        <section class="table-section">
            <div class="data-card">
                <div class="data-card-header">
                    <h5 class="mb-0">Daftar Transaksi</h5>
                </div>

                <div class="data-card-body">
                    <div class="table-responsive">
                        <table class="table data-table table-hover" id="salesHistoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                    <th scope="col" style="width: 150px;">No. Transaksi</th>
                                    <th scope="col" style="width: 180px;">Tanggal/Waktu</th>
                                    <th scope="col" style="width: 150px;">Kasir</th>
                                    <th scope="col" class="text-end" style="width: 150px;">Total</th>
                                    <th scope="col" class="text-center" style="width: 120px;">Status</th>
                                    <th scope="col" class="text-center" style="width: 140px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                @forelse($sales as $i => $sale)
                                <tr>
                                    <td class="text-center">{{ ($sales->firstItem() ?? 1) + $i }}</td>
                                    <td>
                                        <span class="fw-semibold text-primary">
                                            {{ $sale->transaction_code ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium">
                                                {{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y') }}
                                            </span>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($sale->created_at)->format('H:i') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>{{ $sale->user->name ?? '-' }}</td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-info btn-sm btnDetailHistory"
                                                data-id="{{ $sale->id }}"
                                                aria-label="Lihat detail transaksi {{ $sale->transaction_code }}">
                                            <i class="bi bi-eye me-1"></i>
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state text-center py-5">
                                            <div class="empty-icon mb-3">
                                                <i class="bi bi-clock-history display-1 text-muted"></i>
                                            </div>
                                            <h4 class="text-muted mb-2">Belum Ada Riwayat Transaksi</h4>
                                            <p class="text-muted mb-0">
                                                Belum ada transaksi yang selesai.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($sales->hasPages())
                    <nav aria-label="Navigasi halaman" class="mt-4">
                        <div id="paginationContainer">
                            {{ $sales->withQueryString()->links() }}
                        </div>
                    </nav>
                    @endif
                </div>
            </div>
        </section>

    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" 
     id="historyDetailModal" 
     tabindex="-1" 
     aria-labelledby="historyDetailModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            
            <!-- Modal Header -->
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title mb-0" id="historyDetailModalLabel">
                        Detail Transaksi
                    </h5>
                </div>
                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="modal" 
                        aria-label="Tutup modal">
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Loading State -->
                <div id="historyDetailLoading" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Memuat data...</span>
                    </div>
                    <p class="text-muted mb-0">Memuat data transaksi...</p>
                </div>

                <!-- Content Container -->
                <div id="historyDetailContent" style="display: none;">
                    <!-- Data detail akan diisi via JavaScript -->
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light">
                <button type="button" 
                        class="btn btn-secondary" 
                        data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Tutup
                </button>
                <button type="button" 
                        class="btn btn-primary" 
                        id="btnPrintHistoryDetail"
                        aria-label="Print detail transaksi">
                    <i class="bi bi-printer me-1"></i>
                    Print
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script')
    <script>
        let currentSaleId = null;

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btnDetailHistory').forEach(btn => {
                btn.addEventListener('click', function() {
                    let saleId = this.dataset.id;
                    currentSaleId = saleId; // Track sale id for print
                    let modal = new bootstrap.Modal(document.getElementById('historyDetailModal'));
                    document.getElementById('historyDetailLoading').style.display = '';
                    document.getElementById('historyDetailContent').style.display = 'none';
                    modal.show();

                    showLoading();
                    fetch(`/cashier/history/${saleId}`)
                        .then(response => response.json())
                        .then(res => {
                            hideLoading();
                            if (res.status !== 'success') {
                                Swal.fire('Error', res.message ?? 'Data tidak ditemukan.',
                                    'error');
                                modal.hide();
                                return;
                            }
                            // Render detail transaksi
                            let sale = res.sale;
                            let html = `
                            <div class="mb-3">
                                <h5>No Transaksi: <span class="fw-semibold">${sale.transaction_code ?? '-'}</span></h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div><span class="fw-semibold">Tanggal:</span> ${formatDateTime(sale.created_at)}</div>
                                        <div><span class="fw-semibold">Kasir:</span> ${sale.user?.name ?? '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div><span class="fw-semibold">Status:</span> ${statusLabel(sale.status)}</div>
                                        <div><span class="fw-semibold">Grand Total:</span> Rp ${numberWithSeparator(sale.total_amount)}</div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <h6>Daftar Item</h6>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Menu Item</th>
                                        <th>Qty</th>
                                        <th>Harga Satuan</th>
                                        <th>Extra</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${sale.items.map(item => `
                                                    <tr>
                                                        <td>${item.menu_item?.name ?? '-'}</td>
                                                        <td>${item.quantity}</td>
                                                        <td>Rp ${numberWithSeparator(item.price)}</td>
                                                        <td>
                                                            ${item.selected_modifiers && item.selected_modifiers.length > 0 
                                                                ? item.selected_modifiers.map(sm => `<div>+ ${sm.modifier?.name ?? '-'}</div>`).join('')
                                                                : '<small class="text-muted">-</small>'
                                                            }
                                                        </td>
                                                        <td>Rp ${numberWithSeparator((item.price + ((item.selected_modifiers || []).reduce((a, s) => a + (parseInt(s.modifier?.price ?? 0)),0))) * item.quantity)}</td>
                                                    </tr>
                                                `).join('')}
                                </tbody>
                            </table>
                            <div>
                                <div><span class="fw-semibold">Subtotal:</span> Rp ${numberWithSeparator(sale.subtotal)}</div>
                                <div><span class="fw-semibold">Tax:</span> Rp ${numberWithSeparator(sale.tax_amount)}</div>
                                <div><span class="fw-semibold">Discount:</span> Rp ${numberWithSeparator(sale.discount_amount)}</div>
                                <div><span class="fw-semibold">Grand Total:</span> Rp ${numberWithSeparator(sale.total_amount)}</div>
                            </div>
                            <hr>
                            <h6>Pembayaran</h6>
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Metode</th>
                                        <th>Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${sale.payments && sale.payments.length > 0 
                                        ? sale.payments.map(pay => `
                                                        <tr>
                                                            <td>${(pay.payment_method ?? '-').toUpperCase()}</td>
                                                            <td>Rp ${numberWithSeparator(pay.amount ?? 0)}</td>
                                                        </tr>
                                                    `).join('') : '<tr><td colspan="2">-</td></tr>'}
                                </tbody>
                            </table>
                        `;
                            document.getElementById('historyDetailContent').innerHTML = html;
                            document.getElementById('historyDetailLoading').style.display =
                                'none';
                            document.getElementById('historyDetailContent').style.display = '';
                        })
                        .catch(err => {
                            hideLoading();
                            Swal.fire('Error', 'Gagal memuat detail transaksi.', 'error');
                            let modalObj = bootstrap.Modal.getInstance(document.getElementById(
                                'historyDetailModal'));
                            if (modalObj) modalObj.hide();
                        });
                });
            });

            document.getElementById('btnPrintHistoryDetail').addEventListener('click', function() {
                if (!currentSaleId) {
                    Swal.fire('Error', 'Tidak ada transaksi yang dipilih untuk dicetak.', 'error');
                    return;
                }
                const url = `/cashier/sales/${currentSaleId}/print/customer`;

                showLoading && showLoading('Menyiapkan struk...');

                fetch(url)
                    .then(response => {
                        if (showLoading) hideLoading();
                        if (response.headers.get("content-type")?.includes("text/html")) {
                            return response.text().then(html => {
                                const printWindow = window.open('', '_blank');
                                printWindow.document.write(html);
                                printWindow.document.close();
                                printWindow.focus();
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.status) {
                            if (data.status === 'success' && data.message) {
                                toastr && toastr.success(data.message);
                            } else if (data.status === 'error') {
                                Swal.fire('Error', data.message || 'Gagal mencetak struk.', 'error');
                            }
                        }
                    })
                    .catch(() => {
                        if (showLoading) hideLoading();
                        Swal.fire('Error', 'Gagal menghubungi server cetak.', 'error');
                    });
            });

            function formatDateTime(dt) {
                if (!dt) return '-';
                const dateObj = new Date(dt);
                return dateObj.toLocaleString('id-ID');
            }

            function numberWithSeparator(x) {
                if (!x) return '0';
                return parseInt(x).toLocaleString('id-ID');
            }

            function statusLabel(status) {
                if (status === 'completed') return '<span class="badge bg-success">Completed</span>';
                if (status === 'pending') return '<span class="badge bg-secondary">Pending</span>';
                return '<span class="badge bg-light">' + (status ?? '-') + '</span>';
            }
        });
    </script>
@endpush
