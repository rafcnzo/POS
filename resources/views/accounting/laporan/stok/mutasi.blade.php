{{-- resources/views/accounting/laporan/stok/mutasi.blade.php --}}
@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-title-wrapper">
                    <div class="page-icon"><i class="bi bi-arrow-left-right"></i></div>
                    <div>
                        <h1 class="page-title">Laporan Mutasi Stok</h1>
                        <p class="page-subtitle">{{ $reportTitle }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
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
                                        <option value="in" {{ $filters['movement_type'] == 'in' ? 'selected' : '' }}>
                                            Barang Masuk</option>
                                        <option value="out" {{ $filters['movement_type'] == 'out' ? 'selected' : '' }}>
                                            Barang Keluar</option>
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
                                    <input type="hidden" name="item_id" id="item_id" value="{{ $filters['item_id'] }}">
                                    <input type="hidden" name="item_type" id="item_type"
                                        value="{{ $filters['item_type'] }}">
                                </div>
                                <div class="col-lg-3 col-md-12 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary" style="flex: 1"
                                        aria-label="Terapkan filter">
                                        <i class="bi bi-search"></i> Filter
                                    </button>
                                    <a href="{{ route('acc.laporan-stok-mutasi') }}" class="btn btn-outline-secondary"
                                        aria-label="Reset filter" data-bs-toggle="tooltip" title="Reset">
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

            <!-- Data Table Section -->
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
                                        <th scope="col" class="text-end" style="width: 12%;">Quantity Stok</th>
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
                                                    <span class="badge bg-soft-success text-success">Bahan Baku</span>
                                                @else
                                                    <span class="badge bg-soft-info text-info">FFNE</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($movement->movement_direction === 'in')
                                                    <span class="badge bg-success"><i class="bi bi-arrow-down"></i>
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
                                                    <p class="text-muted">Belum ada mutasi stok untuk filter yang dipilih.
                                                    </p>
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
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- Skrip Item Selection tetap ---
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

            // --- KODE EKSPOR EXCEL BARU VIA JS ---
            const btnExportExcel = document.getElementById('btnExportExcel');
            if (btnExportExcel) {
                btnExportExcel.addEventListener('click', function(event) {
                    event.preventDefault();
                    const url = this.dataset.url;
                    const btn = this;
                    const originalHtml = btn.innerHTML;

                    // 1. Visual loading
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Loading...';
                    if (typeof showLoading === 'function') {
                        showLoading('Mengambil data laporan...');
                    }

                    // 2. Fetch json dari controller
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

        // --- KODE EKSPOR PDF: gunakan handler bawaan lama ---
        function handleExportClick(event) {
            event.preventDefault();
            const link = event.currentTarget;
            const url = link.dataset.url;
            const format = link.dataset.format || 'file';

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
        }

        // Initialize tooltips if Bootstrap is loaded
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
        });
    </script>
@endpush
