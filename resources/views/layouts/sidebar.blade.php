<div class="wrapper">
    <div class="sidebar-wrapper" id="sidebar" data-simplebar="true">
        <div class="sidebar-header">
            @php
                $storeLogo = \App\Models\Setting::where('key', 'store_logo')->value('value');
            @endphp

            <div class="user-box text-center py-3">
                <div>
                    <img src="{{ $storeLogo ? asset('storage/' . $storeLogo) : asset('backendpenjual/assets/images/logo-icon.png') }}"
                        class="logo-icon mb-2" alt="Logo Toko" style="max-width: 120px; max-height: 60px;">
                </div>
            </div>
            <div>
                <h4 class="logo-text">Panel Admin</h4>
            </div>
            <div class="toggle-icon ms-auto" id="sidebar-toggle-btn" style="cursor:pointer;">
                <i class="bi bi-chevron-left"></i>
            </div>
        </div>

        <ul class="metismenu" id="menu">
            <li>
                <a href="{{ url('admin') }}">
                    <div class="parent-icon"><i class="bi bi-speedometer2"></i></div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>
            @role('Super Admin')
                <li>
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-person-gear"></i></div>
                        <div class="menu-title">User Management</div>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('admin.users.index') }}"><i class="bi bi-person-check"></i>User
                                Authorization</a>
                        </li>
                    </ul>
                </li>
            @endrole
            @hasanyrole('Super Admin|HeadBar|HeadKitchen')
                <li class="menu-label">Kitchen</li>
                <li>
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-collection"></i></div>
                        <div class="menu-title">Master</div>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('kitchen.kategori.index') }}"><i class="bi bi-tags"></i> Kategori</a>
                        </li>
                        <li>
                            <a href="{{ route('kitchen.bahanbaku.index') }}"><i class="bi bi-box-seam"></i> Data Bahan
                                Baku</a>
                        </li>
                        <li>
                            <a href="{{ route('kitchen.menu.index') }}"><i class="bi bi-egg-fried"></i> Data Menu Jadi</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-gear"></i></div>
                        <div class="menu-title">Operasional</div>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('kitchen.storerequest.index') }}"><i class="bi bi-cart-plus"></i> Store
                                Request</a>
                        </li>
                        <li>
                            <a href="{{ route('kitchen.energycost.index') }}"><i class="bi bi-lightning-charge"></i> Energy
                                Cost Harian</a>
                        </li>
                    </ul>
                </li>
            @endhasanyrole
            @hasanyrole('Super Admin|Accounting')
                <li class="menu-label">Accounting</li>
                <li>
                    <a href="{{ route('acc.suppliers.index') }}">
                        <div class="parent-icon"><i class="bi bi-truck"></i></div>
                        <div class="menu-title">Data Supplier</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('acc.suppliers.credit_limit_monitoring') }}">
                        <div class="parent-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <div class="menu-title">Monitoring Limit</div>
                    </a>
                </li>
                <li>
                    <a href="#" class="has-arrow">
                        <div class="parent-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
                        <div class="menu-title">Laporan</div>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('acc.laporan-penjualan') }}"><i class="bi bi-receipt-cutoff"></i> Laporan Penjualan</a>
                        </li>
                        {{-- Tambahkan item laporan lain di sini jika ada --}}
                        <!--
                        <li>
                            <a href="#"><i class="bi bi-cash-coin"></i> Laporan Pembelian</a>
                        </li>
                        -->
                    </ul>
                </li>
            @endhasanyrole
            @hasanyrole('Super Admin|Purchasing')
                <li class="menu-label">Purchasing</li>
                <li>
                    <a href="{{ route('prc.purchase_orders.index') }}">
                        <div class="parent-icon"><i class="bi bi-list-task"></i></div>
                        <div class="menu-title">Purchase Order</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('prc.penerimaanbarang.index') }}">
                        <div class="parent-icon"><i class="bi bi-box-arrow-up"></i></div>
                        <div class="menu-title">Penerimaan Barang</div>
                    </a>
                </li>
            @endhasanyrole
            @hasanyrole('Super Admin|Cashier')
                <li class="menu-label">Kasir</li>
                <li>
                    <a href="{{ route('cashier.index') }}">
                        <div class="parent-icon"><i class="bi bi-cash-stack"></i></div>
                        <div class="menu-title">Transaksi Baru</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('cashier.history') }}">
                        <div class="parent-icon"><i class="bi bi-clock-history"></i></div>
                        <div class="menu-title">Riwayat Transaksi</div>
                    </a>
                </li>
            @endhasanyrole
            @role('Super Admin')
                <li class="menu-label">Pengaturan</li>
                <li>
                    <a href="{{ route('admin.settings.index') }}">
                        <div class="parent-icon"><i class="bi bi-sliders"></i></div>
                        <div class="menu-title">Setup Sistem</div>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.backup.index') }}">
                        <div class="parent-icon"><i class="bi bi-hdd-stack"></i></div>
                        <div class="menu-title">Backup Database</div>
                    </a>
                </li>
            @endrole
        </ul>
    </div>
</div>
