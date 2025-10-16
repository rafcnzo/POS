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
                        <i class="bi bi-calendar-event"></i>
                        <span id="currentDate"></span>
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
                        <h3 class="stat-value">Rp 45.250.000</h3>
                        <p class="stat-label">Total Penjualan Bulan Ini</p>
                        <div class="stat-trend trend-up">
                            <i class="bi bi-arrow-up"></i>
                            <span>12.5% dari bulan lalu</span>
                        </div>
                    </div>
                </div>

                <div class="stat-card stat-success">
                    <div class="stat-icon">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">1,247</h3>
                        <p class="stat-label">Total Transaksi</p>
                        <div class="stat-trend trend-up">
                            <i class="bi bi-arrow-up"></i>
                            <span>8.3% dari bulan lalu</span>
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
                            <i class="bi bi-dash"></i>
                            <span>Stabil</span>
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
                                <div class="top-product-item">
                                    <div class="product-rank rank-1">1</div>
                                    <div class="product-info">
                                        <div class="product-name">Nasi Goreng Special</div>
                                        <div class="product-sales">285 porsi</div>
                                    </div>
                                    <div class="product-revenue">Rp 7.125.000</div>
                                </div>
                                <div class="top-product-item">
                                    <div class="product-rank rank-2">2</div>
                                    <div class="product-info">
                                        <div class="product-name">Ayam Bakar Madu</div>
                                        <div class="product-sales">234 porsi</div>
                                    </div>
                                    <div class="product-revenue">Rp 7.020.000</div>
                                </div>
                                <div class="top-product-item">
                                    <div class="product-rank rank-3">3</div>
                                    <div class="product-info">
                                        <div class="product-name">Soto Ayam</div>
                                        <div class="product-sales">198 porsi</div>
                                    </div>
                                    <div class="product-revenue">Rp 3.960.000</div>
                                </div>
                                <div class="top-product-item">
                                    <div class="product-rank">4</div>
                                    <div class="product-info">
                                        <div class="product-name">Es Teh Manis</div>
                                        <div class="product-sales">567 gelas</div>
                                    </div>
                                    <div class="product-revenue">Rp 2.835.000</div>
                                </div>
                                <div class="top-product-item">
                                    <div class="product-rank">5</div>
                                    <div class="product-info">
                                        <div class="product-name">Gado-Gado</div>
                                        <div class="product-sales">156 porsi</div>
                                    </div>
                                    <div class="product-revenue">Rp 2.340.000</div>
                                </div>
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
                            <span class="badge-alert">5 item</span>
                        </div>
                        <div class="data-card-body">
                            <div class="alert-list">
                                <div class="alert-item alert-danger-item">
                                    <div class="alert-icon">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                    </div>
                                    <div class="alert-content">
                                        <div class="alert-title">Tepung Terigu</div>
                                        <div class="alert-desc">Stok tinggal 5 kg - Segera order!</div>
                                    </div>
                                    <div class="alert-stock stock-critical">5 kg</div>
                                </div>
                                <div class="alert-item alert-warning-item">
                                    <div class="alert-icon">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                    <div class="alert-content">
                                        <div class="alert-title">Minyak Goreng</div>
                                        <div class="alert-desc">Stok menipis - Perlu restock</div>
                                    </div>
                                    <div class="alert-stock stock-low">12 L</div>
                                </div>
                                <div class="alert-item alert-warning-item">
                                    <div class="alert-icon">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                    <div class="alert-content">
                                        <div class="alert-title">Beras Premium</div>
                                        <div class="alert-desc">Stok menipis - Perlu restock</div>
                                    </div>
                                    <div class="alert-stock stock-low">18 kg</div>
                                </div>
                                <div class="alert-item alert-danger-item">
                                    <div class="alert-icon">
                                        <i class="bi bi-exclamation-circle-fill"></i>
                                    </div>
                                    <div class="alert-content">
                                        <div class="alert-title">Gula Pasir</div>
                                        <div class="alert-desc">Stok kritis - Order sekarang!</div>
                                    </div>
                                    <div class="alert-stock stock-critical">3 kg</div>
                                </div>
                                <div class="alert-item alert-warning-item">
                                    <div class="alert-icon">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                    <div class="alert-content">
                                        <div class="alert-title">Kecap Manis</div>
                                        <div class="alert-desc">Stok menipis - Perlu restock</div>
                                    </div>
                                    <div class="alert-stock stock-low">8 btl</div>
                                </div>
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
                                <div class="activity-item">
                                    <div class="activity-icon status-pending">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">PO#12345 - PT Maju Jaya</div>
                                        <div class="activity-desc">Tepung, Minyak, Gula (15 items)</div>
                                        <div class="activity-time">2 jam yang lalu</div>
                                    </div>
                                    <div class="activity-status">
                                        <span class="badge-status status-pending">Pending</span>
                                        <div class="activity-amount">Rp 5.450.000</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon status-approved">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">PO#12344 - CV Segar Sentosa</div>
                                        <div class="activity-desc">Sayuran segar (8 items)</div>
                                        <div class="activity-time">5 jam yang lalu</div>
                                    </div>
                                    <div class="activity-status">
                                        <span class="badge-status status-approved">Approved</span>
                                        <div class="activity-amount">Rp 2.340.000</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon status-received">
                                        <i class="bi bi-box-check"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">PO#12343 - UD Berkah</div>
                                        <div class="activity-desc">Daging ayam, bumbu (12 items)</div>
                                        <div class="activity-time">1 hari yang lalu</div>
                                    </div>
                                    <div class="activity-status">
                                        <span class="badge-status status-received">Received</span>
                                        <div class="activity-amount">Rp 8.750.000</div>
                                    </div>
                                </div>
                                <div class="activity-item">
                                    <div class="activity-icon status-approved">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-title">PO#12342 - PT Indo Rasa</div>
                                        <div class="activity-desc">Beras, kecap, saus (10 items)</div>
                                        <div class="activity-time">2 hari yang lalu</div>
                                    </div>
                                    <div class="activity-status">
                                        <span class="badge-status status-approved">Approved</span>
                                        <div class="activity-amount">Rp 4.200.000</div>
                                    </div>
                                </div>
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
                        <div class="quick-stat-value">Rp 125.000</div>
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
                        <div class="quick-stat-value">Rp 36.300</div>
                        <div class="quick-stat-label">Rata-rata Transaksi</div>
                    </div>
                </div>
                <div class="quick-stat-item">
                    <i class="bi bi-clock-fill"></i>
                    <div class="quick-stat-info">
                        <div class="quick-stat-value">18:30</div>
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
            // Display current date
            const dateElement = document.getElementById('currentDate');
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            dateElement.textContent = now.toLocaleDateString('id-ID', options);

            // Sales Chart
            const ctx = document.getElementById('salesChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                        datasets: [{
                            label: 'Penjualan',
                            data: [6200000, 5800000, 6500000, 7100000, 6800000, 8200000, 7500000],
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
                            data: [6000000, 6000000, 6000000, 6000000, 6000000, 7000000, 7000000],
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
    </script>
@endpush
