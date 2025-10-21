@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-list-task"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Purchase Order</h1>
                            <p class="page-subtitle">Kelola data purchase order (PO) pembelian</p>
                        </div>
                    </div>
                    <button type="button" class="btn-add-primary" id="btnTambahPO" data-bs-toggle="modal"
                        data-bs-target="#modalTambahPO">
                        <i class="bi bi-plus-circle"></i>
                        <span>Buat Purchase Order</span>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-clipboard-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $purchaseOrders->count() }}</h3>
                        <p class="stat-label">Total PO</p>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">Rp
                            {{ number_format($purchaseOrders->sum('total_amount'), 0, ',', '.') }}
                        </h3>
                        <p class="stat-label">Total Nilai PO</p>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Purchase Order</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari PO..." id="searchInput">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-po"
                            data-url="{{ route('prc.purchase_orders.destroy', ['purchaseOrder' => 0]) }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Tanggal</th>
                                    <th class="col-secondary">No. PO</th>
                                    <th class="col-secondary">Supplier</th>
                                    <th class="col-secondary">Store Request</th>
                                    <th class="col-currency">Total Nilai</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchaseOrders as $key => $po)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span
                                                    class="item-name">{{ \Carbon\Carbon::parse($po->order_date)->format('d M Y') }}</span>
                                            </div>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $po->po_number ?? '-' }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span>{{ $po->supplier->name ?? '-' }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span>{{ $po->storeRequest->request_number ?? '-' }}</span>
                                        </td>
                                        <td class="col-currency">
                                            <span class="price-value">Rp
                                                {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button type="button" class="btn-action btn-detail btnModalDetailPO"
                                                    data-id="{{ $po->id }}" data-bs-toggle="tooltip" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusPO"
                                                    data-id="{{ $po->id }}" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-file-earmark"></i>
                                                <h4>Belum ada data purchase order</h4>
                                                <p>Klik tombol "Buat Purchase Order" untuk memulai</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah PO -->
    <div class="modal fade" id="modalTambahPO" tabindex="-1" aria-labelledby="modalTambahPOLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 900px;">
            <div class="modal-content" style="max-height:90vh; overflow:hidden; display:flex; flex-direction:column;">
                <form id="formTambahPO" data-url="{{ route('prc.purchase_orders.submit') }}" enctype="multipart/form-data"
                    style="height:100%;">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahPOLabel"><i class="bi bi-plus-circle"></i> Tambah Purchase
                            Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body" style="overflow-y:auto; max-height:60vh;">
                        <div id="formPOAlert" style="display:none"></div>

                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Pilih Supplier <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" data-type="{{ $supplier->type }}"
                                        data-credit-limit="{{ $supplier->credit_limit ?? 0 }}">
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Supplier Info Display -->
                        <div id="poSupplierInfo" class="mb-3 p-2 border rounded" style="display: none;">
                            <small>
                                <strong>Tipe:</strong> <span id="poSupplierTypeBadge" class="badge"></span>
                                <span id="poCreditLimitInfo" style="display: none; margin-left: 10px;"></span>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label for="store_request_id" class="form-label">Pilih Store Request</label>
                            <select class="form-select" id="store_request_id" name="store_request_id">
                                <option value="">-- Pilih Store Request --</option>
                                @foreach ($storeRequests as $sr)
                                    <option value="{{ $sr->id }}">{{ $sr->request_number }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Opsional, kosongkan jika tidak dari store request.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Metode Pembayaran <span
                                    class="text-danger">*</span></label>
                            <div id="poPaymentMethodOptions">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="payment_type"
                                        id="po_pay_cash" value="petty_cash" checked required>
                                    <label class="form-check-label" for="po_pay_cash">Cash</label>
                                </div>
                                <div class="form-check form-check-inline" id="poTempoOption" style="display: none;">
                                    <input class="form-check-input" type="radio" name="payment_type"
                                        id="po_pay_tempo" value="tempo" required>
                                    <label class="form-check-label" for="po_pay_tempo">
                                        <strong class="text-primary">Gunakan Tempo</strong>
                                    </label>
                                </div>
                            </div>
                            <div id="poTempoWarning" class="form-text text-danger mt-1 fw-bold" style="display: none;">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Tambah catatan..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Detail Item Barang <span class="text-danger">*</span></label>
                            <div id="poItemsContainer">
                                <div class="text-muted">Pilih Store Request terlebih dahulu untuk menampilkan item, atau
                                    tambahkan manual jika tanpa Store Request.</div>
                            </div>
                            <div id="addManualItemsBlock" style="display: none;">
                                <table class="table table-sm table-bordered align-middle mb-2" id="manualItemsTable">
                                    <thead>
                                        <tr>
                                            <th>Nama Bahan</th>
                                            <th>Qty</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Dynamically added -->
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddManualItem">
                                    <i class="bi bi-plus-lg"></i> Tambah Item
                                </button>
                            </div>
                        </div>

                        <!-- Total Display -->
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Total Nilai PO:</strong>
                                <h4 class="mb-0" id="poTotalAmountDisplay">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="flex-shrink:0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmitPO">
                            <i class="bi bi-save"></i> Simpan PO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail PO (Show) -->
    <div class="modal fade" id="modalDetailPO" tabindex="-1" aria-labelledby="modalDetailPOLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 900px;">
            <div class="modal-content" style="max-height:90vh; overflow:hidden; display:flex; flex-direction:column;">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailPOLabel">
                        <i class="bi bi-eye"></i> Detail Purchase Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" style="overflow-y:auto; max-height:60vh;">
                    <div id="poDetailLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border" role="status"></div>
                        <div>Memuat detail PO...</div>
                    </div>
                    <div id="poDetailContent" style="display: none;"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between" style="flex-shrink:0;">
                    <a id="btnPrintPO" href="#" class="btn btn-outline-secondary" target="_blank"
                        style="display: none;">
                        <i class="bi bi-printer"></i> Print PO
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Variabel & Elemen DOM ---
            let poManualItemIdx = 0;
            const poIngredientOptions = {!! json_encode(
                \App\Models\Ingredient::orderBy('name')->select(['id', 'name', 'unit'])->get()->map(function ($i) {
                        return [
                            'id' => $i->id,
                            'name' => $i->name,
                            'unit' => $i->unit,
                        ];
                    })->values(),
            ) !!};

            // Elemen Modal Tambah PO
            const modalTambahPO = document.getElementById('modalTambahPO');
            const formTambahPO = document.getElementById('formTambahPO');
            const poFormAlert = document.getElementById('formPOAlert');
            const poSupplierSelect = document.getElementById('supplier_id');
            const poStoreRequestSelect = document.getElementById('store_request_id');
            const poSupplierInfoDiv = document.getElementById('poSupplierInfo');
            const poSupplierTypeBadge = document.getElementById('poSupplierTypeBadge');
            const poCreditLimitInfo = document.getElementById('poCreditLimitInfo');
            const poTempoOptionDiv = document.getElementById('poTempoOption');
            const poTempoWarningDiv = document.getElementById('poTempoWarning');
            const poPaymentMethodRadios = document.querySelectorAll('input[name="payment_type"]');
            const poItemsContainer = document.getElementById('poItemsContainer');
            const poAddManualItemsBlock = document.getElementById('addManualItemsBlock');
            const poManualItemsTableBody = document.querySelector('#manualItemsTable tbody');
            const poBtnAddManualItem = document.getElementById('btnAddManualItem');
            const poTotalAmountDisplay = document.getElementById('poTotalAmountDisplay');
            const poBtnSubmit = document.getElementById('btnSubmitPO');
            const btnPrintPO = document.getElementById('btnPrintPO');

            // State
            let poCurrentSupplierData = null;
            let poCurrentTotal = 0;

            // --- Fungsi Helper ---
            const formatCurrency = (value) => 'Rp ' + (value || 0).toLocaleString('id-ID');

            function showLoading(message = 'Memproses...') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: message,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }
            }

            function hideLoading() {
                if (typeof Swal !== 'undefined') Swal.close();
            }

            // --- Fungsi Reset ---
            function resetManualItems() {
                if (poManualItemsTableBody) poManualItemsTableBody.innerHTML = '';
                poManualItemIdx = 0;
            }

            // --- Fungsi Tambah Baris Item Manual ---
            function addManualItemRowPO(data = {}) {
                if (!poManualItemsTableBody) return;
                let idx = `manual_${poManualItemIdx++}`;
                let row = document.createElement('tr');
                row.dataset.rowId = idx;
                row.innerHTML = `
            <td>
                <select class="form-select form-select-sm ingredient-select" name="items[${idx}][ingredient_id]" required>
                    <option value="">-- Pilih --</option>
                    ${poIngredientOptions.map(opt => `<option value="${opt.id}" data-unit="${opt.unit || 'Unit'}" ${data.ingredient_id==opt.id?' selected':''}>${opt.name}</option>`).join('')}
                </select>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" min="0.01" step="any" class="form-control item-qty-manual" name="items[${idx}][quantity]" value="${data.quantity||''}" required placeholder="Qty">
                    <span class="input-group-text item-unit-manual">${data.unit || 'Unit'}</span>
                </div>
            </td>
            <td>
                 <input type="number" min="0" step="any" class="form-control form-control-sm item-price-manual" name="items[${idx}][price]" value="${data.price||''}" required placeholder="Harga">
            </td>
            <td class="text-end">
                <strong class="item-subtotal-manual">Rp 0</strong>
            </td>
            <td class="text-center">
                 <button type="button" class="btn btn-danger btn-sm btnRemoveManualItem"><i class="bi bi-trash"></i></button>
            </td>
        `;
                poManualItemsTableBody.appendChild(row);
            }

            // Update Satuan/unit di baris manual
            function updateManualItemUnit(event) {
                const select = event.target;
                const selectedOption = select.options[select.selectedIndex];
                const unitSpan = select.closest('tr')?.querySelector('.item-unit-manual');
                if (unitSpan) {
                    unitSpan.textContent = selectedOption.dataset.unit || 'Unit';
                }
            }

            // Show/Hide blok manual
            function showManualBlock(active) {
                if (!poAddManualItemsBlock || !poItemsContainer) return;
                poAddManualItemsBlock.style.display = active ? 'block' : 'none';

                if (active && poManualItemsTableBody && poManualItemsTableBody.children.length === 0) {
                    addManualItemRowPO();
                } else if (!active) {
                    resetManualItems();
                }
            }

            // --- UI Supplier & Opsi Tempo ---
            function updateSupplierUI(supplierData) {
                poCurrentSupplierData = supplierData;

                if (poSupplierTypeBadge) {
                    poSupplierTypeBadge.textContent = '';
                    poSupplierTypeBadge.className = 'badge';
                }
                if (poCreditLimitInfo) poCreditLimitInfo.style.display = 'none';
                if (poTempoOptionDiv) poTempoOptionDiv.style.display = 'none';
                if (poTempoWarningDiv) poTempoWarningDiv.style.display = 'none';

                const cashRadio = document.getElementById('po_pay_cash');
                if (cashRadio) cashRadio.checked = true;

                if (supplierData) {
                    if (poSupplierTypeBadge) {
                        poSupplierTypeBadge.textContent = supplierData.type === 'tempo' ? 'Tempo' : 'Petty Cash';
                        poSupplierTypeBadge.classList.add(supplierData.type === 'tempo' ? 'bg-info' :
                            'bg-secondary');
                    }
                    if (poSupplierInfoDiv) poSupplierInfoDiv.style.display = 'block';

                    if (supplierData.type === 'tempo') {
                        if (poCreditLimitInfo) {
                            poCreditLimitInfo.textContent =
                                `Sisa Limit: ${formatCurrency(supplierData.credit_limit)}`;
                            poCreditLimitInfo.style.display = 'inline';
                        }
                        if (poTempoOptionDiv) poTempoOptionDiv.style.display = 'inline-block';
                        checkCreditLimit();
                    } else {
                        const tempoRadio = document.getElementById('po_pay_tempo');
                        if (tempoRadio && tempoRadio.checked && cashRadio) {
                            cashRadio.checked = true;
                        }
                    }
                } else {
                    if (poSupplierInfoDiv) poSupplierInfoDiv.style.display = 'none';
                }
            }

            function checkCreditLimit() {
                if (poTempoWarningDiv) poTempoWarningDiv.style.display = 'none';
                if (poBtnSubmit) poBtnSubmit.disabled = false;

                if (poCurrentSupplierData && poCurrentSupplierData.type === 'tempo') {
                    const tempoRadio = document.getElementById('po_pay_tempo');
                    const isTempoSelected = tempoRadio && tempoRadio.checked;

                    if (isTempoSelected && poCurrentTotal > poCurrentSupplierData.credit_limit) {
                        if (poTempoWarningDiv) {
                            poTempoWarningDiv.textContent =
                                `PERINGATAN: Total PO (${formatCurrency(poCurrentTotal)}) melebihi sisa limit kredit (${formatCurrency(poCurrentSupplierData.credit_limit)}).`;
                            poTempoWarningDiv.style.display = 'block';
                        }
                        if (poBtnSubmit) poBtnSubmit.disabled = true;
                    }
                }
            }

            // --- Hitung Total PO ---
            function calculateTotalPO() {
                poCurrentTotal = 0;

                // Hitung dari item SR atau manual
                document.querySelectorAll('input[name$="[quantity]"]').forEach(qtyInput => {
                    const row = qtyInput.closest('tr') || qtyInput.closest('.row');
                    if (!row) return;

                    const priceInput = row.querySelector('input[name$="[price]"]');
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
                    const subtotal = qty * price;

                    const subtotalDisplay = row.querySelector('.po-item-subtotal, .item-subtotal-manual');
                    if (subtotalDisplay) subtotalDisplay.textContent = formatCurrency(subtotal);

                    poCurrentTotal += subtotal;
                });

                if (poTotalAmountDisplay) poTotalAmountDisplay.textContent = formatCurrency(poCurrentTotal);
                checkCreditLimit();
            }

            // --- Event Listeners ---

            // Supplier select change
            if (poSupplierSelect) {
                poSupplierSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];

                    if (selectedOption && selectedOption.value) {
                        const supplierData = {
                            id: selectedOption.value,
                            type: selectedOption.dataset.type || 'petty_cash',
                            credit_limit: parseFloat(selectedOption.dataset.creditLimit) || 0
                        };
                        updateSupplierUI(supplierData);
                    } else {
                        updateSupplierUI(null);
                    }
                });
            }

            // Payment method change
            poPaymentMethodRadios.forEach(radio => {
                radio.addEventListener('change', checkCreditLimit);
            });

            // Store Request select change
            if (poStoreRequestSelect) {
                poStoreRequestSelect.addEventListener('change', function() {
                    const srId = this.value;
                    poItemsContainer.innerHTML = '';
                    resetManualItems();

                    if (!srId) {
                        poItemsContainer.innerHTML =
                            '<div class="text-muted">Tambah item manual jika tanpa Store Request.</div>';
                        showManualBlock(true);
                        calculateTotalPO();
                        return;
                    }

                    showManualBlock(false);
                    poItemsContainer.innerHTML =
                        '<div class="text-center p-3 text-muted"><div class="spinner-border spinner-border-sm"></div> Memuat item SR...</div>';

                    const fetchUrl =
                        "{{ route('prc.purchase_orders.sr_items', ['storeRequestId' => ':id']) }}".replace(
                            ':id', srId);

                    fetch(fetchUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.ok ? res.json() : Promise.reject('Gagal memuat SR'))
                        .then(data => {
                            if (data.status === 'success' && data.items && data.items.length > 0) {
                                let html = `<table class="table table-sm table-bordered align-middle">
                                    <thead><tr><th>Nama Bahan</th><th>Qty Diminta</th><th>Harga Satuan</th><th class="text-end">Subtotal</th></tr></thead>
                                    <tbody>`;

                                data.items.forEach((item, idx) => {
                                    let defaultPrice = 0;
                                    let subtotal = (parseFloat(item.quantity) || 0) *
                                        defaultPrice;
                                    html += `<tr>
                                 <td>
                                     <input type="hidden" name="items[${idx}][ingredient_id]" value="${item.ingredient_id}">
                                     ${item.ingredient_name}
                                 </td>
                                 <td>
                                     <input type="number" min="0.01" step="any" class="form-control form-control-sm po-item-qty" name="items[${idx}][quantity]" value="${item.quantity}" required>
                                 </td>
                                 <td>
                                     <input type="number" min="0" step="any" class="form-control form-control-sm po-item-price" name="items[${idx}][price]" value="${defaultPrice}" required>
                                 </td>
                                 <td class="text-end po-item-subtotal">${formatCurrency(subtotal)}</td>
                              </tr>`;
                                });

                                html += '</tbody></table>';
                                poItemsContainer.innerHTML = html;
                                calculateTotalPO();
                            } else {
                                poItemsContainer.innerHTML =
                                    '<div class="alert alert-warning">Store Request ini tidak memiliki item atau gagal dimuat. Silakan tambah item manual.</div>';
                                showManualBlock(true);
                                calculateTotalPO();
                            }
                        })
                        .catch(err => {
                            console.error("Error fetching SR items:", err);
                            poItemsContainer.innerHTML =
                                '<div class="alert alert-danger">Gagal memuat item Store Request. Silakan tambah item manual.</div>';
                            showManualBlock(true);
                            calculateTotalPO();
                        });
                });
            }

            // Add manual item button
            if (poBtnAddManualItem) {
                poBtnAddManualItem.addEventListener('click', function() {
                    addManualItemRowPO();
                });
            }

            // Manual items table events
            if (poManualItemsTableBody) {
                poManualItemsTableBody.addEventListener('click', function(e) {
                    if (e.target.closest('.btnRemoveManualItem')) {
                        e.target.closest('tr')?.remove();
                        calculateTotalPO();
                    }
                });

                poManualItemsTableBody.addEventListener('change', function(e) {
                    if (e.target.classList.contains('ingredient-select')) {
                        updateManualItemUnit(e);
                    }
                });
            }

            // Input changes for calculation
            if (modalTambahPO) {
                modalTambahPO.addEventListener('input', function(e) {
                    if (
                        e.target.classList.contains('po-item-qty') ||
                        e.target.classList.contains('po-item-price') ||
                        e.target.classList.contains('item-qty-manual') ||
                        e.target.classList.contains('item-price-manual')
                    ) {
                        calculateTotalPO();
                    }
                });
            }

            // Submit form PO
            if (formTambahPO) {
                formTambahPO.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (poFormAlert) {
                        poFormAlert.style.display = 'none';
                        poFormAlert.innerHTML = '';
                    }

                    // Validasi credit limit
                    if (poCurrentSupplierData && poCurrentSupplierData.type === 'tempo') {
                        const tempoRadio = document.getElementById('po_pay_tempo');
                        if (tempoRadio && tempoRadio.checked && poCurrentTotal > poCurrentSupplierData
                            .credit_limit) {
                            Swal.fire(
                                'Limit Kredit Tidak Cukup',
                                `Total PO (${formatCurrency(poCurrentTotal)}) melebihi sisa limit kredit supplier (${formatCurrency(poCurrentSupplierData.credit_limit)}).`,
                                'error'
                            );
                            return;
                        }
                    }

                    // Validasi item
                    const hasItems = document.querySelectorAll('input[name$="[ingredient_id]"]').length > 0;
                    if (!hasItems) {
                        Swal.fire('Item Kosong', 'Harap tambahkan minimal satu item barang ke dalam PO.',
                            'warning');
                        return;
                    }

                    showLoading('Menyimpan data purchase order...');

                    if (poBtnSubmit) {
                        poBtnSubmit.disabled = true;
                        poBtnSubmit.innerHTML = '<i class="bi bi-arrow-repeat"></i> Menyimpan...';
                    }

                    const formData = new FormData(formTambahPO);
                    const submitUrl = formTambahPO.dataset.url || formTambahPO.getAttribute('action');

                    fetch(submitUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(async response => {
                            hideLoading();

                            if (poBtnSubmit) {
                                poBtnSubmit.disabled = false;
                                poBtnSubmit.innerHTML = '<i class="bi bi-save"></i> Simpan PO';
                            }

                            let data;
                            try {
                                data = await response.json();
                            } catch (err) {
                                data = {
                                    status: 'error',
                                    message: 'Respon server tidak valid.'
                                };
                            }

                            if (response.ok && data.status === 'success') {
                                const modalInstance = bootstrap.Modal.getInstance(modalTambahPO);
                                if (modalInstance) modalInstance.hide();

                                Swal.fire('Berhasil', data.message, 'success').then(() => location
                                    .reload());
                            } else {
                                let errorMsg = data.message || 'Terjadi kesalahan.';

                                if (data.errors) {
                                    errorMsg = '<ul>' + Object.keys(data.errors).map(key => {
                                        return `<li>${data.errors[key].join(', ')}</li>`;
                                    }).join('') + '</ul>';
                                }

                                if (poFormAlert) {
                                    poFormAlert.innerHTML = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                   <strong>Gagal!</strong><br>${errorMsg}
                                                   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                               </div>`;
                                    poFormAlert.style.display = 'block';

                                    const modalBody = modalTambahPO.querySelector('.modal-body');
                                    if (modalBody) modalBody.scrollTop = 0;
                                }
                            }
                        })
                        .catch(error => {
                            hideLoading();

                            if (poBtnSubmit) {
                                poBtnSubmit.disabled = false;
                                poBtnSubmit.innerHTML = '<i class="bi bi-save"></i> Simpan PO';
                            }

                            if (poFormAlert) {
                                poFormAlert.innerHTML =
                                    `<div class="alert alert-danger">Error jaringan: ${error.message}</div>`;
                                poFormAlert.style.display = 'block';

                                const modalBody = modalTambahPO.querySelector('.modal-body');
                                if (modalBody) modalBody.scrollTop = 0;
                            }
                        });
                });
            }

            // Reset modal PO saat dibuka
            if (modalTambahPO) {
                modalTambahPO.addEventListener('show.bs.modal', function() {
                    if (formTambahPO) formTambahPO.reset();
                    if (poFormAlert) poFormAlert.style.display = 'none';

                    if (poItemsContainer) {
                        poItemsContainer.innerHTML =
                            '<div class="text-muted">Pilih Store Request atau tambahkan item manual.</div>';
                    }

                    resetManualItems();
                    showManualBlock(true);

                    if (poManualItemsTableBody && poManualItemsTableBody.children.length === 0) {
                        addManualItemRowPO();
                    }

                    updateSupplierUI(null);
                    calculateTotalPO();

                    if (poBtnSubmit) {
                        poBtnSubmit.disabled = false;
                        poBtnSubmit.innerHTML = '<i class="bi bi-save"></i> Simpan PO';
                    }
                });
            }

            // --- Search functionality ---
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#tabel-po tbody tr.data-row');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }

            // --- Delete PO functionality ---
            const tabelPO = document.getElementById('tabel-po');
            if (tabelPO) {
                tabelPO.addEventListener('click', function(e) {
                    if (e.target.closest('.btnHapusPO')) {
                        const btn = e.target.closest('.btnHapusPO');
                        const id = btn.getAttribute('data-id');
                        const baseUrl = tabelPO.getAttribute('data-url');
                        const url = baseUrl.replace(/0$/, id);

                        withAuth(() => {
                            Swal.fire({
                                title: 'Yakin ingin menghapus?',
                                text: "Data Purchase Order ini akan dihapus permanen!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonText: 'Batal',
                                confirmButtonText: 'Ya, hapus!'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    showLoading('Menghapus data purchase order...');

                                    fetch(url, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-Requested-With': 'XMLHttpRequest',
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(async response => {
                                            hideLoading();

                                            let data;
                                            try {
                                                data = await response.json();
                                            } catch (err) {
                                                data = {
                                                    status: 'error',
                                                    message: 'Gagal parsing response server.'
                                                };
                                            }

                                            if (response.ok && data.status !== 'error') {
                                                Swal.fire({
                                                    title: 'Terhapus!',
                                                    text: data.message,
                                                    icon: 'success'
                                                }).then(() => location.reload());
                                            } else {
                                                Swal.fire({
                                                    title: 'Gagal',
                                                    text: data.message ||
                                                        'Terjadi kesalahan saat menghapus data.',
                                                    icon: 'error'
                                                });
                                            }
                                        })
                                        .catch(error => {
                                            hideLoading();
                                            Swal.fire({
                                                title: 'Gagal',
                                                text: 'Terjadi kesalahan saat menghapus data.',
                                                icon: 'error'
                                            });
                                        });
                                }
                            });
                        });
                    }
                });
            }

            // --- Detail PO functionality ---
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnModalDetailPO')) {
                    const btn = e.target.closest('.btnModalDetailPO');
                    const id = btn.getAttribute('data-id');
                    const poDetailContent = document.getElementById('poDetailContent');
                    const poDetailLoading = document.getElementById('poDetailLoading');

                    // Reset
                    if (poDetailContent) {
                        poDetailContent.innerHTML = '';
                        poDetailContent.style.display = 'none';
                    }
                    if (poDetailLoading) poDetailLoading.style.display = '';

                    // Hide print button initially
                    if (btnPrintPO) {
                        btnPrintPO.style.display = "none";
                        btnPrintPO.setAttribute('href', '#');
                    }

                    // Show modal
                    const modalDetailPO = document.getElementById('modalDetailPO');
                    if (modalDetailPO) {
                        const bsModal = bootstrap.Modal.getOrCreateInstance(modalDetailPO);
                        bsModal.show();
                    }

                    // Fetch PO details
                    fetch("{{ url('prc/purchase-orders') }}/" + id, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (poDetailLoading) poDetailLoading.style.display = 'none';

                            if (data.status === 'success' && data.po) {
                                const po = data.po;
                                let html = `
                        <div class="mb-3">
                            <dl class="row">
                                <dt class="col-sm-4">No. PO</dt>
                                <dd class="col-sm-8">${po.po_number || '-'}</dd>
                                <dt class="col-sm-4">Tanggal PO</dt>
                                <dd class="col-sm-8">${po.order_date ? (typeof moment !== 'undefined' ? moment(po.order_date).format('DD MMM YYYY') : po.order_date) : '-'}</dd>
                                <dt class="col-sm-4">Supplier</dt>
                                <dd class="col-sm-8">${po.supplier?.name || '-'}</dd>
                                <dt class="col-sm-4">Store Request</dt>
                                <dd class="col-sm-8">${po.store_request?.request_number || '-'}</dd>
                                <dt class="col-sm-4">Metode Pembayaran</dt>
                                <dd class="col-sm-8"><span class="badge bg-info">${po.payment_type || '-'}</span></dd>
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8"><span class="badge bg-success">${po.status || 'Pending'}</span></dd>
                                <dt class="col-sm-4">Nilai Total PO</dt>
                                <dd class="col-sm-8"><strong>Rp ${parseInt(po.total_amount || 0).toLocaleString('id-ID')}</strong></dd>
                                <dt class="col-sm-4">Catatan</dt>
                                <dd class="col-sm-8">${po.notes ? po.notes.replace(/\n/g,'<br>') : '-'}</dd>
                            </dl>
                        </div>
                        <div class="mb-2">
                            <strong>Detail Item:</strong>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Bahan</th>
                                            <th>Qty</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;

                                let grandTotal = 0;
                                if (Array.isArray(po.items) && po.items.length > 0) {
                                    po.items.forEach((item, idx) => {
                                        let price = parseFloat(item.price) || 0;
                                        let quantity = parseFloat(item.quantity) || 0;
                                        let subtotal = quantity * price;
                                        grandTotal += subtotal;

                                        html += `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td>${item.ingredient_name || item.ingredient?.name || '-'}</td>
                                    <td>${quantity}</td>
                                    <td>Rp ${price.toLocaleString('id-ID')}</td>
                                    <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                                </tr>
                            `;
                                    });
                                } else {
                                    html +=
                                        `<tr><td colspan="5" class="text-center text-muted">Tidak ada item PO</td></tr>`;
                                }

                                html += `
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-end">Total</th>
                                            <th>Rp ${parseInt(grandTotal).toLocaleString('id-ID')}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `;

                                if (poDetailContent) {
                                    poDetailContent.innerHTML = html;
                                    poDetailContent.style.display = '';
                                }

                                // Show print button
                                if (btnPrintPO && po.id) {
                                    const printUrl = '{{ route('prc.purchase_orders.print', 0) }}'
                                        .replace(/0$/, po.id);
                                    btnPrintPO.setAttribute('href', printUrl);
                                    btnPrintPO.style.display = '';
                                }
                            } else {
                                if (poDetailContent) {
                                    poDetailContent.innerHTML =
                                        '<div class="text-danger">Gagal memuat detail PO. Silakan coba lagi.</div>';
                                    poDetailContent.style.display = '';
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching PO details:', error);

                            if (poDetailLoading) poDetailLoading.style.display = 'none';
                            if (poDetailContent) {
                                poDetailContent.innerHTML =
                                    '<div class="text-danger">Gagal memuat detail PO. Silakan coba lagi.</div>';
                                poDetailContent.style.display = '';
                            }
                        });
                }
            });

            // --- Edit PO functionality ---
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnEditPO')) {
                    const btn = e.target.closest('.btnEditPO');
                    withAuth(() => {
                        btn.click();
                    });
                }
            });

        });
    </script>
@endpush
