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
    .icon-revenue { background-color: #28a745; }
    .icon-gross-profit { background-color: #17a2b8; }
    .icon-net-profit-positive { background-color: #007bff; }
    .icon-net-profit-negative { background-color: #dc3545; }
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

        {{-- Bagian Filter --}}
        <section class="filter-section mb-4">
            <div class="data-card">
                <div class="data-card-body p-4">
                    <form method="GET" action="{{ route('acc.laporan-labarugi') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}">
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6">
                                <label for="end_date" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}">
                            </div>
                            <div class="col-lg-4 col-md-4 col-sm-12 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary w-100" aria-label="Terapkan filter"><i class="bi bi-search me-1"></i><span>Filter</span></button>
                                <a href="{{ route('acc.laporan-labarugi') }}" class="btn btn-outline-secondary" aria-label="Reset filter"><i class="bi bi-arrow-clockwise"></i></a>
                                <button type="button" id="btnExportExcel" class="btn btn-success" data-bs-toggle="tooltip" title="Ekspor ke Excel"><i class="bi bi-file-earmark-excel"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- Stats Section --}}
        <section class="kpi-section mb-4">
            <div class="row g-4">
                {{-- KPI: Total Pendapatan --}}
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card stat-success">
                        <div class="kpi-icon icon-revenue"><i class="bi bi-cash-coin"></i></div>
                        <div class="stat-info">
                            <h3 class="stat-value">Rp {{ number_format($summary['revenue']['value'], 0, ',', '.') }}</h3>
                            <p class="stat-label">Total Pendapatan</p>
                        </div>
                    </div>
                </div>
                {{-- KPI: Laba Kotor --}}
                <div class="col-lg-4 col-md-6">
                    <div class="stat-card stat-info">
                        <div class="kpi-icon icon-gross-profit"><i class="bi bi-graph-up"></i></div>
                        <div class="stat-info">
                            <h3 class="stat-value">Rp {{ number_format($summary['gross_profit']['value'], 0, ',', '.') }}</h3>
                            <p class="stat-label">Laba Kotor</p>
                        </div>
                    </div>
                </div>
                {{-- KPI: Laba Bersih --}}
                <div class="col-lg-4 col-md-12">
                    @php
                        $isProfit = $summary['net_profit']['value'] >= 0;
                    @endphp
                    <div class="stat-card {{ $isProfit ? 'stat-primary' : 'stat-danger' }}">
                        <div class="kpi-icon {{ $isProfit ? 'icon-net-profit-positive' : 'icon-net-profit-negative' }}">
                            <i class="{{ $isProfit ? 'bi bi-wallet2' : 'bi bi-graph-down' }}"></i>
                        </div>
                        <div class="stat-info">
                            <h3 class="stat-value">Rp {{ number_format($summary['net_profit']['value'], 0, ',', '.') }}</h3>
                            <p class="stat-label">Laba Bersih</p>
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
                                        <td><i class="bi bi-arrow-up-circle text-success me-2"></i>{{ $summary['revenue']['label'] }}</td>
                                        <td class="text-end text-success fw-bold">Rp {{ number_format($summary['revenue']['value'], 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-arrow-down-circle text-danger me-2"></i>(-) {{ $summary['hpp']['label'] }}</td>
                                        <td class="text-end text-danger">Rp {{ number_format($summary['hpp']['value'], 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td>{{ $summary['gross_profit']['label'] }}</td>
                                        <td class="text-end fs-5">Rp {{ number_format($summary['gross_profit']['value'], 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

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
                                        <td><i class="bi bi-dash-circle text-muted me-2"></i>{{ $expense['label'] }}</td>
                                        <td class="text-end text-danger">Rp {{ number_format($expense['value'], 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-5"><em>Tidak ada beban operasional pada periode ini.</em></td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                @if(count($summary['expenses']['details']) > 0)
                                <tfoot>
                                    <tr class="total-row">
                                        <td>Total Beban Operasional</td>
                                        <td class="text-end text-danger fs-5">Rp {{ number_format($summary['expenses']['value'], 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    {{-- Card: Perbandingan Kunci (Bar Chart) --}}
                    <div class="data-card">
                        <div class="data-card-header">
                            <h5 class="mb-0">Perbandingan Kunci</h5>
                        </div>
                        <div class="data-card-body p-3">
                            <div style="height: 350px;">
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
                                <span class="fw-bold">Rp {{ number_format($summary['gross_profit']['value'], 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-muted">(-) Total Beban Operasional</span>
                                <span class="fw-bold text-danger">Rp {{ number_format($summary['expenses']['value'], 0, ',', '.') }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="fw-bold fs-5">{{ $summary['net_profit']['label'] }}</span>
                                <span class="fw-bold fs-4 text-{{ $summary['net_profit']['value'] >= 0 ? 'primary' : 'danger' }}">
                                    Rp {{ number_format($summary['net_profit']['value'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Card: Komposisi Beban (Doughnut Chart) --}}
                    @if(count($summary['expenses']['details']) > 0)
                    <div class="data-card mb-4">
                        <div class="data-card-header">
                            <h5 class="mb-0">Komposisi Beban</h5>
                        </div>
                        <div class="data-card-body p-3">
                            <div style="height: 300px;">
                                <canvas id="expensesDoughnutChart"></canvas>
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

        // Tampilkan alert jika ada masalah data pada HPP
        @if (!empty($summary['alert']) && count($summary['alert']) > 0)
            @php
                $alertCount = count($summary['alert']);
                $maxShow = 3; // Batasi maksimal 3 warning yang ditampilkan
            @endphp

            @foreach (array_slice($summary['alert'], 0, $maxShow) as $index => $warning)
                setTimeout(function() {
                    toastr.warning(
                        "{{ addslashes($warning) }}",
                        "⚠️ Peringatan Data {{ $index + 1 }}"
                    );
                }, {{ $index * 300 }}); // Delay 300ms antar toast
            @endforeach

            @if ($alertCount > $maxShow)
                setTimeout(function() {
                    toastr.warning(
                        "Dan {{ $alertCount - $maxShow }} peringatan lainnya. HPP mungkin tidak akurat.",
                        "⚠️ Peringatan Tambahan"
                    );
                }, {{ $maxShow * 300 }});
            @endif

            // Toast ringkasan di akhir
            setTimeout(function() {
                toastr.info(
                    "Ditemukan {{ $alertCount }} masalah data. Silakan lengkapi data bahan baku dan harga pembelian untuk perhitungan yang akurat.",
                    "📊 Ringkasan Validasi Data", {
                        "timeOut": "10000",
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
                    Swal.fire({
                        icon: 'warning',
                        title: 'Filter Belum Diisi',
                        text: 'Silakan tentukan periode laporan terlebih dahulu.',
                    });
                    return;
                }

                let baseUrl = "{{ route('acc.laporan-labarugi.download.excel') }}";
                const downloadUrl = `${baseUrl}?start_date=${startDate}&end_date=${endDate}`;
                window.location.href = downloadUrl;
            });
        }

        // --- Data untuk Charts ---
        const revenue = {{ $summary['revenue']['value'] ?? 0 }};
        const hpp = {{ $summary['hpp']['value'] ?? 0 }};
        const totalExpenses = {{ $summary['expenses']['value'] ?? 0 }};
        const grossProfit = {{ $summary['gross_profit']['value'] ?? 0 }};
        const netProfit = {{ $summary['net_profit']['value'] ?? 0 }};

        // --- Chart 1: Bar Chart Perbandingan Kunci ---
        const ctxComparison = document.getElementById('profitLossComparisonChart');
        if (ctxComparison) {
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
                            netProfit >= 0 ? 'rgba(0, 123, 255, 0.8)' : 'rgba(220, 53, 69, 0.8)'
                        ],
                        borderWidth: 0
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
                                label: (context) => 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => 'Rp ' + new Intl.NumberFormat('id-ID', {
                                    notation: 'compact'
                                }).format(value)
                            }
                        }
                    }
                }
            });
        }

        // --- Chart 2: Doughnut Chart untuk Detail Beban ---
        const ctxExpenses = document.getElementById('expensesDoughnutChart');
        if (ctxExpenses && {{ count($summary['expenses']['details']) > 0 ? 'true' : 'false' }}) {
            const expenseLabels = @json(array_column($summary['expenses']['details'], 'label'));
            const expenseValues = @json(array_column($summary['expenses']['details'], 'value'));

            new Chart(ctxExpenses.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: expenseLabels,
                    datasets: [{
                        data: expenseValues,
                        backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#6f42c1', '#6610f2'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const total = context.chart.getDatasetMeta(0).total;
                                    const value = context.parsed;
                                    const percentage = total > 0 ? (value / total * 100).toFixed(1) : 0;
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(value) + ` (${percentage}%)`;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
