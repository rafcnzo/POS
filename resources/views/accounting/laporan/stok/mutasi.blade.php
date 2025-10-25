{{-- resources/views/accounting/laporan/stok/mutasi.blade.php --}}
@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon"><i class="bi bi-arrow-left-right"></i></div>
                        <div>
                            <h1 class="page-title">Laporan Mutasi & Opname</h1>
                            <p class="page-subtitle">{{ $reportTitle }}</p>
                        </div>
                    </div>
                    <button class="btn-add-warning" id="btnBukaModalOpname">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Stock Opname</span>
                    </button>
                </div>
            </div>

            <ul class="nav nav-tabs nav-tabs-bordered" id="stockTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="mutasi-tab" data-bs-toggle="tab" data-bs-target="#mutasi-content"
                        type="button" role="tab" aria-controls="mutasi-content" aria-selected="true">
                        <i class="bi bi-arrow-left-right me-2"></i>Laporan Mutasi Stok
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="opname-tab" data-bs-toggle="tab" data-bs-target="#opname-content"
                        type="button" role="tab" aria-controls="opname-content" aria-selected="false">
                        <i class="bi bi-clipboard-check me-2"></i>Detail Opname Hari Ini
                    </button>
                </li>
            </ul>

            <div class="tab-content pt-3" id="stockTabsContent">
                {{-- Tab 1: Mutasi Stok --}}
                <div class="tab-pane fade show active" id="mutasi-content" role="tabpanel" aria-labelledby="mutasi-tab">
                    <section class="filter-section mb-4">
                        <div class="data-card">
                            <div class="data-card-body p-4">
                                <form method="GET" action="{{ route('acc.laporan-stok-mutasi') }}">
                                    <div class="row g-3">
                                        <div class="col-lg-2 col-md-6">
                                            <label for="start_date" class="form-label">Dari Tanggal</label>
                                            <input type="date" class="form-control" name="start_date" id="start_date"
                                                value="{{ $filters['start_date'] }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label for="end_date" class="form-label">Sampai Tanggal</label>
                                            <input type="date" class="form-control" name="end_date" id="end_date"
                                                value="{{ $filters['end_date'] }}">
                                        </div>
                                        <div class="col-lg-2 col-md-6">
                                            <label for="movement_type" class="form-label">Jenis Mutasi</label>
                                            <select name="movement_type" id="movement_type" class="form-select">
                                                <option value="">-- Semua Mutasi --</option>
                                                <option value="in"
                                                    {{ $filters['movement_type'] == 'in' ? 'selected' : '' }}>
                                                    Barang Masuk
                                                </option>
                                                <option value="out"
                                                    {{ $filters['movement_type'] == 'out' ? 'selected' : '' }}>
                                                    Barang Keluar
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3 col-md-6">
                                            <label for="item_search" class="form-label">Nama Item</label>
                                            <select name="item_search" id="item_search" class="form-select">
                                                <option value="">-- Semua Item --</option>
                                                @foreach ($allItems as $item)
                                                    <option value="{{ $item['type'] }}:{{ $item['id'] }}"
                                                        {{ $filters['item_type'] == $item['type'] && $filters['item_id'] == $item['id'] ? 'selected' : '' }}>
                                                        {{ $item['display_name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="item_id" id="item_id"
                                                value="{{ $filters['item_id'] }}">
                                            <input type="hidden" name="item_type" id="item_type"
                                                value="{{ $filters['item_type'] }}">
                                        </div>
                                        <div class="col-lg-3 col-md-12 d-flex align-items-end gap-2">
                                            <button type="submit" class="btn btn-primary" style="flex: 1">
                                                <i class="bi bi-search"></i> Filter
                                            </button>
                                            <a href="{{ route('acc.laporan-stok-mutasi') }}"
                                                class="btn btn-outline-secondary" aria-label="Reset filter"
                                                data-bs-toggle="tooltip" title="Reset">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-success" id="btnExportExcel"
                                                data-url="{{ route('acc.laporan-stok-mutasi.export', array_merge(request()->query(), ['type' => 'excel'])) }}"
                                                data-format="EXCEL" aria-label="Ekspor ke Excel" data-bs-toggle="tooltip"
                                                title="Ekspor ke Excel">
                                                <i class="bi bi-file-earmark-excel"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" id="btnExportPdf"
                                                data-url="{{ route('acc.laporan-stok-mutasi.export', array_merge(request()->query(), ['type' => 'pdf'])) }}"
                                                data-format="PDF" aria-label="Ekspor ke PDF" data-bs-toggle="tooltip"
                                                title="Ekspor ke PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>

                    <section class="table-section">
                        <div class="data-card">
                            <div class="data-card-header">
                                <h5 class="mb-0">Daftar Mutasi Stok</h5>
                            </div>
                            <div class="data-card-body">
                                <div class="table-responsive">
                                    <table class="table data-table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col" class="text-center" style="width: 3%;">#</th>
                                                <th scope="col" style="width: 11%;">Tanggal</th>
                                                <th scope="col" style="width: 13%;">No. Referensi</th>
                                                <th scope="col" style="width: 24%;">Nama Item</th>
                                                <th scope="col" style="width: 10%;">Tipe</th>
                                                <th scope="col" class="text-center" style="width: 9%;">Mutasi</th>
                                                <th scope="col" class="text-end" style="width: 12%;">Quantity Stok
                                                </th>
                                                <th scope="col" style="width: 18%;">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($movements as $i => $movement)
                                                <tr>
                                                    <td class="text-center">{{ ($movements->firstItem() ?? 1) + $i }}</td>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($movement->movement_date)->translatedFormat('d M Y H:i') }}
                                                    </td>
                                                    <td class="fw-semibold text-primary">{{ $movement->reference }}</td>
                                                    <td>{{ $movement->name }}</td>
                                                    <td>
                                                        @if ($movement->item_type === 'ingredient')
                                                            <span class="badge bg-soft-success text-success">Bahan
                                                                Baku</span>
                                                        @else
                                                            <span class="badge bg-soft-info text-info">FFNE</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($movement->movement_direction === 'in')
                                                            <span class="badge bg-success"><i
                                                                    class="bi bi-arrow-down"></i>
                                                                Masuk</span>
                                                        @else
                                                            <span class="badge bg-danger"><i class="bi bi-arrow-up"></i>
                                                                Keluar</span>
                                                        @endif
                                                    </td>
                                                    <td
                                                        class="text-end fw-bold {{ $movement->movement_direction === 'in' ? 'text-success' : 'text-danger' }}">
                                                        {{ $movement->movement_direction === 'in' ? '+' : '-' }}{{ number_format($movement->quantity, 2, ',', '.') }}
                                                    </td>
                                                    <td>{{ $movement->description }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <div class="empty-state">
                                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                                            <h4 class="mt-3">Tidak ada data mutasi stok</h4>
                                                            <p class="text-muted">Belum ada mutasi stok untuk filter yang
                                                                dipilih.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if ($movements->hasPages())
                                    <div class="mt-4">
                                        {{ $movements->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                </div> {{-- /tab mutasi --}}

                <div class="tab-pane fade" id="opname-content" role="tabpanel" aria-labelledby="opname-tab">
                    <div class="data-card">
                        <div class="data-card-header d-flex align-items-center justify-content-between px-3 py-2">
                            <h5 class="mb-0">Detail Stock Opname ({{ now()->translatedFormat('d F Y') }})</h5>
                            <div class="data-card-actions">
                                <button type="button" class="btn btn-outline-success" id="btnExportOpnameExcel"
                                    data-url="{{ route('acc.stock-opname.export.excel') }}">
                                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-danger"
                                    id="btnExportOpnamePdf"
                                    data-url="{{ route('acc.stock-opname.export.pdf') }}"
                                    data-format="pdf">
                                    <i class="bi bi-file-earmark-pdf"></i> Export PDF
                                </button>
                            </div>
                        </div>
                        <div class="data-card-body px-3 py-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0" id="tabel-opname-hari-ini">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-2 py-2">Waktu</th>
                                            <th class="px-2 py-2">Nama Item</th>
                                            <th class="px-2 py-2">Tipe</th>
                                            <th class="text-end px-2 py-2">Stok Sistem</th>
                                            <th class="text-end px-2 py-2">Stok Fisik</th>
                                            <th class="text-end px-2 py-2">Selisih</th>
                                            <th class="px-2 py-2">User</th>
                                            <th class="px-2 py-2">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($todayOpnames as $opname)
                                            <tr>
                                                <td class="px-2 py-2">{{ $opname->timestamp ? \Carbon\Carbon::parse($opname->timestamp)->format('H:i:s') : '-' }}</td>
                                                <td class="px-2 py-2">{{ $opname->item_name }}</td>
                                                <td class="px-2 py-2">
                                                    @if ($opname->item_type == 'ingredient')
                                                        <span class="badge bg-soft-success text-success">Bahan Baku</span>
                                                    @else
                                                        <span class="badge bg-soft-info text-info">FFNE</span>
                                                    @endif
                                                </td>
                                                <td class="text-end px-2 py-2">
                                                    {{ number_format($opname->stock_before, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end px-2 py-2">
                                                    {{ number_format($opname->stock_after, 2, ',', '.') }}
                                                </td>
                                                <td class="text-end fw-bold px-2 py-2 @if ($opname->adjustment_qty < 0) text-danger @elseif($opname->adjustment_qty > 0) text-success @endif">
                                                    {{ number_format($opname->adjustment_qty, 2, ',', '.') }}
                                                </td>
                                                <td class="px-2 py-2">{{ $opname->user_name }}</td>
                                                <td class="px-2 py-2">{{ $opname->notes }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">
                                                    <i class="bi bi-check-all fs-3 d-block mb-2"></i>
                                                    Belum ada data stock opname hari ini.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>{{-- /tab opname --}}
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalStockOpname" tabindex="-1" aria-labelledby="modalStockOpnameLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formStockOpname" data-url="{{ route('acc.stock-opname.submit') }}"> {{-- Sesuaikan route --}}
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalStockOpnameLabel">Form Stock Opname</h5>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal"><i
                                class="bi bi-x"></i></button>
                    </div>
                    <div class="modal-body">
                        <div id="formOpnameAlert"></div>

                        <div class="form-group-custom">
                            <label for="opname_item_id" class="form-label-custom required">Pilih Item</label>
                            <select class="form-control-custom" id="opname_item_id" name="item_id_type" required>
                                <option value="">-- Pilih Bahan Baku / FFNE --</option>
                                @foreach ($allItems as $item)
                                    <option value="{{ $item['id'] }}:{{ $item['type'] }}"
                                        data-stock="{{ isset($item['stock']) ? $item['stock'] : '' }}"
                                        data-unit="{{ isset($item['unit']) ? $item['unit'] : '' }}">
                                        {{ $item['display_name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="item_id" id="opname_item_id_hidden">
                            <input type="hidden" name="item_type" id="opname_item_type_hidden">
                        </div>

                        <div class="form-group-custom">
                            <label for="opname_stock" class="form-label-custom required">Stok Sistem (Saat Ini)</label>
                            <input type="number" class="form-control-custom" id="opname_stock" name="stock"
                                min="0" step="any" readonly placeholder="Otomatis terisi setelah pilih item">
                        </div>

                        <div class="form-group-custom">
                            <label for="opname_unit" class="form-label-custom required">Satuan</label>
                            <input type="text" class="form-control-custom" id="opname_unit" name="unit" readonly
                                placeholder="Otomatis terisi setelah pilih item">
                        </div>

                        <div class="form-group-custom">
                            <label for="opname_actual_stock" class="form-label-custom required">Stok Fisik
                                (Aktual)</label>
                            <input type="number" class="form-control-custom" id="opname_actual_stock"
                                name="actual_stock" min="0" step="any" required
                                placeholder="Jumlah stok hasil hitungan fisik">
                        </div>

                        <div class="form-group-custom">
                            <label for="opname_notes" class="form-label-custom">Catatan Opname</label>
                            <textarea class="form-control-custom" id="opname_notes" name="notes" rows="2"
                                placeholder="Cth: Hasil opname bulanan, barang rusak, dll."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanOpname">Simpan Penyesuaian</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemSearchSelect = document.getElementById('item_search');
            const itemIdInput = document.getElementById('item_id');
            const itemTypeInput = document.getElementById('item_type');

            if (itemSearchSelect) {
                itemSearchSelect.addEventListener('change', function() {
                    const value = this.value;
                    if (value) {
                        const [type, id] = value.split(':');
                        itemTypeInput.value = type;
                        itemIdInput.value = id;
                    } else {
                        itemTypeInput.value = '';
                        itemIdInput.value = '';
                    }
                });
            }

            const btnExportExcel = document.getElementById('btnExportExcel');
            if (btnExportExcel) {
                btnExportExcel.addEventListener('click', function(event) {
                    event.preventDefault();
                    const url = this.dataset.url;
                    const btn = this;
                    const originalHtml = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Loading...';
                    if (typeof showLoading === 'function') {
                        showLoading('Mengambil data laporan...');
                    }

                    fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;

                            if (typeof hideLoading === 'function') hideLoading();

                            if (data.status === 'success' && Array.isArray(data.salesData)) {
                                if (data.salesData.length > 0) {
                                    const ws = XLSX.utils.json_to_sheet(data.salesData);
                                    const wb = XLSX.utils.book_new();
                                    XLSX.utils.book_append_sheet(wb, ws, "Mutasi Stok");
                                    XLSX.writeFile(wb, data.fileName || "laporan-mutasi-stok.xlsx");
                                } else {
                                    Swal.fire('Data Kosong', 'Tidak ada data untuk diekspor.', 'info');
                                }
                            } else {
                                throw new Error(data.message || 'Gagal mengambil data');
                            }
                        })
                        .catch(error => {
                            if (typeof hideLoading === 'function') hideLoading();
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                            console.error('Export Error:', error);
                            Swal.fire('Gagal Ekspor', error.message, 'error');
                        });
                });
            }

            // Handler khusus untuk btnExportPdf
            // Handler untuk ekspor PDF Mutasi
            const btnExportPdf = document.getElementById('btnExportPdf');
            if (btnExportPdf) {
                btnExportPdf.addEventListener('click', function(event) {
                    event.preventDefault();
                    const url = this.dataset.url;
                    const format = this.dataset.format || 'PDF';

                    Swal.fire({
                        title: `Ekspor Laporan ke ${format}?`,
                        text: "Filter yang sedang aktif akan diterapkan pada file ekspor.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ekspor!',
                        cancelButtonText: 'Batal',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                });
            }

            // Handler untuk ekspor PDF Opname
            const btnExportOpnamePdf = document.getElementById('btnExportOpnamePdf');
            if (btnExportOpnamePdf) {
                btnExportOpnamePdf.addEventListener('click', function(event) {
                    event.preventDefault();
                    const url = this.dataset.url;
                    const format = this.dataset.format || 'PDF';

                    Swal.fire({
                        title: `Ekspor Laporan Opname ke ${format}?`,
                        text: "Data opname hari ini akan diekspor ke file.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Ekspor!',
                        cancelButtonText: 'Batal',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (!url) {
                                Swal.fire('Gagal Ekspor', 'URL ekspor tidak tersedia.', 'error');
                                return;
                            }
                            window.location.href = url;
                        }
                    });
                });
            }

            if (typeof bootstrap !== 'undefined') {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            const modalOpnameEl = document.getElementById('modalStockOpname');
            if (modalOpnameEl) {
                const modalOpname = new bootstrap.Modal(modalOpnameEl);
                const formOpname = document.getElementById('formStockOpname');
                const alertOpname = document.getElementById('formOpnameAlert');
                const btnSimpanOpname = document.getElementById('btnSimpanOpname');
                const selectItem = document.getElementById('opname_item_id');
                const fieldStock = document.getElementById('opname_stock');
                const fieldUnit = document.getElementById('opname_unit');

                document.getElementById('btnBukaModalOpname').addEventListener('click', function() {
                    formOpname.reset();
                    alertOpname.innerHTML = '';
                    alertOpname.style.display = 'none';
                    document.getElementById('opname_item_id_hidden').value = '';
                    document.getElementById('opname_item_type_hidden').value = '';
                    if (fieldStock) fieldStock.value = '';
                    if (fieldUnit) fieldUnit.value = '';
                    modalOpname.show();
                });

                selectItem.addEventListener('change', function() {
                    const [id, type] = this.value.split(':');
                    document.getElementById('opname_item_id_hidden').value = id || '';
                    document.getElementById('opname_item_type_hidden').value = type || '';

                    const selectedOption = this.options[this.selectedIndex];
                    const stock = selectedOption.getAttribute('data-stock');
                    const unit = selectedOption.getAttribute('data-unit');

                    if (fieldStock) fieldStock.value = stock !== null ? stock : '';
                    if (fieldUnit) fieldUnit.value = unit !== null ? unit : '';
                });

                formOpname.addEventListener('submit', function(e) {
                    e.preventDefault();
                    alertOpname.innerHTML = '';
                    btnSimpanOpname.disabled = true;
                    btnSimpanOpname.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';
                    showLoading('Menyimpan Stock Opname...');

                    const formData = new FormData(formOpname);
                    const url = formOpname.dataset.url; // URL dari data-url form

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': formData.get('_token'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(async response => {
                            const data = await response.json();
                            hideLoading();
                            btnSimpanOpname.disabled = false;
                            btnSimpanOpname.innerHTML = 'Simpan Penyesuaian';

                            if (response.ok) {
                                modalOpname.hide();
                                Swal.fire('Berhasil', data.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                const errorMsg = data.errors ? Object.values(data.errors).flat()
                                    .join('<br>') : data.message;
                                alertOpname.innerHTML =
                                    `<div class="alert alert-danger" role="alert">${errorMsg}</div>`;
                                alertOpname.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            btnSimpanOpname.disabled = false;
                            btnSimpanOpname.innerHTML = 'Simpan Penyesuaian';
                            alertOpname.innerHTML =
                                `<div class="alert alert-danger">Error Jaringan: ${error.message}</div>`;
                            alertOpname.style.display = 'block';
                        });
                });
            }

            const btnExportOpnameExcel = document.getElementById('btnExportOpnameExcel');

            if (btnExportOpnameExcel) {
                btnExportOpnameExcel.addEventListener('click', function(event) {
                    event.preventDefault();
                    const url = this.dataset.url;
                    const btn = this;
                    const originalHtml = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Loading...';
                    if (typeof showLoading === 'function') showLoading('Mengambil data opname...');

                    fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Ambil token CSRF
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (typeof hideLoading === 'function') hideLoading();

                            if (data.status === 'success' && Array.isArray(data.salesData) && data
                                .salesData.length > 0) {

                                const ws = XLSX.utils.json_to_sheet(data.salesData);
                                const wb = XLSX.utils.book_new();

                                ws['!cols'] = [{
                                        wch: 10
                                    }, 
                                    {
                                        wch: 30
                                    }, 
                                    {
                                        wch: 10
                                    }, 
                                    {
                                        wch: 15
                                    }, 
                                    {
                                        wch: 15
                                    }, 
                                    {
                                        wch: 15
                                    }, 
                                    {
                                        wch: 20
                                    }, 
                                    {
                                        wch: 30
                                    } 
                                ];

                                XLSX.utils.book_append_sheet(wb, ws, "Opname Hari Ini");
                                XLSX.writeFile(wb, data.fileName || "laporan-opname-hari-ini.xlsx");

                            } else if (data.salesData && data.salesData.length === 0) {
                                Swal.fire('Data Kosong', 'Tidak ada data opname untuk diekspor.',
                                    'info');
                            } else {
                                throw new Error(data.message || 'Gagal mengambil data');
                            }
                        })
                        .catch(error => {
                            if (typeof hideLoading === 'function') hideLoading();
                            console.error('Export Opname Error:', error);
                            Swal.fire('Gagal Ekspor', error.message, 'error');
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-file-earmark-excel"></i> Export Excel';
                        });
                });
            }
        });
    </script>
@endpush
