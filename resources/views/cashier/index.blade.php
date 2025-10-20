@extends('app')

@section('style')
    <style>
        /* POS Card Styles */
        .pos-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            padding: 1.5rem;
            height: calc(100vh - 180px);
            display: flex;
            flex-direction: column;
        }

        .pos-search-wrapper {
            margin-bottom: 1.5rem;
        }

        /* Category Tabs */
        .pos-category-tabs {
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .pos-category-tabs .nav-tabs {
            border: none;
            gap: 0.5rem;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .pos-category-tabs .nav-tabs::-webkit-scrollbar {
            height: 4px;
        }

        .pos-category-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-radius: 8px 8px 0 0;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .pos-category-tabs .nav-link:hover {
            background: #f8f9fa;
            color: #495057;
        }

        .pos-category-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .pos-category-tabs .nav-link i {
            margin-right: 0.5rem;
        }

        /* Products Grid */
        .tab-content {
            flex: 1;
            overflow-y: auto;
        }

        .pos-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
            padding: 0.5rem 0;
        }

        .pos-product-card {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .pos-product-card:hover {
            border-color: #667eea;
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .pos-product-image {
            width: 100%;
            height: 120px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0.75rem;
            position: relative;
        }

        .pos-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pos-product-placeholder {
            width: 100%;
            height: 100%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
        }

        .pos-product-placeholder i {
            font-size: 2.5rem;
        }

        .pos-stock-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: #dc3545;
            color: #fff;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .pos-product-info {
            text-align: center;
        }

        .pos-product-name {
            font-size: 0.9rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pos-product-price {
            font-size: 1rem;
            font-weight: 700;
            color: #667eea;
            margin: 0;
        }

        /* Cart Card */
        .pos-cart-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            height: calc(100vh - 180px);
            display: flex;
            flex-direction: column;
        }

        .pos-cart-header {
            padding: 1.5rem;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pos-cart-header h4 {
            margin: 0;
            font-size: 1.25rem;
            color: #2d3748;
        }

        .pos-cart-header h4 i {
            color: #667eea;
            margin-right: 0.5rem;
        }

        .btn-clear-cart {
            background: #fff;
            border: 1px solid #e9ecef;
            color: #dc3545;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-clear-cart:hover {
            background: #dc3545;
            color: #fff;
            border-color: #dc3545;
        }

        /* Custom style for .btn-add-secondary (Buat Reservasi button) */
        .btn-add-secondary {
            background: #e9ecef;
            color: #343a40;
            font-weight: 600;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 0.6rem 1.4rem;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.25s, box-shadow 0.2s, transform 0.2s, color 0.2s;
        }

        .btn-add-secondary i {
            font-size: 1.25rem;
        }

        .btn-add-secondary:hover,
        .btn-add-secondary:focus {
            background: linear-gradient(90deg, #5f2c82 0%, #49a09d 100%);
            color: #fff;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.18);
            transform: translateY(-2px) scale(1.03);
            text-decoration: none;
        }

        .pos-cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
            color: #adb5bd;
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-cart p {
            margin: 0;
            font-size: 0.95rem;
        }

        .cart-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-item-info h6 {
            margin: 0 0 0.25rem 0;
            font-size: 0.95rem;
            color: #2d3748;
        }

        .cart-item-price {
            font-size: 0.875rem;
            color: #667eea;
            font-weight: 600;
        }

        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
        }

        .btn-remove-item {
            background: #dc3545;
            border: none;
            color: #fff;
            width: 28px;
            height: 28px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .btn-remove-item:hover {
            background: #c82333;
        }

        .pos-cart-summary {
            padding: 1.5rem;
            border-top: 2px solid #e9ecef;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            color: #6c757d;
        }

        .summary-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d3748;
            padding-top: 0.75rem;
            border-top: 1px solid #e9ecef;
        }

        .summary-value {
            font-weight: 600;
        }

        .btn-checkout {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-checkout:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-checkout:disabled {
            background: #e9ecef;
            color: #adb5bd;
            cursor: not-allowed;
        }

        .btn-checkout i {
            margin-right: 0.5rem;
        }

        /* Quantity Modal */
        .qty-product-info {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .qty-product-info h6 {
            margin: 0 0 0.5rem 0;
            color: #2d3748;
        }

        .qty-product-info p {
            margin: 0;
            color: #667eea;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .qty-adjuster {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .btn-qty-minus,
        .btn-qty-plus {
            width: 50px;
            height: 50px;
            border: 2px solid #667eea;
            background: #fff;
            color: #667eea;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: all 0.3s ease;
        }

        .btn-qty-minus:hover,
        .btn-qty-plus:hover {
            background: #667eea;
            color: #fff;
        }

        .qty-input {
            width: 100px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }

        .qty-note {
            text-align: center;
            color: #6c757d;
            font-size: 0.875rem;
            margin: 0;
        }

        /* Checkout Modal */
        .checkout-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }

        .checkout-summary h6 {
            margin-bottom: 1rem;
            color: #2d3748;
        }

        .checkout-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .checkout-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #dee2e6;
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d3748;
        }

        .total-value {
            color: #667eea;
        }

        @media (max-width: 991px) {

            .pos-card,
            .pos-cart-card {
                height: auto;
                min-height: 500px;
            }

            .pos-products-grid {
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            }
        }

        .pos-search-container {
            position: relative;
            /* Penting sebagai jangkar untuk hasil pencarian */
        }

        .search-results-container {
            display: none;
            /* Sembunyikan secara default */
            position: absolute;
            top: 100%;
            /* Muncul tepat di bawah search box */
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            /* Pastikan muncul di atas konten lain */
        }

        .search-result-item {
            padding: 12px 16px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f1f1;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .search-result-item:hover {
            background-color: #f8f9fa;
        }

        .search-result-item .price {
            font-weight: 600;
            color: #667eea;
        }

        .search-no-results {
            padding: 16px;
            text-align: center;
            color: #6c757d;
        }

        /* Modernize button styles */
        .btn-check:checked+.btn-outline-primary,
        .btn-check:active+.btn-outline-primary,
        .btn-check:focus+.btn-outline-primary {
            background-color: #667eea !important;
            color: #fff !important;
            border-color: #667eea !important;
            box-shadow: 0 2px 12px rgba(102, 126, 234, 0.1);
        }

        .btn-outline-primary,
        .btn-outline-secondary,
        .btn-outline-info {
            border-radius: 30px !important;
            padding: 8px 20px !important;
            font-weight: 600 !important;
            border-width: 2px !important;
            transition:
                background 0.15s,
                color 0.15s,
                box-shadow 0.2s;
            background: #fff;
            box-shadow: 0 2px 12px rgba(102, 126, 234, 0.02);
        }

        .btn-outline-primary:hover,
        .btn-outline-secondary:hover,
        .btn-outline-info:hover {
            background: linear-gradient(90deg, #667eea 60%, #5a67d8 100%);
            color: #fff !important;
            border-color: #667eea;
            box-shadow: 0 4px 18px rgba(102, 126, 234, 0.12);
        }

        .btn-outline-secondary {
            border-color: #7c8a97;
            color: #535353;
        }

        .btn-check:checked+.btn-outline-secondary {
            background: #495b6e !important;
            color: #fff !important;
            border-color: #495b6e !important;
        }

        .btn-outline-info {
            border-color: #38b6ff;
            color: #2a7ea8;
        }

        .btn-check:checked+.btn-outline-info {
            background: #38b6ff !important;
            color: #fff !important;
            border-color: #38b6ff !important;
        }

        /* Modernize checkbox selection */
        .btn-check:focus+label,
        .btn-outline-primary:focus,
        .btn-outline-secondary:focus,
        .btn-outline-info:focus {
            outline: 0;
            box-shadow: 0 0 0 3px #b3bcf5;
        }

        /* Search box redesign */
        .search-box {
            background: #f1f5fb;
            border-radius: 32px;
            padding: 6px 18px;
            border: 1px solid #d5e0fa;
            transition: box-shadow 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.04);
            position: relative;
        }

        .search-box:focus-within {
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.12);
            border-color: #667eea;
            background: #fff;
        }

        .search-box i {
            color: #667eea;
            font-size: 18px;
            padding-left: 3px;
        }

        .search-box input {
            border: none;
            outline: none;
            background: transparent;
            width: 90%;
            font-size: 1.06rem;
            color: #263159;
        }

        .search-box input::placeholder {
            color: #aab7cf;
            opacity: 1;
            font-weight: 400;
            font-size: 15px;
        }

        /* Reservasi button modern style */
        .btn-add-secondary {
            background: linear-gradient(90deg, #667eea 55%, #38b6ff 100%);
            color: #fff !important;
            border: none;
            border-radius: 28px;
            font-weight: 600;
            padding: 10px 22px;
            box-shadow: 0 3px 14px rgba(59, 130, 246, 0.10);
            transition: background 0.15s, box-shadow 0.15s, color 0.15s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-add-secondary:hover {
            background: linear-gradient(90deg, #4452c4 70%, #1365dd 100%);
            color: #eee !important;
            box-shadow: 0 6px 22px rgba(59, 130, 246, 0.15);
        }

        .btn-add-secondary i {
            font-size: 1.1rem;
        }

        /* Cart summary card */
        .pos-cart-summary {
            background: #f9fbfd;
            border-radius: 12px;
            padding: 22px 18px;
            box-shadow: 0 2px 16px rgba(36, 68, 194, 0.05);
            margin-bottom: 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 1.03rem;
            margin-bottom: 6px;
            color: #656d8a;
        }

        .summary-total {
            font-weight: 700;
            color: #263159;
            font-size: 1.18rem;
            margin-top: 8px;
        }

        .summary-value {
            font-family: 'Nunito', 'Segoe UI', Arial, sans-serif;
            letter-spacing: 0.1px;
        }

        /* Responsive padding for main POS card */
        .pos-card {
            border-radius: 14px;
            box-shadow: 0 3px 22px rgba(59, 130, 246, 0.03);
            border: 1px solid #e3e9fa;
            background: #fff;
            padding: 2.3rem 2rem 1.3rem 2rem;
            margin-bottom: 28px;
        }

        /* Transaction type header modern look */
        .form-label.fw-bold {
            color: #263159;
            font-size: 1.03rem;
            padding-right: 7px;
            margin-top: 3px;
            font-family: 'Nunito', 'Arial', sans-serif;
        }

        .mb-3.d-flex.align-items-center.gap-2 {
            margin-bottom: 17px !important;
        }

        @media (max-width:800px) {
            .pos-card {
                padding: 1rem 1rem 0.7rem 1rem !important;
            }

            .btn-add-secondary {
                padding: 8px 13px;
                font-size: 15px;
            }

            .summary-row,
            .summary-total {
                font-size: 1rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="pos-card">
                        <div class="mb-3 d-flex align-items-center gap-2">
                            <label class="form-label mb-0 fw-bold">Tipe Transaksi:</label>
                            <div>
                                <input type="radio" class="btn-check" name="transaction_type_selector" id="type_regular"
                                    value="regular" checked>
                                <label class="btn btn-sm btn-outline-primary" for="type_regular">
                                    <i class="bi bi-cart"></i> Regular
                                </label>

                                <input type="radio" class="btn-check" name="transaction_type_selector" id="type_employee"
                                    value="employee_meal">
                                <label class="btn btn-sm btn-outline-secondary" for="type_employee">
                                    <i class="bi bi-person-badge"></i> Makan Karyawan
                                </label>

                                <input type="radio" class="btn-check" name="transaction_type_selector"
                                    id="type_complimentary" value="complimentary">
                                <label class="btn btn-sm btn-outline-info" for="type_complimentary">
                                    <i class="bi bi-gift"></i> Complimentary/Official
                                </label>
                            </div>
                        </div>
                        <hr>
                        <div class="pos-search-container">
                            <div class="pos-search-wrapper" style="display: flex; align-items: center; gap: 20px;">
                                <div class="search-box" style="flex: 1;">
                                    <i class="bi bi-search"></i>
                                    <input type="text" placeholder="Cari produk..." id="searchProduct"
                                        autocomplete="off">
                                </div>
                                <button class="btn-add-secondary" id="btnTambahReservasi" type="button"
                                    data-bs-toggle="modal" data-bs-target="#modalReservasi" style="margin-left: 20px;">
                                    <i class="bi bi-calendar-plus"></i>
                                    <span>Buat Reservasi</span>
                                </button>
                            </div>
                            <div class="search-results-container" id="searchResultsContainer"></div>
                        </div>

                        <div class="pos-category-tabs">
                            <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
                                @forelse($categories as $index => $category)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index == 0 ? 'active' : '' }}"
                                            id="cat-{{ $category->id }}-tab" data-bs-toggle="tab"
                                            data-bs-target="#cat-{{ $category->id }}" type="button" role="tab">
                                            <i class="bi bi-{{ $category->icon ?? 'grid' }}"></i>
                                            {{ $category->name }}
                                        </button>
                                    </li>
                                @empty
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab"
                                            data-bs-target="#all" type="button" role="tab">
                                            <i class="bi bi-grid"></i>
                                            Semua Produk
                                        </button>
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        <!-- Products Grid -->
                        <div class="tab-content" id="categoryTabContent">
                            @forelse($categories as $index => $category)
                                <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}"
                                    id="cat-{{ $category->id }}" role="tabpanel">
                                    <div class="pos-products-grid">
                                        @foreach ($category->products as $product)
                                            <div class="pos-product-card 
                                                {{ ($product->calculated_stock ?? 0) <= 0 ? 'out-of-stock' : '' }}"
                                                data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                data-price="{{ $product->price }}"
                                                data-stock="{{ $product->calculated_stock ?? 0 }}">
                                                <div class="pos-product-info p-3">
                                                    <h5 class="pos-product-name mb-1">{{ $product->name }}</h5>
                                                    <p class="pos-product-price mb-1">Rp
                                                        {{ number_format($product->price, 0, ',', '.') }}</p>
                                                    <div class="pos-product-minor">
                                                        @if (($product->calculated_stock ?? 0) <= 0)
                                                            <span class="text-danger"><b>Habis</b></span>
                                                        @else
                                                            <span class="text-muted">
                                                                Stok: <b>{{ $product->calculated_stock ?? 0 }}</b>
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="tab-pane fade show active" id="all" role="tabpanel">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <h4>Belum ada produk</h4>
                                        <p>Tambahkan produk terlebih dahulu</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Right Side - Cart -->
                <div class="col-lg-4">
                    <div class="pos-cart-card">
                        <div class="pos-cart-header">
                            <h4><i class="bi bi-cart3"></i> Keranjang</h4>
                            <button type="button" class="btn-clear-cart" id="btnClearCart">
                                <i class="bi bi-trash"></i> Hapus Semua
                            </button>
                        </div>

                        <div class="pos-cart-items" id="cartItems">
                            <div class="empty-cart">
                                <i class="bi bi-cart-x"></i>
                                <p>Keranjang masih kosong</p>
                            </div>
                        </div>

                        <div class="pos-cart-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span class="summary-value" id="subtotalAmount">Rp 0</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total:</span>
                                <span class="summary-value" id="totalAmount">Rp 0</span>
                            </div>
                        </div>

                        <button type="button" class="btn-checkout" id="btnCheckout" disabled>
                            <i class="bi bi-check-circle"></i>
                            Lanjut ke Pembayaran
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Quantity Adjuster -->
    <div class="modal fade" id="modalCustomizeItem" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kustomisasi Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="qty-product-info">
                        <h6 id="customizeProductName">Nama Produk</h6>
                        <p id="customizeProductPrice">Rp 0</p>
                    </div>

                    <div class="qty-adjuster">
                        <button type="button" class="btn-qty-minus"><i class="bi bi-dash-lg"></i></button>
                        <input type="number" class="qty-input" id="customizeQtyInput" value="1" min="1">
                        <button type="button" class="btn-qty-plus"><i class="bi bi-plus-lg"></i></button>
                    </div>
                    <p class="qty-note text-center">Stok tersedia: <span id="customizeStock">0</span></p>
                    <hr>

                    <div id="modifierGroupsContainer">
                    </div>
                    <hr>

                    <div class="form-group-custom">
                        <label for="customizeItemNotes" class="form-label-custom">
                            <i class="bi bi-pencil-square"></i> Catatan untuk Item Ini
                        </label>
                        <textarea class="form-control" id="customizeItemNotes" rows="2"
                            placeholder="Contoh: Tidak pedas, tanpa bawang..."></textarea>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Total Item: <span id="modalItemTotal">Rp 0</span></strong>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="btnConfirmAddToCart">Tambah ke
                            Keranjang</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="formToPayment" action="{{ route('cashier.startTransaction') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="cart_data" id="cartDataInput">
        <input type="hidden" name="transaction_type" id="transactionTypeInput" value="regular">
    </form>

    <div class="modal fade" id="modalReservasi" tabindex="-1" aria-labelledby="modalReservasiLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                {{-- Ganti route() dengan nama route Anda --}}
                <form id="formReservasi" data-url="{{ route('cashier.reservations.store') }}">
                    @csrf
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon"><i class="bi bi-calendar-plus-fill"></i></div>
                            <h5 class="modal-title" id="modalReservasiLabel">Buat Reservasi Baru</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup"><i
                                class="bi bi-x"></i></button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formReservasiAlert"></div>

                        <div class="form-group-custom">
                            <label for="res_customer_name" class="form-label-custom required"><i
                                    class="bi bi-person"></i> Nama Customer</label>
                            <input type="text" class="form-control-custom" id="res_customer_name"
                                name="customer_name" required>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-group-custom">
                                    <label for="res_pax" class="form-label-custom required"><i class="bi bi-people"></i>
                                        Jumlah Orang (Pax)</label>
                                    <input type="number" class="form-control-custom" id="res_pax" name="pax"
                                        min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-custom">
                                    <label for="res_table_number" class="form-label-custom"><i class="bi bi-table"></i>
                                        No. Meja</label>
                                    <input type="text" class="form-control-custom" id="res_table_number"
                                        name="table_number">
                                </div>
                            </div>
                        </div>
                        <div class="form-group-custom">
                            <label for="res_reservation_time" class="form-label-custom required"><i
                                    class="bi bi-clock"></i> Waktu Reservasi</label>
                            <input type="datetime-local" class="form-control-custom" id="res_reservation_time"
                                name="reservation_time" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="res_contact_number" class="form-label-custom"><i class="bi bi-phone"></i> No.
                                Kontak</label>
                            <input type="tel" class="form-control-custom" id="res_contact_number"
                                name="contact_number">
                        </div>
                        <hr style="border-top: 2px dashed #ddd; margin: 20px 0;">

                        <h6><i class="bi bi-wallet2"></i> Detail Deposit (DP)</h6>
                        <div class="form-group-custom">
                            <label for="res_deposit_amount" class="form-label-custom required"><i
                                    class="bi bi-cash-coin"></i> Jumlah DP (Rp)</label>
                            <input type="number" class="form-control-custom" id="res_deposit_amount"
                                name="deposit_amount" min="0" value="0" required> {{-- Default 0 --}}
                        </div>

                        <div class="form-group-custom" id="deposit_method_group" style="display: none;">
                            <label for="res_deposit_payment_method" class="form-label-custom required"><i
                                    class="bi bi-credit-card"></i> Metode Bayar DP</label>
                            <select class="form-control-custom" id="res_deposit_payment_method"
                                name="deposit_payment_method">
                                <option value="">-- Pilih Metode --</option>
                                <option value="cash">Tunai (Cash)</option>
                                <option value="edc">EDC (Kartu)</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        <hr style="border-top: 2px dashed #ddd; margin: 20px 0;">
                        <div class="form-group-custom">
                            <label for="res_notes" class="form-label-custom"><i class="bi bi-pencil-square"></i>
                                Catatan</label>
                            <textarea class="form-control-custom" id="res_notes" name="notes" rows="2"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal"><i
                                class="bi bi-x"></i> Batal</button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanReservasi"><i
                                class="bi bi-check"></i> Simpan Reservasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        const menuItemsData = @json($menuItemsWithStock);
        let cart = [];
        let currentProduct = null;

        const formatCurrency = (value) => 'Rp ' + (value || 0).toLocaleString('id-ID');
        document.addEventListener('DOMContentLoaded', function() {
            const formReservasi = document.getElementById('formReservasi');
            const modalReservasi = new bootstrap.Modal(document.getElementById('modalReservasi'));
            const alertReservasi = document.getElementById('formReservasiAlert');
            const btnSimpanReservasi = document.getElementById('btnSimpanReservasi');

            // --- LOGIKA UNTUK DP & METODE BAYAR ---
            const depositAmountInput = document.getElementById('res_deposit_amount');
            const depositMethodGroup = document.getElementById('deposit_method_group');
            const depositMethodSelect = document.getElementById('res_deposit_payment_method');

            const correctAuthPassword = @json($authorizationPassword ?? '');
            let currentTransactionType = 'regular';
            const transactionTypeRadios = document.querySelectorAll('input[name="transaction_type_selector"]');
            const transactionTypeInputHidden = document.getElementById('transactionTypeInput');

            if (depositAmountInput && depositMethodGroup && depositMethodSelect) {
                function toggleDepositMethod() {
                    const amount = parseFloat(depositAmountInput.value) || 0;
                    if (amount > 0) {
                        depositMethodGroup.style.display = 'block';
                        depositMethodSelect.setAttribute('required', 'required');
                    } else {
                        depositMethodGroup.style.display = 'none';
                        depositMethodSelect.removeAttribute('required');
                        depositMethodSelect.value = '';
                    }
                }
                depositAmountInput.addEventListener('input', toggleDepositMethod);
                depositAmountInput.addEventListener('change', toggleDepositMethod);
                document.getElementById('modalReservasi').addEventListener('show.bs.modal', function() {
                    depositAmountInput.value = 0;
                    toggleDepositMethod();
                });
                document.getElementById('modalReservasi').addEventListener('hidden.bs.modal', function() {
                    alertReservasi.innerHTML = '';
                    formReservasi.reset();
                    toggleDepositMethod();
                });
            }
            if (formReservasi) {
                formReservasi.addEventListener('submit', function(e) {
                    e.preventDefault();
                    btnSimpanReservasi.disabled = true;
                    alertReservasi.innerHTML = '';
                    Swal.showLoading();

                    const formData = new FormData(formReservasi);
                    const url = formReservasi.dataset.url;

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json',
                            },
                            body: formData,
                        })
                        .then(async response => {
                            const data = await response.json();
                            Swal.close();
                            btnSimpanReservasi.disabled = false;

                            if (response.ok) {
                                modalReservasi.hide();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message || 'Reservasi berhasil disimpan.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                const errors = data.errors ? Object.values(data.errors).flat().join(
                                    '<br>') : (data.message || 'Gagal menyimpan data');
                                alertReservasi.innerHTML =
                                    `<div class="alert alert-danger" role="alert">${errors}</div>`;
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            btnSimpanReservasi.disabled = false;
                            alertReservasi.innerHTML =
                                `<div class="alert alert-danger" role="alert">Terjadi kesalahan jaringan: ${error.message}</div>`;
                        });
                });
            }

            // --- RESET modalCustomizeItem contents on close ---
            const modalCustomizeItemEl = document.getElementById('modalCustomizeItem');
            if (modalCustomizeItemEl) {
                modalCustomizeItemEl.addEventListener('hidden.bs.modal', function() {
                    document.getElementById('customizeProductName').textContent = 'Nama Produk';
                    document.getElementById('customizeProductPrice').textContent = 'Rp 0';
                    document.getElementById('customizeStock').textContent = '0';
                    document.getElementById('customizeQtyInput').value = "1";
                    document.getElementById('customizeItemNotes').value = '';
                    document.getElementById('modalItemTotal').textContent = 'Rp 0';
                    document.getElementById('modifierGroupsContainer').innerHTML = '';
                });
            }

            transactionTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const selectedType = this.value;
                    const previousType = currentTransactionType;

                    if (selectedType === 'complimentary') {
                        Swal.fire({
                            title: 'Otorisasi Diperlukan',
                            text: 'Masukkan password otorisasi untuk melanjutkan:',
                            input: 'password',
                            inputPlaceholder: 'Password...',
                            inputAttributes: {
                                autocapitalize: 'off',
                                autocorrect: 'off'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Otorisasi',
                            cancelButtonText: 'Batal',
                            showLoaderOnConfirm: true,
                            preConfirm: (password) => {
                                if (password === correctAuthPassword) {
                                    return true;
                                } else {
                                    Swal.showValidationMessage(
                                        'Password otorisasi salah!');
                                    return false;
                                }
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }).then((result) => {
                            if (result.isConfirmed) {
                                currentTransactionType = selectedType;
                                transactionTypeInputHidden.value = selectedType;
                                // Swal.fire({
                                //     icon: 'success',
                                //     title: 'Otorisasi Berhasil',
                                //     text: 'Mode Complimentary/Official diaktifkan.',
                                //     timer: 2000,
                                //     showConfirmButton: false
                                // });
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Mode Complimentary',
                                    text: 'Harga item akan dihitung, namun total akhir transaksi akan menjadi Rp 0.',
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            } else {
                                document.querySelector(
                                    `input[name="transaction_type_selector"][value="${previousType}"]`
                                ).checked = true;
                                transactionTypeInputHidden.value = previousType;
                            }
                        });
                    } else {
                        currentTransactionType = selectedType;
                        transactionTypeInputHidden.value = selectedType;
                        // console.log("Tipe transaksi diubah menjadi:", selectedType);
                    }
                });
            });
        });

        // Jangan panggil dua kali swal complimentary di bawah!
        // Hapus kode swal info double complimentary
        // document.querySelectorAll('input[name="transaction_type_selector"]').forEach(radio => {
        //     radio.addEventListener('change', function() {
        //         const selectedType = this.value;
        //         document.getElementById('transactionTypeInput').value = selectedType;
        //         console.log("Tipe transaksi diubah menjadi:", selectedType);
        //         if (selectedType === 'complimentary') {
        //             Swal.fire({
        //                 icon: 'info',
        //                 title: 'Mode Complimentary',
        //                 text: 'Harga item akan dihitung, namun total akhir transaksi akan menjadi Rp 0.',
        //                 timer: 3000,
        //                 showConfirmButton: false
        //             });
        //         }
        //     });
        // });

        document.querySelectorAll('.pos-product-card').forEach(card => {
            card.addEventListener('click', function() {
                const productId = this.dataset.id;
                const product = menuItemsData.find(item => item.id == productId);

                if (!product || product.calculated_stock <= 0) {
                    Swal.fire('Stok Habis', 'Stok produk ini sudah habis.', 'warning');
                    return;
                }

                currentProduct = product;
                openCustomizeModal(product);
            });
        });

        function openCustomizeModal(product) {
            document.getElementById('customizeProductName').textContent = product.name;
            document.getElementById('customizeProductPrice').textContent = formatCurrency(product.price);
            document.getElementById('customizeStock').textContent = product.calculated_stock;

            const qtyInput = document.getElementById('customizeQtyInput');
            qtyInput.value = 1;
            qtyInput.max = product.calculated_stock;

            const container = document.getElementById('modifierGroupsContainer');
            container.innerHTML = '';

            if (product.modifier_groups && product.modifier_groups.length > 0) {
                product.modifier_groups.forEach(group => {
                    let groupHtml = `<div class="mb-3"><h6>${group.name}</h6>`;

                    group.modifiers.forEach(modifier => {
                        const inputType = group.selection_type === 'single' ? 'radio' :
                            'checkbox';
                        groupHtml += `
                        <div class="form-check">
                            <input class="form-check-input modifier-option" type="${inputType}" name="group-${group.id}" 
                                   id="mod-${modifier.id}" value="${modifier.id}" data-price="${modifier.price}">
                            <label class="form-check-label" for="mod-${modifier.id}">
                                ${modifier.name} <span class="text-success">(+${formatCurrency(modifier.price)})</span>
                            </label>
                        </div>
                        `;
                    });
                    groupHtml += `</div>`;
                    container.innerHTML += groupHtml;
                });
            } else {
                container.innerHTML =
                    '<p class="text-muted text-center">Tidak ada pilihan tambahan untuk item ini.</p>';
            }

            updateModalTotal();

            container.querySelectorAll('.modifier-option').forEach(option => {
                option.addEventListener('change', updateModalTotal);
            });

            new bootstrap.Modal(document.getElementById('modalCustomizeItem')).show();
        }

        function updateModalTotal() {
            const basePrice = currentProduct.price;
            const qty = parseInt(document.getElementById('customizeQtyInput').value);

            let modifiersPrice = 0;
            document.querySelectorAll('.modifier-option:checked').forEach(option => {
                modifiersPrice += parseFloat(option.dataset.price);
            });

            const total = (basePrice + modifiersPrice) * qty;
            document.getElementById('modalItemTotal').textContent = formatCurrency(total);
        }

        document.querySelector('#modalCustomizeItem .btn-qty-plus').addEventListener('click', () => {
            const input = document.getElementById('customizeQtyInput');
            if (parseInt(input.value) < parseInt(input.max)) {
                input.value = parseInt(input.value) + 1;
                updateModalTotal();
            }
        });
        document.querySelector('#modalCustomizeItem .btn-qty-minus').addEventListener('click', () => {
            const input = document.getElementById('customizeQtyInput');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateModalTotal();
            }
        });
        document.getElementById('customizeQtyInput').addEventListener('change', updateModalTotal);

        document.getElementById('btnConfirmAddToCart').addEventListener('click', function() {
            const qty = parseInt(document.getElementById('customizeQtyInput').value);
            const notes = document.getElementById('customizeItemNotes').value.trim();

            const selectedModifierIds = [];
            document.querySelectorAll('.modifier-option:checked').forEach(option => {
                selectedModifierIds.push(option.value);
            });

            const selectedModifiers = [];
            if (currentProduct.modifier_groups && currentProduct.modifier_groups.length > 0) {
                selectedModifierIds.forEach(modId => {
                    for (const group of currentProduct.modifier_groups) {
                        const found = group.modifiers.find(m => m.id == modId);
                        if (found) {
                            selectedModifiers.push(found);
                            break;
                        }
                    }
                });
            }

            const cartItemId = currentProduct.id + '-' + selectedModifierIds.sort().join('-') + (notes ?
                '-' + btoa(
                    notes) : '');

            const existingItem = cart.find(item => item.cartItemId === cartItemId);

            if (existingItem) {
                existingItem.quantity += qty;
            } else {
                cart.push({
                    cartItemId: cartItemId,
                    menu_item_id: currentProduct.id,
                    name: currentProduct.name,
                    price: currentProduct.price,
                    quantity: qty,
                    modifiers: selectedModifiers,
                    modifier_ids: selectedModifierIds,
                    notes: notes
                });
            }

            updateCartDisplay();
            bootstrap.Modal.getInstance(document.getElementById('modalCustomizeItem')).hide();
            Swal.fire('Berhasil', 'Item ditambahkan ke keranjang.', 'success');
        });

        function updateCartDisplay() {
            const cartContainer = document.getElementById('cartItems');
            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="empty-cart">
                        <i class="bi bi-cart-x"></i>
                        <p>Keranjang masih kosong</p>
                    </div>
                `;
                document.getElementById('btnCheckout').disabled = true;
            } else {
                cartContainer.innerHTML = cart.map(item => {
                    const modifiersPrice = item.modifiers.reduce((sum, mod) => sum + parseFloat(mod
                        .price), 0);
                    const itemTotal = (item.price * item.quantity) + (modifiersPrice * item.quantity);

                    let modifiersHtml = item.modifiers.map(mod =>
                        `<div class="cart-modifier-item ms-3"><small>+ ${mod.name}</small></div>`
                    ).join('');
                    let notesHtml = item.notes ?
                        `<div class="cart-notes-item ms-3 fst-italic"><small>Catatan: ${item.notes}</small></div>` :
                        '';
                    return `
                        <div class="cart-item" data-cart-id="${item.cartItemId}">
                            <div class="cart-item-info">
                                <h6>${item.name}</h6>
                                ${modifiersHtml}
                                ${notesHtml}
                                <span class="cart-item-price">${item.quantity} x ${formatCurrency(item.price)}${modifiersPrice > 0 ? ' + ' + formatCurrency(modifiersPrice) : ''}</span>
                            </div>
                            <div class="cart-item-actions">
                                <strong>${formatCurrency(itemTotal)}</strong>
                                <button class="btn-remove-item" onclick="removeFromCart('${item.cartItemId}')"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    `;
                }).join('');
                document.getElementById('btnCheckout').disabled = false;
            }
            updateTotal();
        }

        function removeFromCart(cartItemId) {
            Swal.fire({
                title: 'Yakin hapus item?',
                text: 'Item ini akan dihapus dari keranjang.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = cart.filter(item => item.cartItemId !== cartItemId);
                    updateCartDisplay();
                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapus',
                        text: 'Item dihapus dari keranjang.',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            });
        }

        document.getElementById('btnClearCart').addEventListener('click', function() {
            if (cart.length === 0) return;
            Swal.fire({
                title: 'Hapus semua item?',
                text: 'Seluruh keranjang akan dikosongkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = [];
                    updateCartDisplay();
                    Swal.fire({
                        icon: 'success',
                        title: 'Keranjang dikosongkan',
                        text: 'Semua item telah dihapus.',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            });
        });

        function updateTotal() {
            const total = cart.reduce((sum, item) => {
                const modifiersPrice = item.modifiers.reduce((s, mod) => s + parseFloat(mod.price), 0);
                return sum + ((item.price + modifiersPrice) * item.quantity);
            }, 0);
            document.getElementById('subtotalAmount').textContent = formatCurrency(total);
            document.getElementById('totalAmount').textContent = formatCurrency(total);
        }

        document.getElementById('btnCheckout').addEventListener('click', function(e) {
            e.preventDefault(); // <-- 1. Cegah submit form biasa

            const formElement = document.getElementById('formToPayment');
            const url = formElement.action;
            const currentType = document.querySelector('input[name="transaction_type_selector"]:checked').value;

            // Siapkan data untuk dikirim
            const dataToSend = {
                cart_data: JSON.stringify(cart),
                transaction_type: currentType,
                _token: formElement.querySelector('input[name="_token"]').value // Ambil CSRF token
            };

            Swal.showLoading(); // Tampilkan loading

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', // Kirim sebagai JSON
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': dataToSend._token
                    },
                    body: JSON.stringify(dataToSend) // Ubah data jadi string JSON
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url; // Langsung redirect ke halaman payment
                        return; // Hentikan eksekusi lebih lanjut
                    }
                    return response.json().then(data => ({
                        data,
                        status: response.status,
                        ok: response.ok
                    }));
                })
                .then(res => {
                    if (!res) return; // Keluar jika sudah redirect

                    const {
                        data,
                        ok
                    } = res;
                    Swal.close(); // Tutup loading

                    if (ok && data.status === 'success' && data.is_complimentary === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Transaksi Complimentary berhasil disimpan.',
                            timer: 2500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = data.redirect_url ||
                            "{{ route('cashier.index') }}"; // Ambil URL dari JSON
                        });
                        cart = [];
                        updateCartDisplay();
                    } else if (!ok) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Gagal memulai transaksi. Coba lagi.',
                        });
                    } else {
                        console.warn("Unexpected response:", data);
                        if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        }
                    }
                })
                .catch(error => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Jaringan',
                        text: 'Tidak dapat terhubung ke server: ' + error.message,
                    });
                    console.error("Fetch error:", error);
                });
        });

        const btnProcessPayment = document.getElementById('btnProcessPayment');
        if (btnProcessPayment) {
            btnProcessPayment.addEventListener('click', function() {
                const form = document.getElementById('formCheckout');

                if (!form || !form.checkValidity()) {
                    if (form) form.reportValidity();
                    return;
                }

                const formData = new FormData(form);
                const typeTransaksi = formData.get('transaction_type') ?? (document.getElementById(
                    'transactionTypeInput')?.value ?? '');

                const formattedItems = cart.map(item => ({
                    menu_item_id: item.menu_item_id,
                    quantity: item.quantity,
                    modifiers: item.modifier_ids,
                    notes: item.notes
                }));

                const payments = [{
                    method: formData.get('payment_method'),
                    amount: cart.reduce((sum, item) => {
                        const modifiersPrice = item.modifiers.reduce((s, mod) => s + parseFloat(mod
                            .price), 0);
                        return sum + ((item.price + modifiersPrice) * item.quantity);
                    }, 0)
                }];

                const orderData = {
                    items: formattedItems,
                    table_number: formData.get('table_number'),
                    customer_name: formData.get('customer_name'),
                    order_type: formData.get('order_type'),
                    notes: formData.get('notes'),
                    payments: payments,
                    transaction_type: typeTransaksi
                };

                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    text: 'Proses pembayaran dan simpan transaksi?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, proses!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const csrfTokenEl = document.querySelector('meta[name="csrf-token"]');
                        const csrfToken = csrfTokenEl ? csrfTokenEl.content : '';
                        fetch("{{ route('cashier.submit') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify(orderData)
                            })
                            .then(response => response.json()
                                .then(data => ({
                                    data,
                                    status: response.status,
                                    ok: response.ok
                                }))
                            )
                            .then(res => {
                                const {
                                    data,
                                    ok
                                } = res;
                                if (ok && orderData.transaction_type === 'complimentary') {
                                    // Jika complimentary, munculkan swal langsung dari json dari backend
                                    Swal.fire({
                                        icon: data.swal_icon || 'success',
                                        title: data.swal_title || 'Berhasil!',
                                        text: data.swal_text || data.message ||
                                            'Transaksi complimentary berhasil diproses!',
                                        timer: data.swal_timer || 2000,
                                        showConfirmButton: false
                                    });
                                    cart = [];
                                    updateCartDisplay();
                                    form.reset();
                                    const modalCheckout = document.getElementById('modalCheckout');
                                    if (modalCheckout) {
                                        const modalInstance = bootstrap.Modal.getInstance(
                                        modalCheckout);
                                        if (modalInstance) {
                                            modalInstance.hide();
                                        }
                                    }
                                } else if (ok) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message ||
                                            'Transaksi berhasil diproses!',
                                        timer: 1800,
                                        showConfirmButton: false
                                    });
                                    cart = [];
                                    updateCartDisplay();
                                    form.reset();
                                    const modalCheckout = document.getElementById('modalCheckout');
                                    if (modalCheckout) {
                                        const modalInstance = bootstrap.Modal.getInstance(
                                        modalCheckout);
                                        if (modalInstance) {
                                            modalInstance.hide();
                                        }
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: (data && data.message) ||
                                            'Terjadi kesalahan saat memproses transaksi.',
                                    });
                                }
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi kesalahan!',
                                    text: 'Tidak dapat menyimpan transaksi.',
                                });
                                console.error(error);
                            });
                    }
                });
            });
        }

        const searchInput = document.getElementById('searchProduct');
        const searchContainer = document.querySelector('.pos-search-container');
        const resultsContainer = document.getElementById('searchResultsContainer');

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();

            if (!searchTerm) {
                resultsContainer.style.display = 'none';
                return;
            }

            const filteredItems = menuItemsData.filter(item =>
                item.name.toLowerCase().includes(searchTerm)
            );

            if (filteredItems.length > 0) {
                resultsContainer.innerHTML = filteredItems.map(item => `
                <div class="search-result-item" 
                     data-id="${item.id}" 
                     data-name="${item.name}" 
                     data-price="${item.price}" 
                     data-stock="${item.calculated_stock}">
                    <span>${item.name}</span>
                    <span class="price">${formatCurrency(item.price)}</span>
                </div>
            `).join('');
            } else {
                resultsContainer.innerHTML =
                    '<div class="search-no-results">Produk tidak ditemukan</div>';
            }

            resultsContainer.style.display = 'block';
        });

        document.addEventListener('click', function(e) {
            if (!searchContainer.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        });

        resultsContainer.addEventListener('click', function(e) {
            const itemElement = e.target.closest('.search-result-item');
            if (itemElement) {
                const productId = itemElement.dataset.id;
                const product = menuItemsData.find(item => item.id == productId);

                if (product) {
                    if (product.calculated_stock > 0) {
                        resultsContainer.style.display = 'none';
                        searchInput.value = '';
                        currentProduct = product;
                        openCustomizeModal(product);
                    } else {
                        Swal.fire('Stok Habis', 'Stok produk ini sudah habis.', 'warning');
                    }
                }
            }
        });
    </script>
@endpush
