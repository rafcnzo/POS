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
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formTambahPenerimaan" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTambahPenerimaanLabel"><i class="bi bi-plus-circle"></i> Tambah
                            Penerimaan Barang</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Penerimaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailPenerimaan" tabindex="-1" aria-labelledby="modalDetailPenerimaanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailPenerimaanLabel">
                        <i class="bi bi-eye"></i> Detail Penerimaan Barang
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="penerimaanDetailLoading" class="text-center py-4 d-none">
                        <div class="spinner-border" role="status"></div>
                        <div>Memuat detail penerimaan...</div>
                    </div>
                    <div id="penerimaanDetailContent" class="d-none"></div>
                </div>
                <div class="modal-footer justify-content-end">
                    {{-- <a id="btnPrintPenerimaan" href="#" class="btn btn-outline-secondary d-none" target="_blank">
                        <i class="bi bi-printer"></i> Print Penerimaan
                    </a> --}}
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
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

            // Tombol Hapus Penerimaan
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

            // Modal Tambah Penerimaan: Dynamic Items
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
                                '<thead><tr><th>Nama Barang</th><th>Qty Dipesan</th><th>Qty Diterima</th><th>Harga Satuan</th></tr></thead><tbody>';
                            data.items.forEach((item, idx) => {
                                html += `<tr>
                                <td>
                                    <input type="hidden" name="items[${idx}][ingredient_id]" value="${item.ingredient_id}">
                                    ${item.ingredient_name}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="number" min="0.01" step="0.01" class="form-control" value="${item.quantity_ordered}" readonly disabled style="width: 90px;">
                                        <span>${item.unit ? item.unit : ''}</span>
                                    </div>
                                    <input type="hidden" name="items[${idx}][quantity_ordered]" value="${item.quantity_ordered}">
                                </td>
                                <td>
                                    <input type="number" min="0.01" step="0.01" class="form-control" name="items[${idx}][quantity_received]" required>
                                </td>
                                <td>
                                    <input type="number" min="0" step="1" class="form-control" value="${item.cost_price}" readonly disabled>
                                    <input type="hidden" name="items[${idx}][cost_price]" value="${item.cost_price}">
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

            // Submit Tambah Penerimaan
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

                                console.log("===== MEMERIKSA RESPONSE DARI SERVER =====");
                                console.log("Objek response mentah:", response);
                                console.log("Apakah response.ok?", response
                                .ok); // <--- INI PENTING (Harusnya true)

                                const data = await response.json();
                                console.log("Data JSON yang sudah di-parse:", data);
                                console.log("Apakah data.status === 'success'?", data
                                    .status === 'success'); // <--- INI KUNCINYA

                                if (response.ok && data.status === 'success') {
                                    console.log(
                                        "Kesimpulan: KONDISI SUKSES TERPENUHI. Menampilkan notif berhasil."
                                        );
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
                                    console.error(
                                        "Kesimpulan: KONDISI SUKSES GAGAL. Menampilkan notif gagal."
                                        );
                                    Swal.fire({
                                        title: 'Gagal',
                                        text: data.message || 'Terjadi kesalahan.',
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(error => {
                                hideLoading();
                                console.error("===== TERJADI ERROR DI BLOK .CATCH() =====");
                                console.error("Objek error:", error);
                                Swal.fire({
                                    title: 'Error Fatal',
                                    text: 'Tidak bisa memproses request. Cek console.',
                                    icon: 'error'
                                });
                            });
                    }
                });
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.btnModalDetailPenerimaan')) {
                    const btn = e.target.closest('.btnModalDetailPenerimaan');
                    const id = btn.getAttribute('data-id');
                    const penerimaanDetailContent = document.getElementById('penerimaanDetailContent');
                    const penerimaanDetailLoading = document.getElementById('penerimaanDetailLoading');
                    // const btnPrintPenerimaan = document.getElementById('btnPrintPenerimaan');

                    // Reset
                    penerimaanDetailContent.innerHTML = '';
                    penerimaanDetailContent.classList.add('d-none');
                    penerimaanDetailLoading.classList.remove('d-none');

                    // // Hide "Print Penerimaan" button initially
                    // if (btnPrintPenerimaan) {
                    //     btnPrintPenerimaan.classList.add('d-none');
                    //     btnPrintPenerimaan.setAttribute('href', '#');
                    // }

                    // Hapus preview dokumen gambar jika ada sebelumnya
                    let oldPreview = document.getElementById('proofDocumentPreview');
                    if (oldPreview) {
                        oldPreview.parentNode.removeChild(oldPreview);
                    }

                    const modalDetailPenerimaan = document.getElementById('modalDetailPenerimaan');
                    if (modalDetailPenerimaan) {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const bsModal = bootstrap.Modal.getOrCreateInstance(modalDetailPenerimaan);
                            bsModal.show();
                        } else {
                            modalDetailPenerimaan.style.display = 'block'; // Fallback minimal
                        }
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
                                // proofPreviewHtml will be constructed if ada dokumen
                                let proofPreviewHtml = '';

                                // Logic for displaying proof document (as img or fallback) like in header photo usage
                                if (p.proof_document) {
                                    let proofUrl = '';
                                    if (/^(https?:)?\/\//.test(p.proof_document) || p.proof_document
                                        .startsWith('/')) {
                                        proofUrl = p.proof_document;
                                    } else {
                                        proofUrl = "{{ asset('storage') }}/" + p.proof_document;
                                    }
                                    proofPreviewHtml = `
                                    <span>
                                        <a href="#" class="btn btn-link btn-sm px-0" id="lihatDokumenLink">
                                            <i class="bi bi-eye"></i> Lihat Dokumen
                                        </a>
                                    </span>
                                    <div id="proofDocumentPreview" style="display:none;margin-top:1rem;">
                                        <img src="${proofUrl}" alt="Bukti Dokumen" style="max-width:100%;border:1px solid #ddd;padding:4px;">
                                    </div>
                                `;
                                } else {
                                    // Fallback like in header: if null, show default image (for proof: maybe just "-")
                                    proofPreviewHtml = '-';
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
                                        <dd class="col-sm-8">
                                            ${proofPreviewHtml}
                                        </dd>
                                    </dl>
                                </div>
                            `;
                                penerimaanDetailContent.innerHTML = html;

                                // listener utk lihat dokumen
                                if (p.proof_document) {
                                    const lihatLink = document.getElementById('lihatDokumenLink');
                                    const previewDiv = document.getElementById('proofDocumentPreview');
                                    if (lihatLink && previewDiv) {
                                        lihatLink.addEventListener('click', function(ev) {
                                            ev.preventDefault();
                                            if (previewDiv.style.display === 'none' ||
                                                previewDiv.style.display === '') {
                                                previewDiv.style.display = 'block';
                                                lihatLink.innerHTML =
                                                    `<i class="bi bi-eye-slash"></i> Tutup Dokumen`;
                                            } else {
                                                previewDiv.style.display = 'none';
                                                lihatLink.innerHTML =
                                                    `<i class="bi bi-eye"></i> Lihat Dokumen`;
                                            }
                                        });
                                    }
                                }

                                // // Set the href of Print Penerimaan button and show it
                                // if (btnPrintPenerimaan && p.id) {
                                //     btnPrintPenerimaan.setAttribute('href', '#');
                                //     // btnPrintPenerimaan.setAttribute('href', '{{ url('prc/penerimaanbarang/print/0') }}'.replace(/0$/, p.id)); // update print href jika ada route
                                //     btnPrintPenerimaan.classList.remove('d-none');
                                // }
                            } else {
                                penerimaanDetailContent.innerHTML =
                                    `<div class="text-danger">Gagal memuat detail penerimaan. Silakan coba lagi.</div>`;
                                // Hide the "Print Penerimaan" button again if error
                                if (btnPrintPenerimaan) {
                                    btnPrintPenerimaan.classList.add('d-none');
                                    btnPrintPenerimaan.setAttribute('href', '#');
                                }
                            }
                            penerimaanDetailContent.classList.remove('d-none');
                        })
                        .catch(() => {
                            penerimaanDetailLoading.classList.add('d-none');
                            penerimaanDetailContent.innerHTML =
                                `<div class="text-danger">Gagal memuat detail penerimaan. Silakan coba lagi.</div>`;
                            penerimaanDetailContent.classList.remove('d-none');
                            // Hide the "Print Penerimaan" button on fetch error
                            if (btnPrintPenerimaan) {
                                btnPrintPenerimaan.classList.add('d-none');
                                btnPrintPenerimaan.setAttribute('href', '#');
                            }
                        });
                }
            });
        });
    </script>
@endpush
