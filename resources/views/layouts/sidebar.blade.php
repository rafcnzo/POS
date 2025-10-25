<div class="wrapper">
    <div class="sidebar-wrapper" id="sidebar" data-simplebar="true">
        <div class="sidebar-header">
            @php
                $storeName =
                    \App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Restoran Sukses Maju Jaya';
            @endphp

            <div>
                <h4 class="logo-text">{{ $storeName }}</h4>
            </div>
            <div class="toggle-icon" id="sidebar-toggle-btn" style="cursor:pointer; margin-inline-start: 10px;">
                <i class="bi bi-list"></i>
            </div>
        </div>

        <ul class="metismenu" id="menu">
            @role('Super Admin')
                <li class="mb-1">
                    <a href="{{ url('admin') }}">
                        <div class="parent-icon"><i class="bi bi-speedometer2"></i></div>
                        <div class="menu-title">Dashboard</div>
                    </a>
                </li>
            @endrole

            {{-- User Management (Cleaned) --}}
            @canany(['manage users', 'manage roles'])
                <li class="mb-1">
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-person-gear"></i></div>
                        <div class="menu-title">User Management</div>
                    </a>
                    <ul>
                        @can('manage users')
                            <li class="mb-1">
                                <a href="{{ route('admin.users.index') }}"><i class="bi bi-person-check"></i>User
                                    Authorization</a>
                            </li>
                        @endcan

                        @can('manage roles')
                            <li class="mb-1">
                                <a href="{{ route('admin.roles.index') }}"><i class="bi bi-shield-lock"></i>Roles &
                                    Permissions</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Man Power (Cleaned) --}}
            @canany(['manage employees', 'manage payroll'])
                <li class="mb-1">
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-people-fill"></i></div>
                        <div class="menu-title">Man Power</div>
                    </a>
                    <ul>
                        @can('manage employees')
                            <li class="mb-1">
                                <a href="{{ route('admin.karyawan.index') }}"><i class="bi bi-person-badge"></i>Master
                                    Karyawan</a>
                            </li>
                        @endcan
                        @can('manage payroll')
                            <li class="mb-1">
                                <a href="{{ route('acc.payroll.index') }}"><i class="bi bi-cash-coin"></i>Penggajian
                                    Bulanan</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Kitchen/Bar Master (Cleaned) --}}
            @canany(['manage categories', 'manage ingredients', 'manage menus'])
                <li class="mb-1">
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-collection"></i></div>
                        <div class="menu-title">Master</div>
                    </a>
                    <ul>
                        @can('manage categories')
                            <li class="mb-1">
                                <a href="{{ route('kitchen.kategori.index') }}"><i class="bi bi-tags"></i> Kategori</a>
                            </li>
                        @endcan
                        @can('manage ingredients')
                            <li class="mb-1">
                                <a href="{{ route('kitchen.bahanbaku.bar.index') }}"><i class="bi bi-box-seam"></i> Data
                                    Bahan Baku Bar</a>
                            </li>
                            <li class="mb-1">
                                <a href="{{ route('kitchen.bahanbaku.kitchen.index') }}"><i class="bi bi-box-seam"></i>
                                    Data
                                    Bahan Baku Kitchen</a>
                            </li>
                        @endcan
                        @can('manage menus')
                            <li class="mb-1">
                                <a href="{{ route('kitchen.menu.index') }}"><i class="bi bi-egg-fried"></i> Data Menu
                                    Jadi</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Kitchen/Bar Operasional (Cleaned) --}}
            @canany(['create store requests', 'approve store requests', 'view store requests', 'manage energy cost',
                'view ffne'])
                <li class="mb-1">
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-gear"></i></div>
                        <div class="menu-title">Operasional</div>
                    </a>
                    <ul>
                        @canany(['create store requests', 'approve store requests', 'view store requests'])
                            <li class="mb-1">
                                <a href="{{ route('kitchen.storerequest.index') }}"><i class="bi bi-cart-plus"></i>
                                    Store Request</a>
                            </li>
                        @endcanany
                        @can('manage energy cost')
                            <li class="mb-1">
                                <a href="{{ route('kitchen.energycost.index') }}"><i class="bi bi-lightning-charge"></i> Energy
                                    Cost Harian</a>
                            </li>
                        @endcan
                        @can('view ffne')
                            <li class="mb-1">
                                <a href="{{ route('kitchen.ffne.index') }}">
                                    <i class="bi bi-archive"></i>Manajemen FF&E</a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Accounting: Supplier (Cleaned) --}}
            @canany(['manage suppliers', 'manage supplier payments'])
                <li class="mb-1">
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-truck"></i></div>
                        <div class="menu-title">Supplier</div>
                    </a>
                    <ul>
                        @can('manage suppliers')
                            <li class="mb-1">
                                <a href="{{ route('acc.suppliers.index') }}">
                                    <i class="bi bi-card-list"></i> Data Supplier
                                </a>
                            </li>
                        @endcan
                        @can('manage supplier payments')
                            <li class="mb-1">
                                <a href="{{ route('acc.suppliers.payments.index') }}">
                                    <i class="bi bi-credit-card"></i> Pembayaran Supplier
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Accounting: Credit Monitoring (Cleaned) --}}
            @can('view credit monitoring')
                <li class="mb-1">
                    <a href="{{ route('acc.suppliers.credit_limit_monitoring') }}">
                        <div class="parent-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <div class="menu-title">Monitoring Limit</div>
                    </a>
                </li>
            @endcan

            {{-- Accounting: Laporan (Cleaned) --}}
            @canany(['view sales reports', 'view profit loss report'])
                <li class="mb-1">
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
                        <div class="menu-title">Laporan</div>
                    </a>
                    <ul>
                        @can('view sales reports')
                            <li class="mb-1">
                                <a href="{{ route('acc.laporan-penjualan') }}"><i class="bi bi-receipt-cutoff"></i>
                                    Laporan Penjualan</a>
                            </li>
                        @endcan
                        @can('view profit loss report')
                            <li class="mb-1">
                                <a href="{{ route('acc.laporan-labarugi') }}"
                                    class="{{ request()->routeIs('acc.laporan-labarugi*') ? 'mm-active' : '' }}">
                                    <i class="bi bi-file-earmark-bar-graph"></i> Laporan Laba Rugi
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany

            {{-- Accounting: Laporan Inventaris (Cleaned) --}}
            @can('view inventory mutation report')
                <li class="mb-1">
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-clipboard-data"></i></div>
                        <div class="menu-title">Laporan Inventaris</div>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('acc.laporan-stok-mutasi') }}"><i class="bi bi-arrow-left-right"></i>
                                Laporan Mutasi Stok Barang</a>
                        </li>
                    </ul>
                </li>
            @endcan

            {{-- Purchasing (Cleaned) --}}
            @can('view purchase orders')
                <li class="mb-1">
                    <a href="{{ route('prc.purchase_orders.index') }}">
                        <div class="parent-icon"><i class="bi bi-list-task"></i></div>
                        <div class="menu-title">Purchase Order</div>
                    </a>
                </li>
            @endcan
            @can('receive goods')
                <li class="mb-1">
                    <a href="{{ route('prc.penerimaanbarang.index') }}">
                        <div class="parent-icon"><i class="bi bi-box-arrow-up"></i></div>
                        <div class="menu-title">Penerimaan Barang</div>
                    </a>
                </li>
            @endcan

            {{-- Cashier (Cleaned) --}}
            @can('access cashier terminal')
                <li class="mb-1">
                    <a href="{{ route('cashier.index') }}">
                        <div class="parent-icon"><i class="bi bi-cash-stack"></i></div>
                        <div class="menu-title">Transaksi Baru</div>
                    </a>
                </li>
            @endcan
            @can('view transaction history')
                <li class="mb-1">
                    <a href="{{ route('cashier.history') }}">
                        <div class="parent-icon"><i class="bi bi-clock-history"></i></div>
                        <div class="menu-title">Riwayat Transaksi</div>
                    </a>
                </li>
            @endcan

            {{-- Pengaturan --}}
            @role('Super Admin')
                <li class="menu-label mb-1">Pengaturan</li>
                <li class="mb-1">
                    <a href="{{ route('admin.settings.index') }}">
                        <div class="parent-icon"><i class="bi bi-sliders"></i></div>
                        <div class="menu-title">Setup Sistem</div>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.backup.index') }}">
                        <div class="parent-icon"><i class="bi bi-hdd-stack"></i></div>
                        <div class="menu-title">Backup Database</div>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.setup.database.index') }}">
                        <div class="parent-icon"><i class="bi bi-database-gear"></i></div>
                        <div class="menu-title">Pengaturan Database</div>
                    </a>
                </li>
                <li class="mb-1">
                    <a href="{{ route('admin.allowed-ips.index') }}">
                        <div class="parent-icon"><i class="bi bi-shield-check"></i></div>
                        <div class="menu-title">IP Whitelist</div>
                    </a>
                </li>
            @endrole
        </ul>
    </div>
</div>
