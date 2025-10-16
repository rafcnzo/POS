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
                            {{ number_format($purchaseOrders->sum(function ($po) {return $po->items->sum(fn($i) => $i->quantity * $i->cost_price);}),0,',','.') }}
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
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formTambahPO" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahPOLabel"><i class="bi bi-plus-circle"></i> Tambah Purchase
                            Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div id="formPOAlert" style="display:none"></div>
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label">Pilih Supplier <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="supplier_id" name="supplier_id" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan PO</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail PO (Show) -->
    <div class="modal fade" id="modalDetailPO" tabindex="-1" aria-labelledby="modalDetailPOLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailPOLabel">
                        <i class="bi bi-eye"></i> Detail Purchase Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="poDetailLoading" class="text-center py-4" style="display: none;">
                        <div class="spinner-border" role="status"></div>
                        <div>Memuat detail PO...</div>
                    </div>
                    <div id="poDetailContent" style="display: none;"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <a id="btnPrintPO" href="#" class="btn btn-outline-secondary" target="_blank" style="display: none;">
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

            let manualItemIdx = 0;
            const ingredientOptions = @json(\App\Models\Ingredient::orderBy('name')->select('id', 'name')->get()->map(fn($i) => ['id' => $i->id, 'name' => $i->name]));
            const srSelect = document.getElementById('store_request_id');
            const poItemsContainer = document.getElementById('poItemsContainer');
            const manualBlock = document.getElementById('addManualItemsBlock');
            const manualItemsTableBody = document.querySelector('#manualItemsTable tbody');
            const addManualItemBtn = document.getElementById('btnAddManualItem');
            const btnPrintPO = document.getElementById('btnPrintPO'); // get the print button

            function resetManualItems() {
                manualItemsTableBody.innerHTML = '';
                manualItemIdx = 0;
            }

            function addManualItemRow(data = {}) {
                let idx = manualItemIdx++;
                let row = document.createElement('tr');
                row.innerHTML = `
            <td>
                <select class="form-select" name="items[${idx}][ingredient_id]" required>
                    <option value="">-- Pilih --</option>
                    ${ingredientOptions.map(opt =>
                        `<option value="${opt.id}"${data.ingredient_id==opt.id?' selected':''}>${opt.name}</option>`
                    ).join('')}
                </select>
            </td>
            <td>
                <input type="number" min="0.01" step="0.01" class="form-control" name="items[${idx}][quantity]" value="${data.quantity||''}" required>
            </td>
            <td>
                <input type="number" min="0" step="1" class="form-control" name="items[${idx}][price]" value="${data.price||''}" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm btnRemoveItem"><i class="bi bi-trash"></i></button>
            </td>
            `;
                manualItemsTableBody.appendChild(row);
            }

            addManualItemBtn.addEventListener('click', function() {
                addManualItemRow();
            });

            manualItemsTableBody.addEventListener('click', function(e) {
                if (e.target.closest('.btnRemoveItem')) {
                    e.target.closest('tr').remove();
                }
            });

            function showManualBlock(active) {
                manualBlock.style.display = active ? '' : 'none';
                manualBlock.parentElement.style.marginBottom = '0';
                if (active) {
                    if (manualItemsTableBody.children.length === 0) {
                        addManualItemRow();
                    }
                } else {
                    resetManualItems();
                }
            }

            srSelect && srSelect.addEventListener('change', function() {
                const srId = this.value;
                if (!srId) {
                    poItemsContainer.innerHTML =
                        '<div class="text-muted">Tambah item manual jika tanpa Store Request.</div>';
                    showManualBlock(true);
                    return;
                }
                showManualBlock(false);
                poItemsContainer.innerHTML = '<div class="text-muted">Memuat item...</div>';
                const fetchUrl = "{{ url('prc/purchase-orders/sr-items') }}/" + srId;
                fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success' && Array.isArray(data.items) && data.items
                            .length > 0) {
                            let html =
                                '<table class="table table-sm table-bordered"><thead><tr><th>Nama Barang</th><th>Qty</th><th>Harga Satuan</th></tr></thead><tbody>';
                            data.items.forEach((item, idx) => {
                                html += `<tr>
                        <td>
                            <input type="hidden" name="items[${idx}][ingredient_id]" value="${item.ingredient_id}">
                            ${item.ingredient_name}
                        </td>
                        <td>
                            <input type="number" min="0.01" step="0.01" class="form-control" name="items[${idx}][quantity]" value="${item.quantity}" required>
                        </td>
                        <td>
                            <input type="number" min="0" step="1" class="form-control" name="items[${idx}][price]" required>
                        </td>
                    </tr>`;
                            });
                            html += '</tbody></table>';
                            poItemsContainer.innerHTML = html;
                        } else if (data.status === 'success' && Array.isArray(data.items) && data.items
                            .length === 0) {
                            poItemsContainer.innerHTML =
                                '<div class="text-danger">Store Request ini tidak memiliki item.</div>';
                        } else {
                            poItemsContainer.innerHTML =
                                '<div class="text-danger">Gagal memuat item Store Request.</div>';
                        }
                    })
                    .catch(() => {
                        poItemsContainer.innerHTML =
                            '<div class="text-danger">Gagal memuat item Store Request.</div>';
                    });
            });

            var modalTambahPO = document.getElementById('modalTambahPO');
            if (modalTambahPO) {
                modalTambahPO.addEventListener('show.bs.modal', function() {
                    document.getElementById('formTambahPO').reset();
                    document.getElementById('formPOAlert').style.display = 'none';
                    poItemsContainer.innerHTML =
                        '<div class="text-muted">Pilih Store Request terlebih dahulu untuk menampilkan item, atau tambahkan manual jika tanpa Store Request.</div>';
                    showManualBlock(true);
                });
            }
            document.getElementById('formTambahPO').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                showLoading('Menyimpan data purchase order...');
                fetch("{{ route('prc.purchase_orders.submit') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
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
                        if (response.ok && data.status === 'success') {
                            var modalTambahPO = document.getElementById('modalTambahPO');
                            if (modalTambahPO) {
                                var bsModal = bootstrap.Modal.getInstance(modalTambahPO) ||
                                    new bootstrap.Modal(modalTambahPO);
                                bsModal.hide();
                            }
                            Swal.fire({
                                title: 'Berhasil',
                                text: data.message,
                                icon: 'success',
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                title: 'Gagal',
                                text: data.message ||
                                    'Terjadi kesalahan saat menyimpan data.',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(() => {
                        hideLoading();
                        Swal.fire({
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menyimpan data.',
                            icon: 'error'
                        });
                    });
            });

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

            document.getElementById('tabel-po').addEventListener('click', function(e) {
                if (e.target.closest('.btnHapusPO')) {
                    let btn = e.target.closest('.btnHapusPO');
                    let id = btn.getAttribute('data-id');
                    let url = document.getElementById('tabel-po').getAttribute('data-url').replace(/0$/,
                        id);

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
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnModalDetailPO')) {
                    const btn = e.target.closest('.btnModalDetailPO');
                    const id = btn.getAttribute('data-id');
                    const poDetailContent = document.getElementById('poDetailContent');
                    const poDetailLoading = document.getElementById('poDetailLoading');
                    const btnPrintPO = document.getElementById('btnPrintPO');


                    // Reset
                    poDetailContent.innerHTML = '';
                    poDetailContent.style.display = 'none';
                    poDetailLoading.style.display = '';

                    // Hide "Print PO" button initially
                    if (btnPrintPO) {
                        btnPrintPO.style.display = "none";
                        btnPrintPO.setAttribute('href', '#');
                    }

                    const modalDetailPO = document.getElementById('modalDetailPO');
                    if (modalDetailPO) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const bsModal = bootstrap.Modal.getOrCreateInstance(modalDetailPO);
                            bsModal.show();
                        } else {
                            modalDetailPO.style.display = 'block'; // Fallback minimal
                        }
                    }
                    fetch("{{ url('prc/purchase-orders') }}/" + id, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            poDetailLoading.style.display = 'none';
                            if (data.status === 'success' && data.po) {
                                const po = data.po;
                                let html = `
                        <div class="mb-3">
                            <dl class="row">
                                <dt class="col-sm-4">No. PO</dt>
                                <dd class="col-sm-8">${po.po_number || '-'}</dd>
                                <dt class="col-sm-4">Tanggal PO</dt>
                                <dd class="col-sm-8">${po.order_date ? moment(po.order_date).format('DD MMM YYYY') : '-'}</dd>
                                <dt class="col-sm-4">Supplier</dt>
                                <dd class="col-sm-8">${po.supplier?.name || '-'}</dd>
                                <dt class="col-sm-4">Store Request</dt>
                                <dd class="col-sm-8">${po.store_request?.request_number || '-'}</dd>
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8"><span class="badge bg-info text-dark">${po.status}</span></dd>
                                <dt class="col-sm-4">Nilai Total PO</dt>
                                <dd class="col-sm-8">Rp ${parseInt(po.total_amount).toLocaleString('id')}</dd>
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
                                    <td>${item.ingredient_name || '-'}</td>
                                    <td>${item.quantity}</td>
                                    <td>Rp ${price.toLocaleString('id')}</td>
                                    <td>Rp ${subtotal.toLocaleString('id')}</td>
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
                                            <th>Rp ${parseInt(grandTotal).toLocaleString('id')}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `;
                                poDetailContent.innerHTML = html;
                                
                                // Set the href of Print PO button and show it
                                if (btnPrintPO && po.id) {
                                    btnPrintPO.setAttribute('href', '{{ route('prc.purchase_orders.print', 0) }}'.replace(/0$/, po.id));
                                    btnPrintPO.style.display = '';
                                }
                            } else {
                                poDetailContent.innerHTML =
                                    `<div class="text-danger">Gagal memuat detail PO. Silakan coba lagi.</div>`;
                                // Hide the "Print PO" button again if error
                                if (btnPrintPO) {
                                    btnPrintPO.style.display = "none";
                                    btnPrintPO.setAttribute('href', '#');
                                }
                            }
                            poDetailContent.style.display = '';
                        })
                        .catch(() => {
                            poDetailLoading.style.display = 'none';
                            poDetailContent.innerHTML =
                                `<div class="text-danger">Gagal memuat detail PO. Silakan coba lagi.</div>`;
                            poDetailContent.style.display = '';
                            // Hide the "Print PO" button on fetch error
                            if (btnPrintPO) {
                                btnPrintPO.style.display = "none";
                                btnPrintPO.setAttribute('href', '#');
                            }
                        });
                }
            });

        });
    </script>
@endpush
