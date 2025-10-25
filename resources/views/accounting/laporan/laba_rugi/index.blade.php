@extends('app')

@push('style')
    <style>
        .kpi-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 50%;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: #fff;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .icon-revenue {
            background-color: #28a745;
        }

        .icon-gross-profit {
            background-color: #17a2b8;
        }

        .icon-net-profit-positive {
            background-color: #007bff;
        }

        .icon-net-profit-negative {
            background-color: #dc3545;
        }

        .table-breakdown {
            font-size: 1rem;
        }

        .table-breakdown td {
            padding: 0.9rem 1rem;
        }

        .table-breakdown .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        /* ✨ NEW: Metric badges */
        .metric-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.37rem 1.1rem 0.37rem 1rem;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.97rem;
            margin: 0.2rem 0.35rem 0.2rem 0;
            box-shadow: 0 2px 8px rgba(44,62,80, 0.09);
            letter-spacing: 0.01em;
            transition: box-shadow 0.15s, background-color 0.15s;
            border: 1px solid rgba(200,200,200,0.14);
        }
        .metric-badge:hover,
        .metric-badge:focus {
            box-shadow: 0 6px 16px rgba(44,62,80, 0.13);
            background-color: #f3f5fa;
        }
        .metric-badge i {
            margin-right: 0.6rem;
            font-size: 1.15em;
            opacity: 0.8;
        }

        /* ✨ NEW: Alert summary badges */
        .alert-summary {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .alert-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ✨ NEW: HPP Breakdown table styling */
        .hpp-breakdown-table {
            font-size: 0.95rem;
        }

        .hpp-breakdown-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .hpp-breakdown-table td {
            padding: 0.75rem;
        }

        .category-row {
            background-color: #f8f9fa;
        }

        /* ✨ NEW: Chart loader */
        .chart-loader {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            color: #6c757d;
        }

        .chart-loader .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        /* ✨ NEW: Expense icon styling */
        .expense-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            {{-- Header Halaman --}}
            <div class="page-header">
                <div class="page-title-wrapper">
                    <div class="page-icon"><i class="bi bi-graph-up-arrow"></i></div>
                    <div>
                        <h1 class="page-title">Laporan Laba Rugi</h1>
                        <p class="page-subtitle">{{ $reportTitle }}</p>
                    </div>
                </div>
            </div>

            {{-- ✨ NEW: Alert Summary Section --}}
            @if (!empty($summary['alert']) && count($summary['alert']) > 0)
                <section class="alert-summary-section mb-4">
                    <div class="data-card border-warning">
                        <div class="data-card-body p-3">
                            <div class="alert-summary">
                                <div class="alert-badge bg-danger text-white">
                                    <i class="bi bi-x-circle-fill"></i>
                                    <span>{{ $summary['alert_summary']['critical'] }} Critical</span>
                                </div>
                                <div class="alert-badge bg-warning text-dark">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <span>{{ $summary['alert_summary']['warning'] }} Warning</span>
                                </div>
                                <div class="alert-badge bg-info text-white">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <span>{{ $summary['alert_summary']['info'] }} Info</span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-lightbulb"></i>
                                Ditemukan {{ $summary['alert_summary']['total'] }} masalah data.
                                Klik tombol thumbs down jika ada yang perlu diperbaiki.
                            </small>
                        </div>
                    </div>
                </section>
            @endif

            {{-- Bagian Filter --}}
            <section class="filter-section mb-4">
                <div class="data-card">
                    <div class="data-card-body p-4">
                        <form method="GET" action="{{ route('acc.laporan-labarugi') }}">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <label for="start_date" class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="start_date" id="start_date"
                                        value="{{ $filters['start_date'] ?? '' }}">
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <label for="end_date" class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="end_date" id="end_date"
                                        value="{{ $filters['end_date'] ?? '' }}">
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-search me-1"></i><span>Filter</span>
                                    </button>
                                    <a href="{{ route('acc.laporan-labarugi') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </a>
                                    <button type="button" id="btnExportExcel"
                                        data-url="{{ route('acc.laporan-labarugi.download.excel', request()->query()) }}"
                                        class="btn btn-success" data-bs-toggle="tooltip" title="Ekspor ke Excel">
                                        <i class="bi bi-file-earmark-excel"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            {{-- Stats Section --}}
            <section class="kpi-section mb-4">
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="stat-card stat-success">
                            <div class="kpi-icon icon-revenue"><i class="bi bi-cash-coin"></i></div>
                            <div class="stat-info">
                                <h3 class="stat-value">Rp {{ number_format($summary['revenue']['value'], 0, ',', '.') }}
                                </h3>
                                <p class="stat-label">Total Pendapatan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="stat-card stat-info">
                            <div class="kpi-icon icon-gross-profit"><i class="bi bi-graph-up"></i></div>
                            <div class="stat-info">
                                <h3 class="stat-value">Rp
                                    {{ number_format($summary['gross_profit']['value'], 0, ',', '.') }}</h3>
                                <p class="stat-label">Laba Kotor</p>
                                {{-- ✨ NEW: Gross margin percentage --}}
                                <span class="metric-badge bg-info text-white">
                                    <i class="bi bi-percent"></i>{{ $summary['metrics']['gross_margin_percentage'] }}%
                                    Margin
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        @php $isProfit = $summary['net_profit']['value'] >= 0; @endphp
                        <div class="stat-card {{ $isProfit ? 'stat-primary' : 'stat-danger' }}">
                            <div
                                class="kpi-icon {{ $isProfit ? 'icon-net-profit-positive' : 'icon-net-profit-negative' }}">
                                <i class="{{ $isProfit ? 'bi bi-wallet2' : 'bi bi-graph-down' }}"></i>
                            </div>
                            <div class="stat-info">
                                <h3 class="stat-value">Rp {{ number_format($summary['net_profit']['value'], 0, ',', '.') }}
                                </h3>
                                <p class="stat-label">Laba Bersih</p>
                                {{-- ✨ NEW: Net margin percentage --}}
                                <span class="metric-badge {{ $isProfit ? 'bg-primary' : 'bg-danger' }} text-white">
                                    <i class="bi bi-percent"></i>{{ $summary['metrics']['net_margin_percentage'] }}% Margin
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Main Report & Charts Section --}}
            <section class="main-report-section">
                <div class="row g-4">
                    {{-- Kolom Kiri: Detail Laporan --}}
                    <div class="col-lg-7">
                        {{-- Card: Perhitungan Laba Kotor --}}
                        <div class="data-card mb-4">
                            <div class="data-card-header">
                                <h5 class="mb-0">Perhitungan Laba Kotor</h5>
                            </div>
                            <div class="data-card-body">
                                <table class="table table-borderless table-breakdown mb-0">
                                    <tbody>
                                        <tr>
                                            <td><i
                                                    class="bi bi-arrow-up-circle text-success me-2"></i>{{ $summary['revenue']['label'] }}
                                            </td>
                                            <td class="text-end text-success fw-bold">Rp
                                                {{ number_format($summary['revenue']['value'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td><i class="bi bi-arrow-down-circle text-danger me-2"></i>(-)
                                                {{ $summary['hpp']['label'] }}</td>
                                            <td class="text-end text-danger">Rp
                                                {{ number_format($summary['hpp']['value'], 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="total-row">
                                            <td>{{ $summary['gross_profit']['label'] }}</td>
                                            <td class="text-end fs-5">Rp
                                                {{ number_format($summary['gross_profit']['value'], 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- ✨ NEW: HPP Breakdown per Category --}}
                        @if (!empty($summary['hpp']['breakdown']))
                            <div class="data-card mb-4">
                                <div class="data-card-header">
                                    <h5 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>HPP per Kategori Menu</h5>
                                </div>
                                <div class="data-card-body p-3">
                                    <table class="table hpp-breakdown-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Kategori</th>
                                                <th class="text-center">Jumlah Item</th>
                                                <th class="text-center">Total Qty</th>
                                                <th class="text-end">Total HPP</th>
                                                <th class="text-end">% dari Total HPP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($summary['hpp']['breakdown'] as $breakdown)
                                                <tr>
                                                    <td class="fw-bold">{{ $breakdown['category'] }}</td>
                                                    <td class="text-center">{{ $breakdown['items_count'] }}</td>
                                                    <td class="text-center">{{ $breakdown['total_quantity'] }}</td>
                                                    <td class="text-end">Rp
                                                        {{ number_format($breakdown['total_hpp'], 0, ',', '.') }}</td>
                                                    <td class="text-end">
                                                        <span class="badge bg-secondary">
                                                            {{ $summary['hpp']['value'] > 0 ? number_format(($breakdown['total_hpp'] / $summary['hpp']['value']) * 100, 1) : 0 }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- Card: Rincian Beban --}}
                        <div class="data-card mb-4">
                            <div class="data-card-header">
                                <h5 class="mb-0">Rincian Beban Operasional</h5>
                            </div>
                            <div class="data-card-body">
                                <table class="table table-borderless table-breakdown mb-0">
                                    <tbody>
                                        @forelse($summary['expenses']['details'] as $expense)
                                            <tr>
                                                <td>
                                                    {{-- ✨ NEW: Expense icons --}}
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="expense-icon bg-{{ $expense['color'] ?? 'secondary' }} bg-opacity-25 text-{{ $expense['color'] ?? 'secondary' }}">
                                                            <i class="{{ $expense['icon'] ?? 'bi-dash-circle' }}"></i>
                                                        </div>
                                                        <span>{{ $expense['label'] }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-end text-danger fs-5">Rp
                                                    {{ number_format($expense['value'] ?? 0, 0, ',', '.') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">Tidak ada data beban
                                                    operasional.</td>
                                            </tr>
                                        @endforelse
                                </table>
                            </div>
                        </div>

                        {{-- Card: Perbandingan Kunci (Bar Chart) --}}
                        <div class="data-card">
                            <div class="data-card-header">
                                <h5 class="mb-0">Perbandingan Kunci</h5>
                            </div>
                            <div class="data-card-body p-3">
                                {{-- ✨ NEW: Chart loader --}}
                                <div id="chartLoader1" class="chart-loader">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-3">Memuat grafik...</p>
                                </div>
                                <div style="height: 350px; display: none;" id="chartContainer1">
                                    <canvas id="profitLossComparisonChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Visualisasi & Kesimpulan --}}
                    <div class="col-lg-5">
                        {{-- Card: Final Summary --}}
                        <div class="data-card mb-4">
                            <div class="data-card-header">
                                <h5 class="mb-0">Kesimpulan Akhir</h5>
                            </div>
                            <div class="data-card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">{{ $summary['gross_profit']['label'] }}</span>
                                    <span class="fw-bold">Rp
                                        {{ number_format($summary['gross_profit']['value'], 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">(-) Total Beban Operasional</span>
                                    <span class="fw-bold text-danger">Rp
                                        {{ number_format($summary['expenses']['value'], 0, ',', '.') }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <span class="fw-bold fs-5">{{ $summary['net_profit']['label'] }}</span>
                                    <span
                                        class="fw-bold fs-4 text-{{ $summary['net_profit']['value'] >= 0 ? 'primary' : 'danger' }}">
                                        Rp {{ number_format($summary['net_profit']['value'], 0, ',', '.') }}
                                    </span>
                                </div>

                                {{-- ✨ NEW: Additional metrics display --}}
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="text-muted mb-3">Rasio Keuangan</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">HPP Ratio</small>
                                        <small class="fw-bold">{{ $summary['metrics']['hpp_to_revenue_ratio'] }}%</small>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted">Expense Ratio</small>
                                        <small
                                            class="fw-bold">{{ $summary['metrics']['expense_to_revenue_ratio'] }}%</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted">Net Margin</small>
                                        <small
                                            class="fw-bold text-{{ $summary['metrics']['net_margin_percentage'] >= 0 ? 'success' : 'danger' }}">
                                            {{ $summary['metrics']['net_margin_percentage'] }}%
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Komposisi Beban (Doughnut Chart) --}}
                        @if (count($summary['expenses']['details']) > 0)
                            <div class="data-card mb-4">
                                <div class="data-card-header">
                                    <h5 class="mb-0">Komposisi Beban</h5>
                                </div>
                                <div class="data-card-body p-3">
                                    {{-- ✨ NEW: Chart loader --}}
                                    <div id="chartLoader2" class="chart-loader" style="min-height: 300px;">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-3">Memuat grafik...</p>
                                    </div>
                                    <div style="height: 300px; display: none;" id="chartContainer2">
                                        <canvas id="expensesDoughnutChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- ✨ NEW: HPP Breakdown Chart (if available) --}}
                        @if (!empty($summary['hpp']['breakdown']))
                            <div class="data-card">
                                <div class="data-card-header">
                                    <h5 class="mb-0">Distribusi HPP per Kategori</h5>
                                </div>
                                <div class="data-card-body p-3">
                                    <div id="chartLoader3" class="chart-loader" style="min-height: 300px;">
                                        <div class="spinner-border text-primary" role="status"></div>
                                        <p class="mt-3">Memuat grafik...</p>
                                    </div>
                                    <div style="height: 300px; display: none;" id="chartContainer3">
                                        <canvas id="hppBreakdownChart"></canvas>
                                    </div>
                                </div>
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
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "10000",
                "extendedTimeOut": "0",
                "tapToDismiss": false,
                "preventDuplicates": true
            };

            // ✨ IMPROVED: Better alert notification system
            @if (!empty($summary['alert']) && count($summary['alert']) > 0)
                @php
                    $alertCount = count($summary['alert']);
                    $maxShow = 3;
                @endphp

                @foreach (array_slice($summary['alert'], 0, $maxShow) as $index => $warning)
                    setTimeout(function() {
                        let type = 'warning';
                        let icon = '⚠️';

                        @if (str_contains($warning, '❌'))
                            type = 'error';
                            icon = '❌';
                        @elseif (str_contains($warning, '💰'))
                            type = 'info';
                            icon = '💰';
                        @endif

                        toastr[type](
                            "{{ addslashes($warning) }}",
                            icon + " Data Issue {{ $index + 1 }}"
                        );
                    }, {{ $index * 300 }});
                @endforeach

                @if ($alertCount > $maxShow)
                    setTimeout(function() {
                        toastr.warning(
                            "Dan {{ $alertCount - $maxShow }} masalah lainnya. Periksa data untuk perhitungan akurat.",
                            "⚠️ {{ $alertCount - $maxShow }} Masalah Tambahan"
                        );
                    }, {{ $maxShow * 300 }});
                @endif

                setTimeout(function() {
                    toastr.info(
                        "Total: {{ $alertCount }} masalah. Critical: {{ $summary['alert_summary']['critical'] }}, Warning: {{ $summary['alert_summary']['warning'] }}, Info: {{ $summary['alert_summary']['info'] }}",
                        "📊 Ringkasan Validasi Data", {
                            "timeOut": "12000",
                            "extendedTimeOut": "3000"
                        }
                    );
                }, {{ ($maxShow + 1) * 300 }});
            @endif

            // --- Logika Ekspor Excel ---
            const btnExcel = document.getElementById('btnExportExcel');
            if (btnExcel) {
                btnExcel.addEventListener('click', function(event) {
                    event.preventDefault();

                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;

                    if (!startDate || !endDate) {
                        Swal.fire('Filter Belum Diisi',
                            'Silakan tentukan periode laporan (tanggal mulai dan akhir).', 'warning');
                        return;
                    }

                    const urlObj = new URL(this.dataset.url, window.location.origin);
                    urlObj.searchParams.set('start_date', startDate);
                    urlObj.searchParams.set('end_date', endDate);
                    const url = urlObj.toString();

                    const btn = this;
                    const originalHtml = btn.innerHTML;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i>';
                    if (typeof showLoading === 'function') showLoading('Mengambil data laporan...');

                    fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => {
                                    throw new Error(err.message || 'Gagal mengambil data');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (typeof hideLoading === 'function') hideLoading();

                            if (data.status === 'success' && Array.isArray(data.salesData) && data
                                .salesData.length > 0) {

                                // ✨ Create worksheet from JSON
                                const ws = XLSX.utils.json_to_sheet(data.salesData);

                                // ✨ STYLING: Apply cell styles
                                const range = XLSX.utils.decode_range(ws['!ref']);

                                for (let R = range.s.r; R <= range.e.r; ++R) {
                                    for (let C = range.s.c; C <= range.e.c; ++C) {
                                        const cellAddress = XLSX.utils.encode_cell({
                                            r: R,
                                            c: C
                                        });
                                        if (!ws[cellAddress]) continue;

                                        const cell = ws[cellAddress];
                                        const cellValue = cell.v ? cell.v.toString() : '';

                                        // Initialize cell style
                                        if (!cell.s) cell.s = {};

                                        // 🎨 HEADER STYLING (Row 0)
                                        if (R === 0) {
                                            cell.s = {
                                                font: {
                                                    bold: true,
                                                    sz: 14,
                                                    color: {
                                                        rgb: "FFFFFF"
                                                    }
                                                },
                                                fill: {
                                                    fgColor: {
                                                        rgb: "1F4788"
                                                    }
                                                },
                                                alignment: {
                                                    horizontal: "center",
                                                    vertical: "center",
                                                    wrapText: true
                                                }
                                            };
                                        }

                                        // 🎨 SECTION HEADERS (with ═══ or UPPERCASE titles)
                                        if (cellValue.includes('═══') ||
                                            (cellValue === cellValue.toUpperCase() &&
                                                cellValue.length > 5 &&
                                                !cellValue.includes('TOTAL') &&
                                                C === 0)) {
                                            cell.s = {
                                                font: {
                                                    bold: true,
                                                    sz: 12,
                                                    color: {
                                                        rgb: "FFFFFF"
                                                    }
                                                },
                                                fill: {
                                                    fgColor: {
                                                        rgb: "2E5C8A"
                                                    }
                                                },
                                                alignment: {
                                                    horizontal: "left",
                                                    vertical: "center"
                                                }
                                            };
                                        }

                                        // 🎨 SUB-HEADERS (with ───)
                                        if (cellValue.includes('───')) {
                                            cell.s = {
                                                font: {
                                                    bold: true,
                                                    sz: 11
                                                },
                                                fill: {
                                                    fgColor: {
                                                        rgb: "D9E2F3"
                                                    }
                                                },
                                                alignment: {
                                                    horizontal: "left",
                                                    vertical: "center"
                                                }
                                            };
                                        }

                                        // 🎨 TOTAL ROWS (starts with "TOTAL")
                                        if (cellValue.startsWith('TOTAL') || cellValue.startsWith(
                                                'Total')) {
                                            cell.s = {
                                                font: {
                                                    bold: true,
                                                    sz: 11,
                                                    color: {
                                                        rgb: "1F4788"
                                                    }
                                                },
                                                fill: {
                                                    fgColor: {
                                                        rgb: "E7E6E6"
                                                    }
                                                },
                                                alignment: {
                                                    horizontal: C === 0 ? "left" : "right",
                                                    vertical: "center"
                                                },
                                                border: {
                                                    top: {
                                                        style: "thin",
                                                        color: {
                                                            rgb: "000000"
                                                        }
                                                    },
                                                    bottom: {
                                                        style: "medium",
                                                        color: {
                                                            rgb: "000000"
                                                        }
                                                    }
                                                }
                                            };
                                        }

                                        // 🎨 LABA BERSIH / NET PROFIT
                                        if (cellValue.includes('LABA BERSIH')) {
                                            cell.s = {
                                                font: {
                                                    bold: true,
                                                    sz: 12,
                                                    color: {
                                                        rgb: "FFFFFF"
                                                    }
                                                },
                                                fill: {
                                                    fgColor: {
                                                        rgb: "28A745"
                                                    }
                                                },
                                                alignment: {
                                                    horizontal: "left",
                                                    vertical: "center"
                                                }
                                            };
                                        }

                                        // 🎨 ALERT SECTION
                                        if (cellValue.includes('PERINGATAN') || cellValue.includes(
                                                'CATATAN')) {
                                            cell.s = {
                                                font: {
                                                    bold: true,
                                                    sz: 11,
                                                    color: {
                                                        rgb: "FFFFFF"
                                                    }
                                                },
                                                fill: {
                                                    fgColor: {
                                                        rgb: "DC3545"
                                                    }
                                                },
                                                alignment: {
                                                    horizontal: "left",
                                                    vertical: "center"
                                                }
                                            };
                                        }

                                        // 🎨 DETAIL ROWS (indented with spaces or bullets)
                                        if ((cellValue.startsWith('  ') || cellValue.includes('•')) &&
                                            C === 0) {
                                            cell.s = {
                                                font: {
                                                    sz: 10,
                                                    italic: cellValue.includes('•')
                                                },
                                                fill: {
                                                    fgColor: {
                                                        rgb: "F8F9FA"
                                                    }
                                                },
                                                alignment: {
                                                    horizontal: "left",
                                                    vertical: "center",
                                                    wrapText: true
                                                }
                                            };
                                        }

                                        // 💰 NUMBER FORMATTING
                                        if (C >= 2) { // Debit & Kredit columns
                                            const numValue = parseFloat(cellValue.replace(/[^0-9.-]/g,
                                                ''));
                                            if (!isNaN(numValue) && numValue !== 0) {
                                                cell.t = 'n';
                                                cell.v = numValue;
                                                cell.z =
                                                '#,##0'; // Number format with thousand separator

                                                if (!cell.s) cell.s = {};
                                                cell.s.alignment = {
                                                    horizontal: "right",
                                                    vertical: "center"
                                                };

                                                // Negative numbers in red
                                                if (numValue < 0) {
                                                    cell.s.font = {
                                                        color: {
                                                            rgb: "DC3545"
                                                        }
                                                    };
                                                }
                                            }
                                        }

                                        // 📊 PERCENTAGE FORMATTING (for metrics)
                                        if (cellValue.includes('%') && C === 3) {
                                            const percentValue = parseFloat(cellValue.replace('%', ''));
                                            if (!isNaN(percentValue)) {
                                                cell.t = 'n';
                                                cell.v = percentValue / 100;
                                                cell.z = '0.00%';

                                                if (!cell.s) cell.s = {};
                                                cell.s.alignment = {
                                                    horizontal: "right",
                                                    vertical: "center"
                                                };
                                            }
                                        }

                                        // 🎨 DEFAULT CELL STYLING
                                        if (!cell.s.alignment) {
                                            cell.s.alignment = {
                                                vertical: "center",
                                                wrapText: C ===
                                                    1 // Wrap text for Description column
                                            };
                                        }
                                    }
                                }

                                // ✨ COLUMN WIDTHS
                                ws['!cols'] = [{
                                        wch: 35
                                    }, // Akun
                                    {
                                        wch: 50
                                    }, // Deskripsi
                                    {
                                        wch: 18
                                    }, // Debit
                                    {
                                        wch: 18
                                    } // Kredit
                                ];

                                // ✨ ROW HEIGHTS
                                if (!ws['!rows']) ws['!rows'] = [];
                                ws['!rows'][0] = {
                                    hpt: 25
                                }; // Header row height

                                // Set height for rows with wrapped text
                                for (let R = range.s.r; R <= range.e.r; ++R) {
                                    const cellB = ws[XLSX.utils.encode_cell({
                                        r: R,
                                        c: 1
                                    })];
                                    if (cellB && cellB.v && cellB.v.toString().length > 50) {
                                        if (!ws['!rows'][R]) ws['!rows'][R] = {};
                                        ws['!rows'][R].hpt = 30; // Taller rows for long descriptions
                                    }
                                }

                                // ✨ FREEZE PANES (Freeze header row)
                                ws['!freeze'] = {
                                    xSplit: 0,
                                    ySplit: 1
                                };

                                // ✨ AUTO-FILTER (on header row)
                                ws['!autofilter'] = {
                                    ref: XLSX.utils.encode_range(range)
                                };

                                // ✨ CREATE WORKBOOK
                                const wb = XLSX.utils.book_new();
                                XLSX.utils.book_append_sheet(wb, ws, "Laporan Laba Rugi");

                                // ✨ WORKBOOK PROPERTIES
                                wb.Props = {
                                    Title: data.reportTitle || "Laporan Laba Rugi",
                                    Subject: "Financial Report",
                                    Author: "Restaurant POS System",
                                    CreatedDate: new Date()
                                };

                                // ✨ WRITE FILE with cellStyles option
                                XLSX.writeFile(wb, data.fileName || "laporan-laba-rugi.xlsx", {
                                    cellStyles: true,
                                    bookType: 'xlsx'
                                });

                                // ✨ SUCCESS NOTIFICATION
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Export Berhasil!',
                                    html: `
                            <p>File Excel telah berhasil diunduh:</p>
                            <strong>${data.fileName}</strong>
                            ${data.metadata ? `
                                    <div class="mt-3 text-start" style="font-size: 0.9em;">
                                        <p class="mb-1"><strong>Ringkasan:</strong></p>
                                        <ul style="list-style: none; padding-left: 0;">
                                            <li>💰 Total Pendapatan: Rp ${new Intl.NumberFormat('id-ID').format(data.metadata.total_revenue)}</li>
                                            <li>📊 Laba Bersih: Rp ${new Intl.NumberFormat('id-ID').format(data.metadata.net_profit)}</li>
                                            <li>📈 Net Margin: ${data.metadata.metrics.net_margin_percentage}%</li>
                                        </ul>
                                    </div>
                                ` : ''}
                        `,
                                    timer: 5000,
                                    showConfirmButton: true
                                });

                            } else if (data.salesData && data.salesData.length === 0) {
                                Swal.fire('Data Kosong',
                                    'Tidak ada data untuk diekspor pada periode ini.', 'info');
                            } else {
                                throw new Error(data.message || 'Format data tidak sesuai');
                            }
                        })
                        .catch(error => {
                            if (typeof hideLoading === 'function') hideLoading();
                            console.error('Export Excel Error:', error);
                            Swal.fire('Gagal Ekspor', error.message, 'error');
                        })
                        .finally(() => {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        });
                });
            }

            // --- Data untuk Charts ---
            const revenue = {{ $summary['revenue']['value'] ?? 0 }};
            const hpp = {{ $summary['hpp']['value'] ?? 0 }};
            const totalExpenses = {{ $summary['expenses']['value'] ?? 0 }};
            const grossProfit = {{ $summary['gross_profit']['value'] ?? 0 }};
            const netProfit = {{ $summary['net_profit']['value'] ?? 0 }};

            // ✨ IMPROVED: Helper function untuk hide loader & show chart
            function showChart(loaderId, containerId) {
                document.getElementById(loaderId).style.display = 'none';
                document.getElementById(containerId).style.display = 'block';
            }

            // --- Chart 1: Bar Chart Perbandingan Kunci ---
            const ctxComparison = document.getElementById('profitLossComparisonChart');
            if (ctxComparison) {
                setTimeout(() => {
                    new Chart(ctxComparison.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: ['Pendapatan', 'HPP', 'Beban', 'Laba Kotor', 'Laba Bersih'],
                            datasets: [{
                                label: 'Jumlah (Rp)',
                                data: [revenue, hpp, totalExpenses, grossProfit, netProfit],
                                backgroundColor: [
                                    'rgba(40, 167, 69, 0.8)',
                                    'rgba(255, 193, 7, 0.8)',
                                    'rgba(220, 53, 69, 0.8)',
                                    'rgba(23, 162, 184, 0.8)',
                                    netProfit >= 0 ? 'rgba(0, 123, 255, 0.8)' :
                                    'rgba(220, 53, 69, 0.8)'
                                ],
                                borderWidth: 0,
                                borderRadius: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (context) => 'Rp ' + new Intl.NumberFormat('id-ID')
                                            .format(context.parsed.y)
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (value) => 'Rp ' + new Intl.NumberFormat(
                                            'id-ID', {
                                                notation: 'compact'
                                            }).format(value)
                                    }
                                }
                            }
                        }
                    });
                    showChart('chartLoader1', 'chartContainer1');
                }, 300);
            }

            // --- Chart 2: Doughnut Chart untuk Detail Beban ---
            const ctxExpenses = document.getElementById('expensesDoughnutChart');
            if (ctxExpenses && {{ count($summary['expenses']['details']) > 0 ? 'true' : 'false' }}) {
                const expenseLabels = @json(array_column($summary['expenses']['details'], 'label'));
                const expenseValues = @json(array_column($summary['expenses']['details'], 'value'));

                setTimeout(() => {
                    new Chart(ctxExpenses.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: expenseLabels,
                            datasets: [{
                                data: expenseValues,
                                backgroundColor: ['#dc3545', '#fd7e14', '#ffc107',
                                    '#6f42c1', '#6610f2', '#20c997'
                                ],
                                hoverOffset: 8,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) label += ': ';
                                            const total = context.chart.getDatasetMeta(0).total;
                                            const value = context.parsed;
                                            const percentage = total > 0 ? (value / total * 100)
                                                .toFixed(1) : 0;
                                            label += 'Rp ' + new Intl.NumberFormat('id-ID')
                                                .format(value) + ` (${percentage}%)`;
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    showChart('chartLoader2', 'chartContainer2');
                }, 600);
            }

            // ✨ NEW: Chart 3 - HPP Breakdown Chart
            const ctxHppBreakdown = document.getElementById('hppBreakdownChart');
            if (ctxHppBreakdown && {{ !empty($summary['hpp']['breakdown']) ? 'true' : 'false' }}) {
                const hppBreakdownData = @json($summary['hpp']['breakdown']);
                const hppLabels = hppBreakdownData.map(item => item.category);
                const hppValues = hppBreakdownData.map(item => item.total_hpp);

                setTimeout(() => {
                    new Chart(ctxHppBreakdown.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: hppLabels,
                            datasets: [{
                                data: hppValues,
                                backgroundColor: [
                                    '#007bff', '#28a745', '#ffc107', '#dc3545',
                                    '#17a2b8', '#6610f2', '#fd7e14', '#20c997'
                                ],
                                hoverOffset: 8,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 12,
                                        font: {
                                            size: 11
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) label += ': ';
                                            const total = hppValues.reduce((a, b) => a + b, 0);
                                            const value = context.parsed;
                                            const percentage = total > 0 ? (value / total * 100)
                                                .toFixed(1) : 0;
                                            label += 'Rp ' + new Intl.NumberFormat('id-ID')
                                                .format(value) + ` (${percentage}%)`;
                                            return label;
                                        },
                                        afterLabel: function(context) {
                                            const index = context.dataIndex;
                                            const item = hppBreakdownData[index];
                                            return `Items: ${item.items_count} | Qty: ${item.total_quantity}`;
                                        }
                                    }
                                }
                            }
                        }
                    });
                    showChart('chartLoader3', 'chartContainer3');
                }, 900);
            }
        });
    </script>
@endpush
