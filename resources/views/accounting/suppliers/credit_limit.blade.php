@extends('app')

@section('style')
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            border-radius: 1rem;
            color: white;
            margin-bottom: 2rem;
        }

        .page-header .page-title {
            color: white;
            margin-bottom: 0.25rem;
        }

        .page-header .page-subtitle {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0;
        }

        .page-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hover-lift {
            transition: all 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
        }

        .progress {
            border-radius: 10px;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 10px;
            font-size: 0.875rem;
        }

        .card {
            border-radius: 1rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        @media (max-width: 768px) {
            .page-header-content {
                flex-direction: column;
                gap: 1rem;
            }

            .page-title-wrapper {
                text-align: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Monitoring Credit Limit</h1>
                            <p class="page-subtitle">Pantau penggunaan limit kredit supplier secara real-time</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary" id="btnRefresh">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-outline-success" id="btnExport">
                            <i class="bi bi-file-earmark-excel"></i> Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            @php
                $totalSuppliers = $suppliersData->count();
                $totalCreditLimit = $suppliersData->sum('credit_limit');
                $totalDebt = $suppliersData->sum('current_debt');
                $avgUsage = $totalSuppliers > 0 ? $suppliersData->avg('usage_percentage') : 0;
                $criticalSuppliers = $suppliersData->where('usage_percentage', '>', 75)->count();
            @endphp

            <div class="row g-3 mb-4" style="font-size: 0.93rem;">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body d-flex align-items-center">
                            <span
                                class="stat-icon-wrapper bg-primary bg-opacity-10 text-primary me-3 d-flex align-items-center justify-content-center rounded-3"
                                style="width:40px;height:40px;">
                                <i class="bi bi-people-fill fs-5"></i>
                            </span>
                            <div class="flex-grow-1">
                                <div class="small text-muted mb-0" style="font-size: 0.85em;">Total Supplier</div>
                                <div class="fw-semibold" style="font-size: 1.2em;">{{ $totalSuppliers }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body d-flex align-items-center">
                            <span
                                class="stat-icon-wrapper bg-success bg-opacity-10 text-success me-3 d-flex align-items-center justify-content-center rounded-3"
                                style="width:40px;height:40px;">
                                <i class="bi bi-wallet2 fs-5"></i>
                            </span>
                            <div class="flex-grow-1">
                                <div class="small text-muted mb-0" style="font-size: 0.85em;">Total Limit</div>
                                <div class="fw-semibold" style="font-size: 1.2em;">Rp
                                    {{ number_format($totalCreditLimit, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body d-flex align-items-center">
                            <span
                                class="stat-icon-wrapper bg-warning bg-opacity-10 text-warning me-3 d-flex align-items-center justify-content-center rounded-3"
                                style="width:40px;height:40px;">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            </span>
                            <div class="flex-grow-1">
                                <div class="small text-muted mb-0" style="font-size: 0.85em;">Total Utang</div>
                                <div class="fw-semibold" style="font-size: 1.2em;">Rp
                                    {{ number_format($totalDebt, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow h-100">
                        <div class="card-body d-flex align-items-center">
                            <span
                                class="stat-icon-wrapper bg-danger bg-opacity-10 text-danger me-3 d-flex align-items-center justify-content-center rounded-3"
                                style="width:40px;height:40px;">
                                <i class="bi bi-shield-exclamation fs-5"></i>
                            </span>
                            <div class="flex-grow-1">
                                <div class="small text-muted mb-0" style="font-size: 0.85em;">Status Kritis</div>
                                <div class="fw-semibold" style="font-size: 1.2em;">{{ $criticalSuppliers }} <span
                                        class="text-muted" style="font-size: 0.95em;">supplier</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchInput"
                                    placeholder="Cari nama supplier...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="safe">Aman (0-50%)</option>
                                <option value="warning">Hati-hati (51-75%)</option>
                                <option value="critical">Kritis (>75%)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="sortBy">
                                <option value="name">Urutkan: Nama</option>
                                <option value="usage_desc">Urutkan: Penggunaan (Tinggi)</option>
                                <option value="usage_asc">Urutkan: Penggunaan (Rendah)</option>
                                <option value="debt_desc">Urutkan: Utang (Besar)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Cards -->
            <div class="row g-3" id="supplierCards">
                @forelse($suppliersData as $supplier)
                    @php
                        // Tentukan status dan warna
                        $statusClass = 'success';
                        $statusText = 'Aman';
                        $statusIcon = 'check-circle-fill';

                        if ($supplier->usage_percentage > 75) {
                            $statusClass = 'danger';
                            $statusText = 'Kritis';
                            $statusIcon = 'exclamation-triangle-fill';
                        } elseif ($supplier->usage_percentage > 50) {
                            $statusClass = 'warning';
                            $statusText = 'Hati-hati';
                            $statusIcon = 'exclamation-circle-fill';
                        }
                    @endphp

                    <div class="col-12 col-xl-6 supplier-card" data-name="{{ strtolower($supplier->name) }}"
                        data-usage="{{ $supplier->usage_percentage }}" data-debt="{{ $supplier->current_debt }}"
                        data-status="{{ $statusText }}">
                        <div class="card border-0 shadow-sm h-100 hover-lift">
                            <div class="card-body">
                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="card-title mb-1 fw-bold">{{ $supplier->name }}</h5>
                                        <span class="badge bg-{{ $statusClass }} bg-opacity-10 text-{{ $statusClass }}">
                                            <i class="bi bi-{{ $statusIcon }} me-1"></i>{{ $statusText }}
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <div class="fs-4 fw-bold text-{{ $statusClass }}">
                                            {{ number_format($supplier->usage_percentage, 1) }}%</div>
                                        <small class="text-muted">Terpakai</small>
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $statusClass }} progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: {{ $supplier->usage_percentage }}%;"
                                            aria-valuenow="{{ $supplier->usage_percentage }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            <strong>{{ number_format($supplier->usage_percentage, 1) }}%</strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Details Grid -->
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <small class="text-muted d-block mb-1">Total Limit</small>
                                            <strong class="d-block text-truncate" style="font-size: 0.9rem;">
                                                Rp {{ number_format($supplier->credit_limit / 1000000, 1) }}M
                                            </strong>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <small class="text-muted d-block mb-1">Utang</small>
                                            <strong class="d-block text-truncate text-danger" style="font-size: 0.9rem;">
                                                Rp {{ number_format($supplier->current_debt / 1000000, 1) }}M
                                            </strong>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="p-2 bg-light rounded">
                                            <small class="text-muted d-block mb-1">Sisa Limit</small>
                                            <strong class="d-block text-truncate text-success" style="font-size: 0.9rem;">
                                                Rp {{ number_format($supplier->remaining_credit / 1000000, 1) }}M
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-sm btn-outline-secondary flex-fill"
                                        onclick="viewHistory({{ $supplier->id }})">
                                        <i class="bi bi-clock-history"></i> Riwayat
                                    </button>
                                </div>
                            </div>

                            <!-- Alert jika kritis -->
                            @if ($supplier->usage_percentage > 90)
                                <div class="card-footer bg-danger bg-opacity-10 border-0">
                                    <small class="text-danger fw-bold">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        Peringatan: Limit hampir habis! Segera lakukan pembayaran.
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">Tidak ada supplier dengan limit kredit</h5>
                                <p class="text-muted">Belum ada supplier yang menggunakan sistem limit kredit.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal Riwayat Pemakaian Limit -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="historyModalLabel">
                        Riwayat Pemakaian Limit: <span id="modalSupplierName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="historyLoader" class="text-center py-4" style="display:none">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <div>Memuat data riwayat...</div>
                    </div>
                    <div id="historyError" class="alert alert-danger d-none"></div>
                    <div id="historyContent" style="display:none">
                        <h6>Pemakaian Limit dari Purchase Order</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-sm align-middle text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>PO Number</th>
                                        <th>Deskripsi</th>
                                        <th>Total</th>
                                        <th>Terbayar</th>
                                        <th>Sisa Hutang</th>
                                    </tr>
                                </thead>
                                <tbody id="poHistoryBody">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <h6>Pembayaran Limit ke Supplier</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal Bayar</th>
                                        <th>Ref/No Transaksi</th>
                                        <th>Metode</th>
                                        <th>Jumlah</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody id="paymentHistoryBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterStatus = document.getElementById('filterStatus');
            const sortBy = document.getElementById('sortBy');
            const supplierCards = document.querySelectorAll('.supplier-card');

            // Search functionality
            searchInput?.addEventListener('keyup', function() {
                filterAndSort();
            });

            // Filter functionality
            filterStatus?.addEventListener('change', function() {
                filterAndSort();
            });

            // Sort functionality
            sortBy?.addEventListener('change', function() {
                filterAndSort();
            });

            function filterAndSort() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusFilter = filterStatus.value;
                let cardsArray = Array.from(supplierCards);

                // Filter
                cardsArray.forEach(card => {
                    const name = card.dataset.name;
                    const status = card.dataset.status.toLowerCase();
                    const usage = parseFloat(card.dataset.usage);

                    let showCard = true;

                    // Search filter
                    if (searchTerm && !name.includes(searchTerm)) {
                        showCard = false;
                    }

                    // Status filter
                    if (statusFilter) {
                        if (statusFilter === 'safe' && usage > 50) showCard = false;
                        if (statusFilter === 'warning' && (usage <= 50 || usage > 75)) showCard = false;
                        if (statusFilter === 'critical' && usage <= 75) showCard = false;
                    }

                    card.style.display = showCard ? '' : 'none';
                });

                // Sort
                const container = document.getElementById('supplierCards');
                const sortValue = sortBy.value;

                cardsArray.sort((a, b) => {
                    switch (sortValue) {
                        case 'name':
                            return a.dataset.name.localeCompare(b.dataset.name);
                        case 'usage_desc':
                            return parseFloat(b.dataset.usage) - parseFloat(a.dataset.usage);
                        case 'usage_asc':
                            return parseFloat(a.dataset.usage) - parseFloat(b.dataset.usage);
                        case 'debt_desc':
                            return parseFloat(b.dataset.debt) - parseFloat(a.dataset.debt);
                        default:
                            return 0;
                    }
                });

                // Re-append sorted cards
                cardsArray.forEach(card => container.appendChild(card));
            }

            // Refresh button
            document.getElementById('btnRefresh')?.addEventListener('click', function() {
                location.reload();
            });

            // Export button (you can implement actual export functionality)
            document.getElementById('btnExport')?.addEventListener('click', function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Info', 'Fitur export sedang dalam pengembangan', 'info');
                } else {
                    alert('Fitur export sedang dalam pengembangan');
                }
            });
        });

        let historyModalEl, historyModal;

        function formatRupiah(val) {
            val = val ?? 0;
            return 'Rp ' + (parseFloat(val) || 0).toLocaleString('id-ID');
        }

        function viewHistory(supplierId) {
            const cardBtn = document.querySelector(`.supplier-card button[onclick*="viewHistory(${supplierId})"]`);
            let supplierName = 'Supplier';
            if (cardBtn) {
                const card = cardBtn.closest('.supplier-card');
                const titleEl = card ? card.querySelector('.card-title') : null;
                if (titleEl) supplierName = titleEl.textContent.trim();
            }

            // Tampilkan di header modal
            const nameModalEl = document.getElementById('modalSupplierName');
            if (nameModalEl) nameModalEl.textContent = supplierName;

            // Reset tampilan modal: loader, isi, error
            document.getElementById('historyLoader').style.display = 'block';
            document.getElementById('historyContent').style.display = 'none';
            document.getElementById('historyError').classList.add('d-none');
            document.getElementById('poHistoryBody').innerHTML =
                '<tr><td colspan="6" class="text-center text-muted">Memuat...</td></tr>';
            document.getElementById('paymentHistoryBody').innerHTML =
                '<tr><td colspan="5" class="text-center text-muted">Memuat...</td></tr>';

            // Inisialisasi modal jika belum
            if (!historyModalEl) {
                historyModalEl = document.getElementById('historyModal');
                historyModal = new bootstrap.Modal(historyModalEl);
            }
            historyModal.show();

            // Route: /acc/suppliers/{supplier}/credit-history
            // Helper route name: acc.suppliers.credit.history
            // Param name is {supplier}
            const urlTemplate = "{{ route('acc.suppliers.credit.history', ['supplier' => ':supplierId']) }}";
            const fetchUrl = urlTemplate.replace(':supplierId', supplierId);

            console.log("Fetching history from:", fetchUrl);

            fetch(fetchUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async resp => {
                    if (!resp.ok) {
                        let errorData;
                        try {
                            errorData = await resp.json();
                        } catch (e) {
                            errorData = {
                                message: resp.statusText
                            };
                        }
                        throw new Error(errorData.message || `HTTP error! status: ${resp.status}`);
                    }
                    return resp.json();
                })
                .then(data => {
                    document.getElementById('historyLoader').style.display = 'none';
                    if (data.status === 'success') {
                        document.getElementById('historyContent').style.display = 'block';
                        fillPoHistory(data.po_history || []);
                        fillPaymentHistory(data.payment_history || []);
                    } else {
                        document.getElementById('historyError').textContent = data.message || 'Gagal memuat data.';
                        document.getElementById('historyError').classList.remove('d-none');
                    }
                })
                .catch(err => {
                    console.error('Fetch history error:', err);
                    document.getElementById('historyLoader').style.display = 'none';
                    document.getElementById('historyError').textContent = "Gagal mengambil data riwayat: " + (err
                        .message || err);
                    document.getElementById('historyError').classList.remove('d-none');
                });
        }

        function fillPoHistory(poArr) {
            const tbody = document.getElementById('poHistoryBody');
            tbody.innerHTML = ''; // Kosongkan tabel

            if (!poArr || !poArr.length) {
                tbody.innerHTML =
                    '<tr><td colspan="6" class="text-center text-muted">Tidak ada riwayat PO Tempo</td></tr>';
                return;
            }

            poArr.forEach(po => {
                const tr = document.createElement('tr');

                tr.innerHTML = `
                                <td>${po.order_date || '-'}</td>
                                <td>${po.po_number || '-'}</td>
                                <td>${po.description || '-'}</td>
                                <td class="text-end">${formatRupiah(po.total_amount)}</td>
                                <td class="text-end">${formatRupiah(po.paid_amount)}</td>
                                <td class="text-end text-danger fw-bold">${formatRupiah(po.outstanding_amount)}</td> 
                            `;

                tbody.appendChild(tr); // Tambahkan baris ke tabel
            });
        }

        function fillPaymentHistory(payArr) {
            const tbody = document.getElementById('paymentHistoryBody');
            tbody.innerHTML = '';
            if (!payArr || !payArr.length) {
                tbody.innerHTML =
                    '<tr><td colspan="5" class="text-center text-muted">Tidak ada riwayat pembayaran</td></tr>';
                return;
            }
            payArr.forEach(pay => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${pay.payment_date || '-'}</td>
                    <td>${pay.reference_number || '-'}</td>
                    <td>${pay.payment_method || '-'}</td>
                    <td class="text-end">${formatRupiah(pay.amount)}</td>
                    <td>${pay.notes || ''}</td>
                `;
                tbody.appendChild(tr);
            });
        }
    </script>
@endpush
