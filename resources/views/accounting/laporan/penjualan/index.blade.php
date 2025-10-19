@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-title-wrapper">
                    <div class="page-icon"><i class="bi bi-bar-chart-line"></i></div>
                    <div>
                        <h1 class="page-title">Laporan Penjualan</h1>
                        <p class="page-subtitle">{{ $reportTitle }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <section class="filter-section mb-4">
                <div class="data-card">
                    <div class="data-card-body p-4">
                        <form method="GET" action="{{ route('acc.laporan-penjualan') }}">
                            <div class="row g-3">
                                <!-- Filter by Date -->
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <label for="filter_date" class="form-label">Per Tanggal</label>
                                    <input type="date" class="form-control" name="filter_date" id="filter_date"
                                        value="{{ $filters['date'] }}">
                                </div>
                                <!-- Filter by Month -->
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <label for="filter_month" class="form-label">Per Bulan</label>
                                    <select class="form-select" name="filter_month" id="filter_month">
                                        <option value="">-- Pilih Bulan --</option>
                                        @php
                                            $bulanIndo = [
                                                1 => 'Januari',
                                                2 => 'Februari',
                                                3 => 'Maret',
                                                4 => 'April',
                                                5 => 'Mei',
                                                6 => 'Juni',
                                                7 => 'Juli',
                                                8 => 'Agustus',
                                                9 => 'September',
                                                10 => 'Oktober',
                                                11 => 'November',
                                                12 => 'Desember',
                                            ];
                                        @endphp
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}"
                                                {{ $filters['month'] == $m ? 'selected' : '' }}>
                                                {{ $bulanIndo[$m] }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <!-- Filter by Year -->
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <label for="filter_year" class="form-label">Per Tahun</label>
                                    <select class="form-select" name="filter_year" id="filter_year">
                                        <option value="">-- Pilih Tahun --</option>
                                        @foreach ($availableYears as $year)
                                            <option value="{{ $year }}"
                                                {{ $filters['year'] == $year ? 'selected' : '' }}>
                                                {{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Submit & Reset Button -->
                                <div class="col-lg-3 col-md-4 col-sm-6 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary w-100" aria-label="Terapkan filter">
                                        <i class="bi bi-search me-1"></i>
                                        <span>Filter</span>
                                    </button>
                                    <a href="{{ route('acc.laporan-penjualan') }}" class="btn btn-outline-secondary"
                                        aria-label="Reset filter"><i class="bi bi-arrow-clockwise"></i></a>
                                    <button type="button" id="btnExportExcel"
                                        data-url="{{ route('acc.laporan-penjualan.export.excel', request()->query()) }}"
                                        class="btn btn-success" data-format="Excel" aria-label="Ekspor ke Excel"
                                        data-bs-toggle="tooltip" title="Ekspor ke Excel">
                                        <i class="bi bi-file-earmark-excel"></i>
                                    </button>
                                    <button type="button" id="btnExportPdf"
                                        data-url="{{ route('acc.laporan-penjualan.export.pdf', request()->query()) }}"
                                        class="btn btn-danger" data-format="PDF" aria-label="Ekspor ke PDF"
                                        data-bs-toggle="tooltip" title="Ekspor ke PDF">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <!-- Stats Section -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-primary">
                    <div class="stat-info">
                        <h3 class="stat-value">Rp {{ number_format($summary->total_revenue, 0, ',', '.') }}</h3>
                        <p class="stat-label">Total Penjualan</p>
                    </div>
                </div>
                <div class="stat-card stat-secondary">
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $summary->total_transactions }}</h3>
                        <p class="stat-label">Total Transaksi</p>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-info">
                        <h3 class="stat-value">Rp {{ number_format($summary->average_sale, 0, ',', '.') }}</h3>
                        <p class="stat-label">Rata-rata/Transaksi</p>
                    </div>
                </div>
                <div class="stat-card stat-success">
                    <div class="stat-info">
                        <h3 class="stat-value">Rp {{ number_format($summary->total_tax, 0, ',', '.') }}</h3>
                        <p class="stat-label">Total Pajak</p>
                    </div>
                </div>
            </div>

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
                                        <th scope="col" class="text-end" style="width: 150px;">Subtotal</th>
                                        <th scope="col" class="text-end" style="width: 100px;">Diskon</th>
                                        <th scope="col" class="text-end" style="width: 100px;">Pajak</th>
                                        <th scope="col" class="text-end" style="width: 140px;">Total Akhir</th>
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
                                            <td class="text-end">
                                                Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end text-danger">
                                                Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end text-primary">
                                                Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="text-end fw-bold">
                                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">
                                                <div class="empty-state text-center py-5">
                                                    <div class="empty-icon mb-3">
                                                        <i class="bi bi-clock-history display-1 text-muted"></i>
                                                    </div>
                                                    <h4 class="text-muted mb-2">Tidak ada data untuk filter yang dipilih.
                                                    </h4>
                                                    <p class="text-muted mb-0">
                                                        Belum ada transaksi yang selesai untuk filter ini.
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        @if ($sales->hasPages())
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
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function validateFilterInputs() {
                const filterDate = document.getElementById('filter_date').value;
                const filterMonth = document.getElementById('filter_month').value;
                const filterYear = document.getElementById('filter_year').value;

                // Jika tidak ada filter sama sekali
                if (filterDate === '' && filterMonth === '' && filterYear === '') {
                    Swal.fire({
                        title: 'Filter belum dipilih!',
                        text: 'Silakan pilih minimal salah satu filter tanggal, bulan/tahun, atau tahun sebelum mengekspor.',
                        icon: 'warning',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                // Validasi: Jika tanggal dipilih tapi bulan tidak, error
                if (filterDate !== '' && filterMonth === '') {
                    Swal.fire({
                        title: 'Filter tidak lengkap!',
                        text: 'Jika memilih filter tanggal, filter bulan harus diisi juga.',
                        icon: 'warning',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                // Validasi: Jika bulan dipilih tapi tahun tidak, error
                if (filterMonth !== '' && filterYear === '') {
                    Swal.fire({
                        title: 'Filter tidak lengkap!',
                        text: 'Jika memilih filter bulan, filter tahun harus diisi juga.',
                        icon: 'warning',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                // Validasi: Jika tanggal dipilih, bulan *harus* dan tahun *harus* diisi (secara logika sering kasus tanggal pasti ada bulan/tahun)
                if (filterDate !== '' && (filterMonth === '' || filterYear === '')) {
                    Swal.fire({
                        title: 'Filter tidak lengkap!',
                        text: 'Jika memilih filter tanggal, filter bulan dan filter tahun harus diisi.',
                        icon: 'warning',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                // Tahun harus angka yang valid jika diisi
                if (filterYear !== '' && isNaN(filterYear)) {
                    Swal.fire({
                        title: 'Filter Tahun tidak valid!',
                        text: 'Tahun harus berupa angka.',
                        icon: 'warning',
                        confirmButtonText: 'Tutup'
                    });
                    return false;
                }

                // Tanggal tidak boleh di masa depan
                if (filterDate !== '') {
                    const today = new Date();
                    const dateValue = new Date(filterDate);
                    today.setHours(0,0,0,0);
                    if (dateValue > today) {
                        Swal.fire({
                            title: 'Tanggal tidak valid!',
                            text: 'Tanggal tidak boleh di masa depan.',
                            icon: 'warning',
                            confirmButtonText: 'Tutup'
                        });
                        return false;
                    }
                }
                return true;
            }

            function handleExportClick(event) {
                event.preventDefault();

                if (!validateFilterInputs()) return;

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
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Mempersiapkan file...',
                            text: 'Harap tunggu, file Anda sedang dibuat.',
                            icon: 'info',
                            timer: 1800, // lebih pendek supaya UX responsif 
                            timerProgressBar: true,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                            willClose: () => {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: `File laporan ${format.toUpperCase()} sedang diunduh.`,
                                    icon: 'success',
                                    timer: 1100,
                                    showConfirmButton: false
                                });
                                setTimeout(function() {
                                    window.location.href = url;
                                }, 900); // agar swal success muncul dulu sebentar
                            }
                        });
                    }
                });
            }

            const btnExcel = document.getElementById('btnExportExcel');
            const btnPdf = document.getElementById('btnExportPdf');

            if (btnExcel) {
                btnExcel.addEventListener('click', handleExportClick);
            }
            if (btnPdf) {
                btnPdf.addEventListener('click', handleExportClick);
            }
        });
    </script>
@endpush
