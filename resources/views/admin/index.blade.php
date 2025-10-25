@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Dashboard</h1>
                            <p class="page-subtitle">Ringkasan operasional restoran Anda</p>
                        </div>
                    </div>
                    <div class="dashboard-date">
                        Halo, <strong>{{ Auth::user()->name }}</strong>! Selamat datang kembali di dashboard.
                    </div>
                </div>
            </div>

            <!-- Main Stats Grid -->
            <div class="stats-grid stats-grid-4">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">Rp {{ number_format($totalSales ?? 45250000, 0, ',', '.') }}</h3>
                        <p class="stat-label">Total Penjualan Bulan Ini</p>
                        <div class="stat-trend trend-up">
                            <i class="bi bi-arrow-up"></i>
                            <span>
                                {{ $salesGrowth ?? '12.5%' }} dari bulan lalu
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ number_format($totalTransactions ?? 1247) }}</h3>
                        <p class="stat-label">Total Transaksi</p>
                        <div class="stat-trend trend-up">
                            <i class="bi bi-arrow-up"></i>
                            <span>
                                {{ $transactionsGrowth ?? '8.3%' }} dari bulan lalu
                            </span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-warning">
                    <div class="stat-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $totalIngredients ?? 45 }}</h3>
                        <p class="stat-label">Jenis Bahan Baku</p>
                        <div class="stat-trend trend-neutral">
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $pendingPO ?? 8 }}</h3>
                        <p class="stat-label">PO Menunggu</p>
                        <div class="stat-trend trend-warning">
                            <i class="bi bi-exclamation-circle"></i>
                            <span>Perlu review</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts & Analytics Row -->
            <div class="row g-3 mb-3">
                <!-- Sales Chart -->
                <div class="col-lg-8">
                    <div class="data-card">
                        <div class="data-card-header">
                            <div class="data-card-title">
                                <i class="bi bi-graph-up"></i>
                                <span>Tren Penjualan (7 Hari Terakhir)</span>
                            </div>
                            <div class="chart-legend">
                                <span class="legend-item"><span class="legend-dot legend-primary"></span> Penjualan</span>
                                <span class="legend-item"><span class="legend-dot legend-success"></span> Target</span>
                            </div>
                        </div>
                        <div class="data-card-body chart-container">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Products -->
                <div class="col-lg-4">
                    <div class="data-card">
                        <div class="data-card-header">
                            <div class="data-card-title">
                                <i class="bi bi-star-fill"></i>
                                <span>Menu Terlaris</span>
                            </div>
                        </div>
                        <div class="data-card-body">
                            <div class="top-products-list">
                                @forelse(($topProducts ?? []) as $i => $product)
                                    <div class="top-product-item">
                                        <div class="product-rank {{ $i < 3 ? 'rank-' . ($i + 1) : '' }}">{{ $i + 1 }}
                                        </div>
                                        <div class="product-info">
                                            <div class="product-name">{{ $product['name'] }}</div>
                                            <div class="product-sales">{{ number_format($product['sales']) }}
                                                {{ $product['unit'] }}</div>
                                        </div>
                                        <div class="product-revenue">Rp
                                            {{ number_format($product['revenue'], 0, ',', '.') }}</div>
                                    </div>
                                @empty
                                    <div>Tidak ada data menu terlaris.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Stock Alerts & Recent Activities -->
            <div class="row g-3">
                <!-- Stock Alerts -->
                <div class="col-lg-6">
                    <div class="data-card">
                        <div class="data-card-header">
                            <div class="data-card-title">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span>Peringatan Stok</span>
                            </div>
                            <span class="badge-alert">
                                {{ $stockAlertsCount ?? 0 }} item
                            </span>
                        </div>
                        <div class="data-card-body">
                            <div class="alert-list">
                                @forelse(($stockAlerts ?? []) as $alert)
                                    @php
                                        $isCritical = $alert['critical'];
                                        $isAman = isset($alert['desc']) && strtolower($alert['desc']) === 'stok aman';
                                    @endphp
                                    <div
                                        class="alert-item 
                                        {{ $isCritical ? 'alert-danger-item' : ($isAman ? 'alert-success-item' : 'alert-warning-item') }}">
                                        <div class="alert-icon">
                                            <i
                                                class="bi 
                                                {{ $isCritical
                                                    ? 'bi-exclamation-circle-fill'
                                                    : ($isAman
                                                        ? 'bi-check-circle-fill'
                                                        : 'bi-exclamation-triangle-fill') }}"></i>
                                        </div>
                                        <div class="alert-content">
                                            <div class="alert-title">{{ $alert['name'] }}</div>
                                            <div class="alert-desc">{{ $alert['desc'] }}</div>
                                        </div>
                                        <div
                                            class="alert-stock 
                                            {{ $isCritical ? 'stock-critical' : ($isAman ? 'stock-safe' : 'stock-low') }}">
                                            {{ $alert['qty'] }}
                                        </div>
                                    </div>
                                @empty
                                    <div>Tidak ada peringatan stok.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Purchase Orders -->
                <div class="col-lg-6">
                    <div class="data-card">
                        <div class="data-card-header">
                            <div class="data-card-title">
                                <i class="bi bi-clock-history"></i>
                                <span>Purchase Order Terbaru</span>
                            </div>
                        </div>
                        <div class="data-card-body">
                            <div class="activity-list">
                                @forelse(($latestPurchaseOrders ?? []) as $po)
                                    <div class="activity-item">
                                        <div class="activity-icon status-{{ strtolower($po['status']) }}">
                                            @if (trim(strtolower($po['status'])) == 'diproses')
                                                <i class="bi bi-clock"></i>
                                            @elseif(trim(strtolower($po['status'])) == 'diterima')
                                                <i class="bi bi-check2-square"></i>
                                            @else
                                                <i class="bi bi-question-circle"></i>
                                            @endif
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-title">{{ $po['code'] }} - {{ $po['vendor'] }}</div>
                                            <div class="activity-desc">{{ $po['desc'] }}</div>
                                            <div class="activity-time">{{ $po['time'] }}</div>
                                        </div>
                                        <div class="activity-status">
                                            <span
                                                class="badge-status status-{{ strtolower($po['status']) }}">{{ $po['status'] }}</span>
                                            <div class="activity-amount">Rp
                                                {{ number_format($po['amount'], 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div>Tidak ada PO terbaru.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Footer -->
            <div class="quick-stats-footer mt-3">
                <div class="quick-stat-item">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <div class="quick-stat-info">
                        <div class="quick-stat-value">
                            Rp {{ number_format($energyCost ?? 125000, 0, ',', '.') }}
                        </div>
                        <div class="quick-stat-label">Biaya Energi/Bulan</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <i class="bi bi-people-fill"></i>
                    <div class="quick-stat-info">
                        <div class="quick-stat-value">{{ $totalSuppliers ?? 12 }}</div>
                        <div class="quick-stat-label">Total Supplier</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <i class="bi bi-graph-up-arrow"></i>
                    <div class="quick-stat-info">
                        <div class="quick-stat-value">Rp {{ number_format($averageTransaction ?? 36300, 0, ',', '.') }}
                        </div>
                        <div class="quick-stat-label">Rata-rata Transaksi</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <i class="bi bi-clock-fill"></i>
                    <div class="quick-stat-info">
                        <div class="quick-stat-value">{{ $busiestHour ?? '18:30' }}</div>
                        <div class="quick-stat-label">Jam Tersibuk</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Sales Chart
            const ctx = document.getElementById('salesChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: @json($salesChartLabels),
                        datasets: [{
                            label: 'Penjualan',
                            data: @json($salesChartData),
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#667eea',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }, {
                            label: 'Target',
                            data: @json($salesChartTarget),
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.05)',
                            tension: 0.4,
                            fill: false,
                            borderWidth: 2,
                            borderDash: [5, 5],
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#28a745',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
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
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                borderWidth: 1,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + (value / 1000000) + 'jt';
                                    },
                                    color: '#6c757d'
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#6c757d'
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            }
        });

        qz.security.setCertificatePromise(function(resolve, reject) {
            resolve("-----BEGIN CERTIFICATE-----\nMIIC...isi sertifikat bawaan...\n-----END CERTIFICATE-----");
        });

        qz.security.setSignaturePromise(function(toSign) {
            return function(resolve, reject) {
                // Boleh dikosongkan dulu kalau belum pakai security
                resolve();
            };
        });

        qz.websocket.connect().then(() => {
            console.log("✅ QZ Tray connected!");
        }).catch(err => {
            console.error("❌ Gagal konek ke QZ Tray:", err);
        });

        qz.printers.getList().then(printers => {
            console.log("Printers ditemukan:", printers);
        }).catch(err => console.error(err));
    </script>
@endpush
