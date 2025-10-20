@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-box-arrow-in-down"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Penerimaan Barang</h1>
                            <p class="page-subtitle">Kelola data penerimaan barang dari pembelian</p>
                        </div>
                    </div>
                    <button type="button" class="btn-add-primary" id="btnTambahPenerimaan" data-bs-toggle="modal"
                        data-bs-target="#modalTambahPenerimaan">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Penerimaan</span>
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
                        <h3 class="stat-value">{{ $receipts->count() }}</h3>
                        <p class="stat-label">Total Penerimaan</p>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Penerimaan Barang</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari penerimaan..." id="searchInput">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-penerimaanbarang"
                            data-url="{{ route('prc.penerimaanbarang.destroy', ['penerimaanbarang' => 0]) }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Tanggal</th>
                                    <th class="col-secondary">No. PO</th>
                                    <th class="col-secondary">Diterima Oleh</th>
                                    <th class="col-secondary">Catatan</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receipts as $key => $receipt)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span
                                                    class="item-name">{{ \Carbon\Carbon::parse($receipt->receipt_date)->format('d M Y') }}</span>
                                            </div>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $receipt->purchaseOrder->po_number ?? '-' }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span>{{ $receipt->user->name ?? '-' }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span>{{ $receipt->notes ?? '-' }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button type="button"
                                                    class="btn-action btn-detail btnModalDetailPenerimaan"
                                                    data-id="{{ $receipt->id }}" data-bs-toggle="tooltip" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusPenerimaan"
                                                    data-id="{{ $receipt->id }}" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-box-arrow-in-down"></i>
                                                <h4>Belum ada data penerimaan barang</h4>
                                                <p>Klik tombol "Tambah Penerimaan" untuk memulai</p>
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

    <!-- Modal Tambah Penerimaan -->
    <div class="modal fade" id="modalTambahPenerimaan" tabindex="-1" aria-labelledby="modalTambahPenerimaanLabel"
        aria-hidden="true" style="--bs-modal-width: 800px;">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 800px; margin: 0 auto;">
            <div class="modal-content" style="max-height:90vh; overflow:hidden; display:flex; flex-direction:column;">
                <form id="formTambahPenerimaan" enctype="multipart/form-data" style="height:100%;display:flex;flex-direction:column;">
                    <div class="modal-header" style="flex-shrink: 0;">
                        <h5 class="modal-title" id="modalTambahPenerimaanLabel"><i class="bi bi-plus-circle"></i> Tambah
                            Penerimaan Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body" style="overflow-y:auto; max-height:60vh; flex-grow:1;">
                        <div id="formPenerimaanAlert" style="display:none"></div>
                        <div class="mb-3">
                            <label for="purchase_order_id" class="form-label">Pilih No. PO</label>
                            <select class="form-select" id="purchase_order_id" name="purchase_order_id" required>
                                <option value="">-- Pilih PO --</option>
                                @foreach ($purchaseOrders as $po)
                                    <option value="{{ $po->id }}">{{ $po->po_number }} -
                                        {{ $po->supplier->name ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="receipt_date" class="form-label">Tanggal Penerimaan</label>
                            <input type="date" class="form-control" id="receipt_date" name="receipt_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="proof_document" class="form-label">Bukti Penerimaan</label>
                            <input type="file" class="form-control" id="proof_document" name="proof_document"
                                accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Detail Barang Diterima</label>
                            <div id="itemsContainer">
                                <div class="text-muted">Pilih PO terlebih dahulu untuk menampilkan item.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="flex-shrink:0;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Penerimaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Penerimaan -->
    <div class="modal fade" id="modalDetailPenerimaan" tabindex="-1" aria-labelledby="modalDetailPenerimaanLabel"
        aria-hidden="true" style="--bs-modal-width: 800px;">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 800px; margin: 0 auto;">
            <div class="modal-content" style="max-height:90vh; display:flex; flex-direction:column;">
                <div class="modal-header" style="flex-shrink: 0;">
                    <h5 class="modal-title" id="modalDetailPenerimaanLabel">
                        <i class="bi bi-eye"></i> Detail Penerimaan Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto; flex-grow:1;">
                    <div id="penerimaanDetailLoading" class="text-center py-4 d-none">
                        <div class="spinner-border" role="status"></div>
                        <div>Memuat detail penerimaan...</div>
                    </div>
                    <div id="penerimaanDetailContent" class="d-none"></div>
                </div>
                <div class="modal-footer justify-content-end" style="flex-shrink:0;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ... (Kode untuk search dan hapus tetap sama) ...
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#tabel-penerimaanbarang tbody tr.data-row');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }

            document.getElementById('tabel-penerimaanbarang').addEventListener('click', function(e) {
                if (e.target.closest('.btnHapusPenerimaan')) {
                    let btn = e.target.closest('.btnHapusPenerimaan');
                    let id = btn.getAttribute('data-id');
                    let url = document.getElementById('tabel-penerimaanbarang').getAttribute('data-url')
                        .replace(
                            /0$/, id);

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Data penerimaan barang ini akan dihapus permanen dan stok akan dikurangi!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showLoading('Menghapus data penerimaan barang...');
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
                                        Swal.fire('Terhapus!', data.message, 'success')
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire('Gagal', data.message ||
                                            'Terjadi kesalahan saat menghapus data.',
                                            'error');
                                    }
                                })
                                .catch(error => {
                                    hideLoading();
                                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.',
                                        'error');
                                });
                        }
                    });
                }
            });

            // ... (Kode untuk modal tambah tetap sama) ...
            const poSelect = document.getElementById('purchase_order_id');
            const itemsContainer = document.getElementById('itemsContainer');
            poSelect && poSelect.addEventListener('change', function() {
                const poId = this.value;
                itemsContainer.innerHTML = '<div class="text-muted">Memuat item...</div>';
                if (!poId) {
                    itemsContainer.innerHTML =
                        '<div class="text-muted">Pilih PO terlebih dahulu untuk menampilkan item.</div>';
                    return;
                }
                const fetchUrl = "{{ url('prc/penerimaanbarang/po-items') }}/" + poId;
                fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success' && Array.isArray(data.items) && data.items
                            .length > 0) {
                            let html = '<table class="table table-sm table-bordered">';
                            html +=
                                '<thead><tr><th>Nama Barang</th><th>Qty Dipesan</th><th>Qty Diterima</th><th>Quantity Ditolak</th><th>Catatan</th><th>Harga Satuan</th></tr></thead><tbody>';
                            data.items.forEach((item, idx) => {
                                html += `<tr>
                                    <td>
                                        ${item.name || 'N/A'}
                                        <input type="hidden" name="items[${idx}][purchase_order_item_id]" value="${item.purchase_order_item_id}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="number" min="0.01" step="0.01" class="form-control" value="${item.quantity_ordered}" readonly disabled style="width: 90px;">
                                            <span>${item.unit ? item.unit : ''}</span>
                                        </div>
                                        <input type="hidden" name="items[${idx}][quantity_ordered]" value="${item.quantity_ordered}">
                                    </td>
                                    <td>
                                    <input type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control form-control-sm qty-received"
                                        name="items[${idx}][quantity_received]"
                                        data-idx="${idx}"
                                        data-max="${item.quantity_ordered || 0}"
                                        placeholder="0"
                                        required
                                        style="width: 100px;">
                                </td>
                                <td>
                                    <input type="number"
                                        min="0"
                                        step="0.01"
                                        class="form-control form-control-sm qty-rejected"
                                        name="items[${idx}][quantity_rejected]"
                                        data-idx="${idx}"
                                        value="0"
                                        placeholder="0"
                                        style="width: 100px;">
                                </td>
                                <td>
                                    <input type="text"
                                        class="form-control form-control-sm"
                                        name="items[${idx}][notes]"
                                        placeholder="Catatan (opsional)"
                                        style="min-width: 200px;">
                                </td>
                                <td class="text-end">
                                    <strong>${item.cost_price ? new Intl.NumberFormat('id-ID', {style: 'currency', currency: 'IDR'}).format(item.cost_price) : '-'}</strong>
                                </td>
                                </tr>`;
                            });
                            html += '</tbody></table>';
                            itemsContainer.innerHTML = html;
                        } else if (data.status === 'success' && Array.isArray(data.items) && data.items
                            .length === 0) {
                            itemsContainer.innerHTML =
                                '<div class="text-danger">PO ini tidak memiliki item.</div>';
                        } else {
                            itemsContainer.innerHTML =
                                '<div class="text-danger">Gagal memuat item PO.</div>';
                        }
                    })
                    .catch(() => {
                        itemsContainer.innerHTML =
                            '<div class="text-danger">Gagal memuat item PO.</div>';
                    });
            });

            document.getElementById('formTambahPenerimaan').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                const alertBox = document.getElementById('formPenerimaanAlert');
                alertBox.style.display = 'none';

                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Apakah Anda yakin ingin menyimpan penerimaan barang ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        showLoading('Menyimpan data penerimaan...');
                        fetch("{{ route('prc.penerimaanbarang.submit') }}", {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData
                            })
                            .then(async response => {
                                hideLoading();
                                const data = await response.json();
                                if (response.ok && data.status === 'success') {
                                    const modal = document.getElementById('modalTambahPenerimaan');
                                    if (modal) {
                                        const modalInstance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                                        modalInstance.hide();
                                    }
                                    Swal.fire({
                                        title: 'Berhasil',
                                        text: data.message,
                                        icon: 'success'
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire({
                                        title: 'Gagal',
                                        text: data.message || 'Terjadi kesalahan.',
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(error => {
                                hideLoading();
                                Swal.fire({
                                    title: 'Error Fatal',
                                    text: 'Tidak bisa memproses request. Cek console.',
                                    icon: 'error'
                                });
                            });
                    }
                });
            });

            // === BAGIAN YANG DIPERBARUI ===
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnModalDetailPenerimaan')) {
                    const btn = e.target.closest('.btnModalDetailPenerimaan');
                    const id = btn.getAttribute('data-id');
                    const penerimaanDetailContent = document.getElementById('penerimaanDetailContent');
                    const penerimaanDetailLoading = document.getElementById('penerimaanDetailLoading');

                    penerimaanDetailContent.innerHTML = '';
                    penerimaanDetailContent.classList.add('d-none');
                    penerimaanDetailLoading.classList.remove('d-none');

                    const modalDetailPenerimaan = document.getElementById('modalDetailPenerimaan');
                     if (modalDetailPenerimaan) {
                        const bsModal = bootstrap.Modal.getOrCreateInstance(modalDetailPenerimaan);
                        bsModal.show();
                     }

                    fetch("{{ url('prc/penerimaanbarang') }}/" + id, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            penerimaanDetailLoading.classList.add('d-none');
                            if (data.status === 'success' && data.penerimaan) {
                                const p = data.penerimaan;

                                let proofPreviewHtml = p.proof_document ? `
                                    <span>
                                        <a href="#" class="btn btn-link btn-sm px-0" id="lihatDokumenLink">
                                            <i class="bi bi-eye"></i> Lihat Dokumen
                                        </a>
                                    </span>
                                    <div id="proofDocumentPreview" style="display:none;margin-top:1rem;">
                                        <img src="{{ asset('storage') }}/${p.proof_document.replace('public/', '')}" alt="Bukti Dokumen" style="max-width:100%;border:1px solid #ddd;padding:4px;">
                                    </div>` : '-';

                                // PERBAIKAN: Mengambil data dari data.detail_items
                                let itemsHtml = '<p>Tidak ada item dalam penerimaan ini.</p>';
                                if (data.detail_items && data.detail_items.length > 0) {
                                    itemsHtml = `
                                        <h6 class="mt-4">Rincian Barang Diterima</h6>
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nama Barang</th>
                                                    <th class="text-end">Qty Diterima</th>
                                                    <th class="text-end">Qty Ditolak</th>
                                                    <th>Catatan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                    `;
                                    data.detail_items.forEach((item, index) => {
                                        // PERBAIKAN: Mengakses nama dari relasi yang benar
                                        const itemName = item.ingredient ? item.ingredient.name : 'Nama Barang Tidak Ditemukan';
                                        itemsHtml += `
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>${itemName}</td>
                                                <td class="text-end">${parseFloat(item.quantity_received) || 0}</td>
                                                <td class="text-end">${parseFloat(item.quantity_rejected) || 0}</td>
                                                <td>${item.notes || '-'}</td>
                                            </tr>
                                        `;
                                    });
                                    itemsHtml += '</tbody></table>';
                                }
                                let html = `
                                <div class="mb-3">
                                    <dl class="row">
                                        <dt class="col-sm-4">No. Penerimaan</dt>
                                        <dd class="col-sm-8">${p.receipt_number || '-'}</dd>
                                        <dt class="col-sm-4">Tanggal Penerimaan</dt>
                                        <dd class="col-sm-8">${p.receipt_date ? moment(p.receipt_date).format('DD MMM YYYY') : '-'}</dd>
                                        <dt class="col-sm-4">Purchase Order</dt>
                                        <dd class="col-sm-8">${p.purchase_order?.po_number || '-'}</dd>
                                        <dt class="col-sm-4">User Penerima</dt>
                                        <dd class="col-sm-8">${p.user?.name || '-'}</dd>
                                        <dt class="col-sm-4">Berkas Bukti</dt>
                                        <dd class="col-sm-8">${proofPreviewHtml}</dd>
                                    </dl>
                                </div>
                                ${itemsHtml}
                                `;

                                penerimaanDetailContent.innerHTML = html;

                                if (p.proof_document) {
                                    const lihatLink = document.getElementById('lihatDokumenLink');
                                    const previewDiv = document.getElementById('proofDocumentPreview');
                                    if (lihatLink && previewDiv) {
                                        lihatLink.addEventListener('click', function(ev) {
                                            ev.preventDefault();
                                            const isHidden = previewDiv.style.display === 'none' || previewDiv.style.display === '';
                                            previewDiv.style.display = isHidden ? 'block' : 'none';
                                            lihatLink.innerHTML = isHidden ? `<i class="bi bi-eye-slash"></i> Tutup Dokumen` : `<i class="bi bi-eye"></i> Lihat Dokumen`;
                                        });
                                    }
                                }

                            } else {
                                penerimaanDetailContent.innerHTML = `<div class="text-danger">Gagal memuat detail penerimaan. Silakan coba lagi.</div>`;
                            }
                            penerimaanDetailContent.classList.remove('d-none');
                        })
                        .catch(() => {
                            penerimaanDetailLoading.classList.add('d-none');
                            penerimaanDetailContent.innerHTML = `<div class="text-danger">Gagal memuat detail penerimaan. Silakan coba lagi.</div>`;
                            penerimaanDetailContent.classList.remove('d-none');
                        });
                }
            });

        });
    </script>
@endpush
