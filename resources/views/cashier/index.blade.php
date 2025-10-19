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
            border-radius: 0 0 12px 12px;
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
                        <div class="pos-search-container">
                            <div class="pos-search-wrapper">
                                <div class="search-box">
                                    <i class="bi bi-search"></i>
                                    <input type="text" placeholder="Cari produk..." id="searchProduct"
                                        autocomplete="off">
                                </div>
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
    </form>
@endsection
@push('script')
    <script>
        const menuItemsData = @json($menuItemsWithStock);
        let cart = [];
        let currentProduct = null;

        const formatCurrency = (value) => 'Rp ' + (value || 0).toLocaleString('id-ID');

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
                        const inputType = group.selection_type === 'single' ? 'radio' : 'checkbox';
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
                container.innerHTML = '<p class="text-muted text-center">Tidak ada pilihan tambahan untuk item ini.</p>';
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

            const cartItemId = currentProduct.id + '-' + selectedModifierIds.sort().join('-') + (notes ? '-' + btoa(
                notes) : '');

            const existingItem = cart.find(item => item.cartItemId === cartItemId);

            if (existingItem) {
                existingItem.quantity += qty; // Tetap gunakan qty, sesuai logic frontend
            } else {
                cart.push({
                    cartItemId: cartItemId,
                    menu_item_id: currentProduct.id,
                    name: currentProduct.name,
                    price: currentProduct.price,
                    quantity: qty, // Ganti 'qty' menjadi 'quantity' di field ini
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
                    const modifiersPrice = item.modifiers.reduce((sum, mod) => sum + parseFloat(mod.price), 0);
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

        document.getElementById('btnCheckout').addEventListener('click', function() {
            document.getElementById('cartDataInput').value = JSON.stringify(cart);
            document.getElementById('formToPayment').submit();
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
                const formattedItems = cart.map(item => ({
                    menu_item_id: item.menu_item_id, // Pastikan ini yang dikirim, bukan `id`
                    quantity: item.qty,
                    modifiers: item.modifier_ids,
                    notes: item.notes
                }));

                const payments = [{
                    method: formData.get('payment_method'),
                    amount: cart.reduce((sum, item) => {
                        const modifiersPrice = item.modifiers.reduce((s, mod) => s + parseFloat(mod
                            .price), 0);
                        return sum + ((item.price + modifiersPrice) * item.qty);
                    }, 0)
                }];

                const orderData = {
                    items: formattedItems,
                    table_number: formData.get('table_number'),
                    customer_name: formData.get('customer_name'),
                    order_type: formData.get('order_type'),
                    notes: formData.get('notes'),
                    payments: payments,
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
                        // DO: POST to backend
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
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: data.message || 'Transaksi berhasil diproses!',
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
                                        text: data.message ||
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
                resultsContainer.innerHTML = '<div class="search-no-results">Produk tidak ditemukan</div>';
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
                    // Cek stok sebelum buka modal
                    if (product.calculated_stock > 0) {
                        resultsContainer.style.display = 'none';
                        searchInput.value = ''; // Kosongkan input
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
