@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-box"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Manajemen FF&E</h1>
                            <p class="page-subtitle">Kelola data Furniture, Fixture & Equipment serta riwayatnya</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahFfne">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah FF&E</span>
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon"><i class="bi bi-layers"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $ffnes->count() }}</h3>
                        <p class="stat-label">Total FF&E</p>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-icon"><i class="bi bi-cash"></i></div>
                    <div class="stat-info">
                        <h3 class="stat-value">
                            Rp {{ number_format($ffnes->sum(function($f) { return $f->harga * $f->stock; }), 0, ',', '.') }}
                        </h3>
                        <p class="stat-label">Total Nilai FF&E</p>
                    </div>
                </div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar FF&E</span>
                    </div>
                    <select id="categoryFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="all">Semua Kategori</option>
                        <option value="Barang Habis Pakai">Barang Habis Pakai</option>
                        <option value="Barang Tidak Habis Pakai">Barang Tidak Habis Pakai</option>
                    </select>

                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" placeholder="Cari di tabel..." id="searchInput">
                    </div>
                </div>

                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-ffne">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Kondisi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ffnes as $key => $ffne)
                                    <tr class="data-row" data-kategori="{{ $ffne->kategori_ffne }}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $ffne->kode_ffne }}</td>
                                        <td>{{ $ffne->nama_ffne }}</td>
                                        <td>{{ $ffne->kategori_ffne }}</td>
                                        <td>Rp {{ number_format($ffne->harga, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($ffne->kategori_ffne === 'Barang Habis Pakai')
                                                <span class="fw-bold">{{ $ffne->stock }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $ffne->satuan_ffne }}</td>
                                        <td class="text-center">
                                            @if ($ffne->kondisi_ffne)
                                                <i class="bi bi-check-square-fill text-danger" title="Rusak"></i>
                                            @else
                                                <i class="bi bi-square text-success" title="Baik"></i>
                                            @endif
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                @if ($ffne->kategori_ffne !== 'Barang Habis Pakai')
                                                    <button class="btn-action btn-extra btnExtraFfne"
                                                        data-id="{{ $ffne->id }}" data-nama="{{ $ffne->nama_ffne }}"
                                                        title="Kelola Riwayat Extra">
                                                        <i class="bi bi-tools"></i>
                                                    </button>
                                                @endif

                                                <button class="btn-action btn-edit btnEditFfne"
                                                    data-id="{{ $ffne->id }}" data-kode_ffne="{{ $ffne->kode_ffne }}"
                                                    data-nama_ffne="{{ $ffne->nama_ffne }}"
                                                    data-kategori_ffne="{{ $ffne->kategori_ffne }}"
                                                    data-harga="{{ $ffne->harga }}"
                                                    data-satuan_ffne="{{ $ffne->satuan_ffne }}"
                                                    data-kondisi_ffne="{{ $ffne->kondisi_ffne }}" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </button>

                                                <button class="btn-action btn-delete btnHapusFfne"
                                                    data-id="{{ $ffne->id }}" title="Hapus"><i
                                                        class="bi bi-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-box"></i>
                                                <h4>Belum ada data FF&E</h4>
                                                <p>Klik tombol “Tambah FF&E” untuk menambahkan data baru</p>
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

    <div class="modal fade" id="modalFfne" tabindex="-1" aria-labelledby="modalFfneLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content custom-modal">
                <form id="formFfne" action="{{ route('kitchen.ffne.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="ffne_id">
                    <div class="modal-header custom-modal-header">
                        <h5 class="modal-title" id="modalFfneLabel">Formulir FF&E</h5>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal"><i
                                class="bi bi-x"></i></button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label>Kode FF&E</label>
                                <!-- ✅ INPUT DIUBAH MENJADI READONLY -->
                                <input type="text" class="form-control-custom" id="kode_ffne" name="kode_ffne"
                                    placeholder="Akan dibuat otomatis" readonly>
                            </div>
                            <div class="col-md-6">
                                <label>Nama FF&E</label>
                                <input type="text" class="form-control-custom" id="nama_ffne" name="nama_ffne"
                                    placeholder="Contoh: Meja Dapur Stainless" required>
                            </div>
                            <div class="col-md-6">
                                <label>Kategori</label>
                                <select class="form-control-custom" id="kategori_ffne" name="kategori_ffne" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <option value="Barang Habis Pakai">Barang Habis Pakai</option>
                                    <option value="Barang Tidak Habis Pakai">Barang Tidak Habis Pakai</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Harga</label>
                                <input type="number" class="form-control-custom" id="harga" name="harga"
                                    placeholder="Contoh: 500000" required>
                            </div>
                            <div class="col-md-6">
                                <label>Satuan</label>
                                <input type="text" class="form-control-custom" id="satuan_ffne" name="satuan_ffne"
                                    placeholder="Contoh: Unit / Pcs / Set" required>
                            </div>
                            <div class="col-md-6" id="stock_awal_group" style="display: none;">
                                <label>Stok Awal</label>
                                <input type="number" class="form-control-custom" id="stock_awal" name="stock"
                                    placeholder="Stok awal barang" value="0">
                                <small class="form-text text-muted">Hanya diisi untuk Barang Habis Pakai.</small>
                            </div>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="kondisi_ffne" name="kondisi_ffne"
                                    value="1">
                                <label class="form-check-label" for="kondisi_ffne">Tandai Rusak</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanFfne">
                            <i class="bi bi-check-circle"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExtra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content custom-modal">
                <div class="modal-header custom-modal-header">
                    <h5 class="modal-title">Riwayat Extra: <span id="namaFfneExtra" class="fw-bold"></span></h5>
                    <button type="button" class="btn-close-custom" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body custom-modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <button class="btn-add-primary" id="btnTambahExtra">
                            <i class="bi bi-plus-circle"></i> Tambah Riwayat Extra
                        </button>

                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center gap-1">
                                <label for="extrasRowsPerPage" class="form-label mb-0 small">Tampil</label>
                                <select id="extrasRowsPerPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                </select>
                                <span class="small">data</span>
                            </div>
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" id="extrasSearchInput" placeholder="Cari riwayat...">
                            </div>
                        </div>
                    </div>

                    <div id="listExtrasContainer" class="table-container"></div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div id="extrasInfo" class="small text-muted"></div>
                        <div id="extrasPagination" class="pagination-buttons"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalFormExtra" tabindex="-1" aria-labelledby="modalFormExtraLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formExtra" action="{{ route('kitchen.ffne.extras.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" id="extra_id">
                    <input type="hidden" name="ffne_id" id="extra_ffne_id">
                    <div class="modal-header custom-modal-header">
                        <h5 class="modal-title" id="modalFormExtraLabel">Formulir Riwayat Extra</h5>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close"><i
                                class="bi bi-x"></i></button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label>Nama Perbaikan/Item</label>
                                <input type="text" class="form-control-custom" name="nama" id="extra_nama"
                                    placeholder="Contoh: Ganti Kaki Meja" required>
                            </div>
                            <div class="col-md-6">
                                <label>Biaya</label>
                                <input type="number" class="form-control-custom" name="harga" id="extra_harga"
                                    placeholder="Contoh: 150000" required>
                            </div>
                            <div class="col-md-6">
                                <label>Tanggal</label>
                                <input type="date" class="form-control-custom" name="tanggal" id="extra_tanggal"
                                    required>
                            </div>
                            <div class="col-12">
                                <label>Keterangan</label>
                                <textarea class="form-control-custom" name="keterangan" id="extra_keterangan" rows="3"
                                    placeholder="Opsional: Jelaskan detail perbaikan..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanExtra">
                            <i class="bi bi-check-circle"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- <div class="modal fade" id="modalStockAdj" tabindex="-1" aria-labelledby="modalStockAdjLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formStockAdj" action="{{ route('kitchen.ffne.stock.submit') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ffne_id" id="adj_ffne_id">
                    <input type="hidden" name="type" value="adjustment">

                    <div class="modal-header custom-modal-header">
                        <h5 class="modal-title" id="modalStockAdjLabel">Penyesuaian Stok</h5>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal"><i
                                class="bi bi-x"></i></button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formStockAdjAlert"></div>

                        <div class="mb-3">
                            <h6 class="mb-0" id="adj_nama_ffne">Nama Barang</h6>
                            <small class="text-muted">Sisa Stok Saat Ini: <strong id="adj_current_stock">0</strong> <span
                                    id="adj_satuan">Unit</span></small>
                        </div>

                        <div class="form-group-custom">
                            <label for="adj_qty" class="form-label-custom required">Jumlah</label>
                            <input type="number" class="form-control-custom" id="adj_qty" name="qty"
                                min="0.01" step="any" placeholder="Jumlah yg disesuaikan" required>
                            <small class="form-text text-muted" id="adj_qty_help">
                                Masukkan jumlah <b>positif</b>. Akan diproses sebagai penyesuaian (opname stok).
                            </small>
                        </div>

                        <div class="form-group-custom">
                            <label for="adj_notes" class="form-label-custom">Catatan</label>
                            <textarea class="form-control-custom" id="adj_notes" name="notes" rows="2"
                                placeholder="Cth: Koreksi opname, revisi perhitungan, dll."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanAdj">Simpan Penyesuaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- INISIALISASI & DEKLARASI ---
            const modalFfne = new bootstrap.Modal(document.getElementById('modalFfne'));
            const modalExtra = new bootstrap.Modal(document.getElementById('modalExtra'));
            const modalFormExtra = new bootstrap.Modal(document.getElementById('modalFormExtra'));
            const stockAwalGroup = document.getElementById('stock_awal_group');
            const stockAwalInput = document.getElementById('stock_awal');
            const kategoriSelect = document.getElementById('kategori_ffne');
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const tabelFfne = document.getElementById('tabel-ffne');
            const btnTambahFfne = document.getElementById('btnTambahFfne');
            const formFfne = document.getElementById('formFfne');
            const btnSimpanFfne = document.getElementById('btnSimpanFfne');
            const kodeFfneInput = document.getElementById('kode_ffne');
            const kondisiCheckbox = document.getElementById('kondisi_ffne');

            const btnTambahExtra = document.getElementById('btnTambahExtra');
            const formExtra = document.getElementById('formExtra');
            const btnSimpanExtra = document.getElementById('btnSimpanExtra');
            const extrasRowsPerPage = document.getElementById('extrasRowsPerPage');
            const extrasSearchInput = document.getElementById('extrasSearchInput');
            const extrasPagination = document.getElementById('extrasPagination');
            const listExtrasContainer = document.getElementById('listExtrasContainer');
            const extrasInfo = document.getElementById('extrasInfo');
            // const modalStockAdj = new bootstrap.Modal(document.getElementById('modalStockAdj'));
            // const formStockAdj = document.getElementById('formStockAdj');
            // const alertStockAdj = document.getElementById('formStockAdjAlert');
            // const btnSimpanAdj = document.getElementById('btnSimpanAdj');

            let currentFfneId = null;

            let allExtras = [];
            let filteredExtras = [];
            let currentPage = 1;
            let rowsPerPage = 10;

            // --- Toggle Stock Field ---
            function toggleStockField() {
                if (!kategoriSelect || !stockAwalGroup) return;

                const ffneIdInput = document.getElementById('ffne_id');
                const ffneId = ffneIdInput ? ffneIdInput.value : '';
                if (ffneId) {
                    stockAwalGroup.style.display = 'none';
                    return;
                }

                if (kategoriSelect.value === 'Barang Habis Pakai') {
                    stockAwalGroup.style.display = 'block';
                    stockAwalInput.setAttribute('required', 'required');
                } else {
                    stockAwalGroup.style.display = 'none';
                    stockAwalInput.removeAttribute('required');
                    stockAwalInput.value = 0; // Set 0 jika disembunyikan
                }
            }

            if (kategoriSelect) {
                kategoriSelect.addEventListener('change', toggleStockField);
            }

            function applyTableFilters() {
                const searchText = searchInput ? searchInput.value.toLowerCase() : '';
                const selectedCategory = categoryFilter ? categoryFilter.value : '';

                document.querySelectorAll('#tabel-ffne tbody tr.data-row').forEach(function(row) {
                    const rowText = row.textContent.toLowerCase();
                    const rowCategory = row.dataset.kategori;

                    const categoryMatch = (!categoryFilter || selectedCategory === 'all' || rowCategory ===
                        selectedCategory);
                    const searchMatch = rowText.includes(searchText);

                    if (categoryMatch && searchMatch) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            if (searchInput) {
                searchInput.addEventListener('keyup', applyTableFilters);
            }
            if (categoryFilter) {
                categoryFilter.addEventListener('change', applyTableFilters);
            }

            // --- TOMBOL TAMBAH FF&E ---
            if (btnTambahFfne) {
                btnTambahFfne.addEventListener('click', function() {
                    if (formFfne) formFfne.reset();
                    const ffneIdInput = document.getElementById('ffne_id');
                    if (ffneIdInput) ffneIdInput.value = '';
                    const modalFfneLabel = document.getElementById('modalFfneLabel');
                    if (modalFfneLabel) modalFfneLabel.textContent = 'Tambah FF&E Baru';
                    if (kodeFfneInput) {
                        kodeFfneInput.value = '';
                        kodeFfneInput.placeholder = 'Akan dibuat otomatis';
                        kodeFfneInput.readOnly = true;
                    }

                    if (kategoriSelect) kategoriSelect.value = '';
                    if (stockAwalGroup) stockAwalGroup.style.display = 'none';
                    if (stockAwalInput) stockAwalInput.removeAttribute('required');

                    modalFfne.show();
                });
            }

            // --- SUBMIT FORM FF&E (TAMBAH & EDIT) ---
            if (formFfne) {
                formFfne.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    const btn = btnSimpanFfne;
                    const formData = new FormData(form);

                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';
                    }
                    showLoading('Menyimpan data FF&E...');

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                modalFfne.hide();
                                Swal.fire('Berhasil!', data.message, 'success').then(() => location
                                    .reload());
                            } else {
                                let errorMessages = data.message || 'Gagal menyimpan data.';
                                if (data.errors) {
                                    errorMessages = Object.values(data.errors).map(msg =>
                                        `<li>${msg}</li>`).join('');
                                    errorMessages = `<ul class="text-start">${errorMessages}</ul>`;
                                }
                                Swal.fire('Gagal!', errorMessages, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Submit FF&E Error:', error);
                            Swal.fire('Terjadi Kesalahan', 'Tidak dapat terhubung ke server.', 'error');
                        })
                        .finally(() => {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="bi bi-check-circle"></i> Simpan';
                            }
                            hideLoading();
                        });
                });
            }

            // --- Event na Tabel FFNE ---
            if (tabelFfne) {
                tabelFfne.addEventListener('click', function(e) {
                    const btnEdit = e.target.closest('.btnEditFfne');
                    const btnHapus = e.target.closest('.btnHapusFfne');
                    const btnExtra = e.target.closest('.btnExtraFfne');
                    const btnAdjStock = e.target.closest('.btn-adj-stock');

                    if (btnEdit) {
                        withAuth(function() {
                            if (formFfne) formFfne.reset();
                            const modalFfneLabel = document.getElementById('modalFfneLabel');
                            if (modalFfneLabel) modalFfneLabel.textContent = 'Edit Data FF&E';

                            ['id', 'kode_ffne', 'nama_ffne', 'kategori_ffne', 'harga', 'satuan_ffne']
                                .forEach(function(field) {
                                    const inputId = (field === 'id') ? 'ffne_id' : field;
                                    const element = document.getElementById(inputId);
                                    if (element && btnEdit.dataset.hasOwnProperty(field)) {
                                        element.value = btnEdit.dataset[field];
                                    }
                                });

                            if (kodeFfneInput) {
                                kodeFfneInput.readOnly = true;
                            }
                            if (kondisiCheckbox && btnEdit.dataset.hasOwnProperty('kondisi_ffne')) {
                                kondisiCheckbox.checked = (btnEdit.dataset.kondisi_ffne === '1');
                            }
                            if (stockAwalGroup) stockAwalGroup.style.display = 'none';
                            if (stockAwalInput) {
                                stockAwalInput.removeAttribute('required');
                                stockAwalInput.value = 0;
                            }
                            // Only attempt to show modal if it is properly initialized
                            if (typeof modalFfne !== "undefined" && modalFfne && typeof modalFfne.show === "function") {
                                modalFfne.show();
                            } else {
                                // fallback: open modal via jQuery if possible (Bootstrap 4/5 support)
                                const $modal = window.jQuery ? window.jQuery('#modalFfne') : null;
                                if ($modal && typeof $modal.modal === "function") {
                                    $modal.modal('show');
                                } else {
                                    // As last fallback, set display directly
                                    const modalElem = document.getElementById('modalFfne');
                                    if (modalElem) modalElem.style.display = 'block';
                                }
                            }
                        });
                    }

                    if (btnHapus) {
                        withAuth(function() {
                            const id = btnHapus.dataset.id;
                            const url = `{{ url('/kitchen/ffne') }}/${id}`;
                            Swal.fire({
                                title: 'Anda Yakin?',
                                text: "Data FF&E dan semua riwayatnya akan dihapus permanen!",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                confirmButtonText: 'Ya, Hapus!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    showLoading('Menghapus data...');
                                    fetch(url, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === 'success') {
                                                Swal.fire('Dihapus!', data.message,
                                                    'success').then(
                                                    () => location.reload());
                                            } else {
                                                Swal.fire('Gagal!', data.message ||
                                                    'Gagal menghapus data.', 'error'
                                                );
                                            }
                                        })
                                        .catch(error => Swal.fire('Terjadi Kesalahan',
                                            'Tidak dapat terhubung ke server.', 'error'
                                        ))
                                        .finally(() => hideLoading());
                                }
                            });
                        });
                    }

                    if (btnExtra) {
                        currentFfneId = btnExtra.dataset.id;
                        const namaFfneExtra = document.getElementById('namaFfneExtra');
                        if (namaFfneExtra) namaFfneExtra.textContent = btnExtra.dataset.nama;
                        showLoading('Memuat riwayat...');
                        loadExtrasFromServer(currentFfneId).finally(() => {
                            hideLoading();
                            modalExtra.show();
                        });
                    }

                    if (btnAdjStock) {
                        if (!formStockAdj || !modalStockAdj) return;

                        const id = btnAdjStock.dataset.id;
                        const nama = btnAdjStock.dataset.nama;
                        const stock = btnAdjStock.dataset.stock;
                        const satuan = btnAdjStock.dataset.satuan;

                        formStockAdj.reset();
                        alertStockAdj.innerHTML = '';
                        btnSimpanAdj.disabled = false;

                        document.getElementById('adj_ffne_id').value = id;
                        document.getElementById('adj_nama_ffne').textContent = nama || '-';
                        document.getElementById('adj_current_stock').textContent = stock || '0';
                        document.getElementById('adj_satuan').textContent = satuan || 'Unit';

                        modalStockAdj.show(); // <-- Tampilkan modal stock adj
                    }
                });
            }

            // --- TOMBOL TAMBAH EXTRA (DI DALAM MODAL) ---
            if (btnTambahExtra) {
                btnTambahExtra.addEventListener('click', function() {
                    if (formExtra) formExtra.reset();
                    const extraId = document.getElementById('extra_id');
                    const extraFfneId = document.getElementById('extra_ffne_id');
                    const modalFormExtraLabel = document.getElementById('modalFormExtraLabel');
                    if (extraId) extraId.value = '';
                    if (extraFfneId) extraFfneId.value = currentFfneId;
                    if (modalFormExtraLabel) modalFormExtraLabel.textContent = 'Tambah Riwayat Extra';
                    modalFormExtra.show();
                });
            }

            // --- SUBMIT FORM EXTRA (TAMBAH & EDIT) ---
            if (formExtra) {
                formExtra.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    const btn = btnSimpanExtra;
                    const formData = new FormData(form);

                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';
                    }
                    showLoading('Menyimpan riwayat...');

                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                modalFormExtra.hide();
                                Swal.fire('Berhasil!', data.message, 'success');
                                loadExtrasFromServer(currentFfneId);
                            } else {
                                let errorMessages = data.message || 'Gagal menyimpan data.';
                                if (data.errors) {
                                    errorMessages = Object.values(data.errors).map(msg =>
                                        `<li>${msg}</li>`).join('');
                                    errorMessages = `<ul class="text-start">${errorMessages}</ul>`;
                                }
                                Swal.fire('Gagal!', errorMessages, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Submit Extra Error:', error);
                            Swal.fire('Terjadi Kesalahan', 'Tidak dapat terhubung ke server.', 'error');
                        })
                        .finally(() => {
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="bi bi-check-circle"></i> Simpan';
                            }
                            hideLoading();
                        });
                });
            }

            // --- KONTROL INTERAKTIF TABEL EXTRA ---
            if (extrasRowsPerPage) {
                extrasRowsPerPage.addEventListener('change', function() {
                    rowsPerPage = parseInt(this.value, 10);
                    currentPage = 1;
                    renderExtrasTable();
                });
            }

            if (extrasSearchInput) {
                extrasSearchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    filteredExtras = allExtras.filter(function(extra) {
                        return extra.nama.toLowerCase().includes(searchTerm) ||
                            (extra.keterangan && extra.keterangan.toLowerCase().includes(
                                searchTerm));
                    });
                    currentPage = 1;
                    renderExtrasTable();
                });
            }

            if (extrasPagination) {
                extrasPagination.addEventListener('click', function(e) {
                    const target = e.target.closest('.btn-pagination');
                    if (!target || target.disabled) return;
                    const page = target.dataset.page;
                    const totalPages = Math.ceil(filteredExtras.length / rowsPerPage);

                    if (page === 'prev') {
                        if (currentPage > 1) currentPage--;
                    } else if (page === 'next') {
                        if (currentPage < totalPages) currentPage++;
                    } else {
                        currentPage = parseInt(page, 10);
                    }
                    renderExtrasTable();
                });
            }

            // --- AKSI PADA TABEL EXTRA (EDIT & HAPUS) ---
            if (listExtrasContainer) {
                listExtrasContainer.addEventListener('click', function(e) {
                    const btnEdit = e.target.closest('.btn-edit-extra');
                    const btnHapus = e.target.closest('.btn-hapus-extra');

                    if (btnEdit) {
                        const extraData = JSON.parse(btnEdit.dataset.extra);
                        const extraId = document.getElementById('extra_id');
                        const extraFfneId = document.getElementById('extra_ffne_id');
                        const extraNama = document.getElementById('extra_nama');
                        const extraHarga = document.getElementById('extra_harga');
                        const extraTanggal = document.getElementById('extra_tanggal');
                        const extraKeterangan = document.getElementById('extra_keterangan');
                        const modalFormExtraLabel = document.getElementById('modalFormExtraLabel');
                        if (extraId) extraId.value = extraData.id;
                        if (extraFfneId) extraFfneId.value = extraData.ffne_id;
                        if (extraNama) extraNama.value = extraData.nama;
                        if (extraHarga) extraHarga.value = extraData.harga;
                        if (extraTanggal) extraTanggal.value = extraData.tanggal;
                        if (extraKeterangan) extraKeterangan.value = extraData.keterangan;
                        if (modalFormExtraLabel) modalFormExtraLabel.textContent = 'Edit Riwayat Extra';
                        modalFormExtra.show();
                    }

                    if (btnHapus) {
                        const id = btnHapus.dataset.id;
                        const url = `{{ url('/kitchen/ffne/extras') }}/${id}`;
                        Swal.fire({
                            title: 'Anda Yakin?',
                            text: "Data riwayat ini akan dihapus permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Ya, Hapus!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showLoading('Menghapus riwayat...');
                                fetch(url, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.status === 'success') {
                                            Swal.fire('Dihapus!', data.message, 'success');
                                            loadExtrasFromServer(currentFfneId);
                                        } else {
                                            Swal.fire('Gagal!', data.message ||
                                                'Gagal menghapus data.', 'error');
                                        }
                                    })
                                    .catch(error => Swal.fire('Terjadi Kesalahan',
                                        'Tidak dapat terhubung.', 'error'))
                                    .finally(() => hideLoading());
                            }
                        });
                    }
                });
            }

            // --- FUNGSI HELPER UNTUK MODAL EXTRA ---
            function loadExtrasFromServer(ffneId) {
                const url = `{{ url('/kitchen/ffne') }}/${ffneId}/extras`;
                return fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        allExtras = data;
                        filteredExtras = [...allExtras];
                        currentPage = 1;
                        if (extrasSearchInput) extrasSearchInput.value = '';
                        renderExtrasTable();
                    })
                    .catch(error => {
                        console.error('Load Extras Error:', error);
                        if (listExtrasContainer)
                            listExtrasContainer.innerHTML =
                            `<p class="text-center text-danger py-5">Gagal memuat data.</p>`;
                        if (extrasInfo) extrasInfo.textContent = '';
                        if (extrasPagination) extrasPagination.innerHTML = '';
                    });
            }

            function renderExtrasTable() {
                const container = listExtrasContainer;
                const infoEl = extrasInfo;
                const paginationEl = extrasPagination;

                if (!container || !infoEl || !paginationEl) return;

                if (filteredExtras.length === 0) {
                    container.innerHTML =
                        `<div class="empty-state"><div class="empty-content"><i class="bi bi-tools"></i><h4>Belum ada riwayat</h4><p>Data riwayat untuk item ini kosong.</p></div></div>`;
                    infoEl.textContent = 'Tidak ada data';
                    paginationEl.innerHTML = '';
                    return;
                }

                const totalRows = filteredExtras.length;
                const totalPages = Math.ceil(totalRows / rowsPerPage);
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                const paginatedItems = filteredExtras.slice(start, end);

                const tableRows = paginatedItems.map(function(extra) {
                    return `
            <tr>
                <td>${extra.nama}</td>
                <td>Rp ${parseInt(extra.harga).toLocaleString('id-ID')}</td>
                <td>${new Date(extra.tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })}</td>
                <td>${extra.keterangan || '-'}</td>
                <td class="col-action">
                    <div class="action-buttons">
                        <button class="btn-action btn-edit btn-edit-extra" title="Edit" data-extra='${JSON.stringify(extra)}'><i class="bi bi-pencil-square"></i></button>
                        <button class="btn-action btn-delete btn-hapus-extra" title="Hapus" data-id="${extra.id}"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>`;
                }).join('');

                container.innerHTML = `
            <table class="data-table w-100">
                <thead><tr><th>Nama Item/Perbaikan</th><th>Biaya</th><th>Tanggal</th><th>Keterangan</th><th>Aksi</th></tr></thead>
                <tbody>${tableRows}</tbody>
            </table>`;

                infoEl.textContent =
                    `Menampilkan ${start + 1} - ${Math.min(end, totalRows)} dari ${totalRows} data`;

                let paginationHTML =
                    `<button class="btn-pagination" data-page="prev" ${currentPage === 1 ? 'disabled' : ''}>&laquo;</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML +=
                        `<button class="btn-pagination ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</button>`;
                }
                paginationHTML +=
                    `<button class="btn-pagination" data-page="next" ${currentPage === totalPages ? 'disabled' : ''}>&raquo;</button>`;
                paginationEl.innerHTML = paginationHTML;
            }
        });
    </script>
@endpush
