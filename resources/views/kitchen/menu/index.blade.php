@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-box"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Manajemen Menu</h1>
                            <p class="page-subtitle">Kelola data menu dan stok bahan baku</p>
                        </div>
                    </div>
                    <div class="d-inline-flex gap-2">
                        <button class="btn-add-primary" id="btnTambahMenu">
                            <i class="bi bi-plus-circle"></i>
                            <span>Tambah Menu</span>
                        </button>
                        <button class="btn btn-primary" id="btnKelolaPilihan">
                            <i class="bi bi-ui-checks-grid"></i> <span>Kelola Pilihan Tambahan</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-layers"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $menus->count() }}</h3>
                        <p class="stat-label">Total Menu</p>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">Rp
                            {{ number_format($menus->sum(fn($m) => $m->getCostPrice()), 0, ',', '.') }}</h3>
                        <p class="stat-label">Total Nilai HPP Menu</p>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Menu</span>
                    </div>
                    <div class="data-card-actions">
                        <form method="GET" action="{{ route('kitchen.menu.index') }}" class="d-flex gap-2">

                            {{-- Filter Kategori --}}
                            <select name="category" class="form-select form-select-sm select2" onchange="this.form.submit()"
                                style="min-width: 180px;">
                                <option value="">Semua Kategori</option>
                                @foreach ($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        {{ request('category') == $kategori->id ? 'selected' : '' }}>
                                        {{ $kategori->name }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- Filter Pencarian --}}
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" name="search" placeholder="Cari menu..."
                                    value="{{ request('search') }}">
                            </div>

                            {{-- Tombol untuk submit pencarian --}}
                            <button type="submit" class="btn btn-sm btn-light">Cari</button>
                            @if (request('search') || request('category'))
                                <a href="{{ route('kitchen.menu.index') }}"
                                    class="btn btn-sm btn-outline-secondary">Reset</a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-menu"
                            data-url="{{ route('kitchen.menu.destroy', ['menu' => 0]) }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama Menu</th>
                                    <th class="col-secondary">Kategori</th>
                                    <th class="col-currency">Harga Jual</th>
                                    <th class="col-currency">HPP</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($menus as $key => $menu)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span class="item-name">{{ $menu->name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-secondary">
                                            <span
                                                class="badge-unit">{{ $menu->menuCategory->name ?? $menu->category }}</span>
                                        </td>
                                        <td class="col-currency">
                                            <span class="price-value">Rp
                                                {{ number_format($menu->price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="col-currency">
                                            <span class="price-value">Rp
                                                {{ number_format($menu->getCostPrice(), 0, ',', '.') }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditMenu"
                                                    data-id="{{ $menu->id }}"
                                                    data-name="{{ $menu->name }}"
                                                    data-category_id="{{ $menu->menu_category_id }}"
                                                    data-price="{{ $menu->price }}"
                                                    data-description="{{ $menu->description }}"
                                                    data-ingredients='{{ json_encode($menu->ingredients->map(fn($i) => ['id' => $i->id, 'quantity' => $i->pivot->quantity])) }}'
                                                    data-modifier-groups='{{ json_encode($menu->modifierGroups->pluck('id')) }}'
                                                    data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusMenu"
                                                    data-id="{{ $menu->id }}" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-box"></i>
                                                <h4>Belum ada data menu</h4>
                                                <p>Klik tombol "Tambah Menu" untuk memulai</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal fade" id="modalMenu" tabindex="-1" aria-labelledby="modalMenuLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <form id="formMenu" data-url="{{ route('kitchen.menu.submit') }}">
                    @csrf
                    <input type="hidden" name="id" id="menu_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon">
                                <i class="bi bi-box"></i>
                            </div>
                            <h5 class="modal-title" id="modalMenuLabel">Tambah Menu</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formMenuAlert"></div>
                        <div class="form-group-custom">
                            <label for="menu_name" class="form-label-custom">
                                <i class="bi bi-box"></i> Nama Menu
                            </label>
                            <input type="text" class="form-control-custom" id="menu_name" name="name"
                                placeholder="Contoh: Nasi Goreng" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group-custom">
                                <label for="menu_category_id" class="form-label-custom">
                                    <i class="bi bi-tags"></i> Kategori
                                </label>
                                <select class="form-control-custom" id="menu_category_id" name="category_id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group-custom">
                                <label for="menu_price" class="form-label-custom">
                                    <i class="bi bi-cash-stack"></i> Harga Jual
                                </label>
                                <input type="number" step="0.01" min="0" class="form-control-custom"
                                    id="menu_price" name="price" placeholder="0" required>
                            </div>
                        </div>
                        <div class="form-group-custom">
                            <label for="menu_description" class="form-label-custom">
                                <i class="bi bi-text-paragraph"></i> Deskripsi
                            </label>
                            <textarea class="form-control-custom" id="menu_description" name="description"
                                placeholder="Deskripsi menu (opsional)"></textarea>
                        </div>
                        <div class="form-group-custom">
                            <label class="form-label-custom">
                                <i class="bi bi-list-check"></i> Bahan Baku yang Diperlukan
                            </label>
                            <div id="ingredients-container">
                                <!-- Dynamic rows will be added here -->
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="btnAddIngredient">
                                <i class="bi bi-plus-circle"></i> Tambah Bahan
                            </button>
                        </div>
                        <hr>
                        <div class="form-group-custom">
                            <label class="form-label-custom">
                                <i class="bi bi-ui-checks-grid"></i> Grup Pilihan Tambahan
                            </label>
                            <div id="modifier-groups-container" class="row">
                                @forelse ($modifierGroups as $group)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="modifier_groups[]"
                                                value="{{ $group->id }}" id="modifier-group-{{ $group->id }}">
                                            <label class="form-check-label" for="modifier-group-{{ $group->id }}">
                                                {{ $group->name }}
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted">Belum ada grup pilihan tambahan. Buat terlebih dahulu di "Kelola
                                        Pilihan Tambahan".</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i> Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanMenu">
                            <i class="bi bi-check"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalKelolaPilihan" tabindex="-1" aria-labelledby="modalKelolaPilihanLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKelolaPilihanLabel"><i class="bi bi-ui-checks-grid"></i> Kelola
                        Pilihan Tambahan (Modifiers)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary" id="btnTambahGroupModal">
                            <i class="bi bi-plus-circle"></i> Tambah Grup Pilihan
                        </button>
                    </div>

                    <div class="row g-3">
                        @forelse($modifierGroups as $group)
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">{{ $group->name }}</h6>
                                            <small class="text-muted">Pilihan:
                                                {{ $group->selection_type == 'single' ? 'Tunggal' : 'Ganda' }}</small>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary btnEditGroup"
                                                data-id="{{ $group->id }}" data-name="{{ $group->name }}"
                                                data-selection_type="{{ $group->selection_type }}"
                                                data-action="{{ route('kitchen.modifier-groups.update', $group->id) }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btnHapus"
                                                data-url="{{ route('kitchen.modifier-groups.destroy', $group->id) }}"
                                                data-label="Grup Pilihan">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        @forelse($group->modifiers as $modifier)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    {{ $modifier->name }} <span class="text-success">(+Rp
                                                        {{ number_format($modifier->price) }})</span>
                                                    @if ($modifier->ingredient)
                                                        <br><small class="text-info"><i class="bi bi-box-seam"></i>
                                                            {{ $modifier->ingredient->name }}
                                                            ({{ $modifier->quantity_used }}
                                                            {{ $modifier->ingredient->unit }})
                                                        </small>
                                                    @endif
                                                </div>
                                                <div>
                                                    <button
                                                        class="btn btn-sm btn-link text-secondary py-0 px-1 btnEditModifier"
                                                        data-id="{{ $modifier->id }}" data-name="{{ $modifier->name }}"
                                                        data-price="{{ $modifier->price }}"
                                                        data-ingredient_id="{{ $modifier->ingredient_id }}"
                                                        data-quantity_used="{{ $modifier->quantity_used }}"
                                                        data-action="{{ route('kitchen.modifiers.update', $modifier->id) }}">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-link text-danger py-0 px-1 btnHapus"
                                                        data-url="{{ route('kitchen.modifiers.destroy', $modifier->id) }}"
                                                        data-label="Pilihan">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="list-group-item text-center text-muted">Belum ada pilihan.</li>
                                        @endforelse
                                    </ul>
                                    <div class="card-footer">
                                        <button class="btn btn-outline-primary btn-sm w-100 btnTambahModifierModal"
                                            data-group-id="{{ $group->id }}">
                                            <i class="bi bi-plus"></i> Tambah Pilihan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center p-5">
                                <p class="text-muted">Belum ada grup pilihan. Klik "Tambah Grup Pilihan" untuk memulai.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGroupForm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formGroup" action="" method="POST">
                    @csrf
                    <div id="methodGroup"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalGroupLabel">...</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="group_name" class="form-label">Nama Grup</label>
                            <input type="text" class="form-control" id="group_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="group_selection_type" class="form-label">Tipe Pilihan</label>
                            <select class="form-select" id="group_selection_type" name="selection_type" required>
                                <option value="multiple">Bisa Pilih Banyak (Contoh: Topping)</option>
                                <option value="single">Hanya Bisa Pilih Satu (Contoh: Level Pedas)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Grup</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalModifierForm" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="formModifier" action="" method="POST">
                    @csrf
                    <div id="methodModifier"></div>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalModifierLabel">...</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="modifier_name" class="form-label">Nama Pilihan</label>
                            <input type="text" class="form-control" id="modifier_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="modifier_price" class="form-label">Harga Tambahan</label>
                            <input type="number" class="form-control" id="modifier_price" name="price" required
                                placeholder="0">
                        </div>
                        <hr>
                        <p class="text-muted small">Opsional: Jika pilihan ini mengurangi stok bahan baku.</p>
                        <div class="mb-3">
                            <label for="modifier_ingredient_id" class="form-label">Bahan Baku Terpakai</label>
                            <select class="form-select" id="modifier_ingredient_id" name="ingredient_id">
                                <option value="">-- Tidak mengurangi stok --</option>
                                @foreach ($bahanbakus as $bahan)
                                    <option value="{{ $bahan->id }}">{{ $bahan->name }} ({{ $bahan->unit }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modifier_quantity_used" class="form-label">Jumlah Terpakai per Pilihan</label>
                            <input type="number" step="0.01" class="form-control" id="modifier_quantity_used"
                                name="quantity_used" placeholder="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pilihan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        const bahanbakus = @json($bahanbakus->map(fn($b) => ['id' => $b->id, 'name' => $b->name]));
        console.log('bahanbakus:', bahanbakus);

        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality (sama seperti sebelumnya)
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#tabel-menu tbody tr.data-row');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }

            // Fungsi untuk tambah row ingredient
            function addIngredientRow(id = '', quantity = '') {
                const container = document.getElementById('ingredients-container');
                const index = container.children.length;
                const row = document.createElement('div');
                row.className = 'ingredient-row d-flex align-items-center mb-2';
                row.innerHTML = `
                    <select name="ingredients[${index}][id]" class="form-control me-2" required>
                        <option value="">Pilih Bahan</option>
                        ${bahanbakus.map(b => `<option value="${b.id}" ${b.id == id ? 'selected' : ''}>${b.name}</option>`).join('')}
                    </select>
                    <input type="number" name="ingredients[${index}][quantity]" value="${quantity}" step="0.01" min="0.01" class="form-control me-2" placeholder="Quantity" required>
                    <button type="button" class="btn btn-danger btn-sm remove-ingredient"><i class="bi bi-trash"></i></button>
                `;
                container.appendChild(row);

                // Event remove
                row.querySelector('.remove-ingredient').addEventListener('click', function() {
                    row.remove();
                    // Reindex names
                    Array.from(container.children).forEach((r, i) => {
                        r.querySelector('select').name = `ingredients[${i}][id]`;
                        r.querySelector('input').name = `ingredients[${i}][quantity]`;
                    });
                });
            }

            // Tombol Tambah Ingredient
            document.getElementById('btnAddIngredient').addEventListener('click', () => addIngredientRow());

            // Tombol Tambah Menu
            document.getElementById('btnTambahMenu').addEventListener('click', function() {
                document.getElementById('modalMenuLabel').textContent = 'Tambah Menu';
                document.querySelector('.modal-icon i').className = 'bi bi-plus-circle';
                document.getElementById('formMenu').reset();
                document.getElementById('menu_id').value = '';
                document.getElementById('formMenuAlert').innerHTML = '';
                document.getElementById('ingredients-container').innerHTML = '';
                addIngredientRow(); // Tambah satu row default
                var modal = new bootstrap.Modal(document.getElementById('modalMenu'));
                modal.show();
            });

            // Tombol Edit Menu
            document.getElementById('tabel-menu').addEventListener('click', function(e) {
                if (e.target.closest('.btnEditMenu')) {
                    let btn = e.target.closest('.btnEditMenu');
                    let id = btn.getAttribute('data-id');
                    let name = btn.getAttribute('data-name');
                    let category_id = btn.getAttribute('data-category_id');
                    let price = btn.getAttribute('data-price');
                    let description = btn.getAttribute('data-description');
                    let ingredients = JSON.parse(btn.getAttribute('data-ingredients') || '[]');
                    let attachedGroups = JSON.parse(btn.getAttribute('data-modifier-groups') || '[]');

                    document.getElementById('modalMenuLabel').textContent = 'Edit Menu';
                    document.querySelector('.modal-icon i').className = 'bi bi-pencil-square';
                    document.getElementById('menu_id').value = id;
                    document.getElementById('menu_name').value = name;
                    document.getElementById('menu_category_id').value = category_id;
                    document.getElementById('menu_price').value = price;
                    document.getElementById('menu_description').value = description;
                    document.getElementById('formMenuAlert').innerHTML = '';
                    document.getElementById('ingredients-container').innerHTML = '';

                    if (ingredients.length === 0) {
                        addIngredientRow();
                    } else {
                        ingredients.forEach(ing => addIngredientRow(ing.id, ing.quantity));
                    }

                    // 1. Reset semua checkbox terlebih dahulu
                    document.querySelectorAll('#modifier-groups-container input[type="checkbox"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });

                    // 2. Centang checkbox yang ID-nya ada di `attachedGroups`
                    if (attachedGroups.length > 0) {
                        attachedGroups.forEach(groupId => {
                            const checkbox = document.getElementById(`modifier-group-${groupId}`);
                            if (checkbox) {
                                checkbox.checked = true;
                            }
                        });
                    }

                    // Tampilkan modal
                    var modal = new bootstrap.Modal(document.getElementById('modalMenu'));
                    modal.show();
                }
            });

            // Submit Form (sama seperti sebelumnya, tapi sudah handle array ingredients)
            document.getElementById('formMenu').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const url = form.getAttribute('data-url');
                const btn = document.getElementById('btnSimpanMenu');
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

                showLoading('Menyimpan data menu...');

                const formData = new FormData(form);

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        hideLoading();
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check"></i> Simpan';

                        let data;
                        try {
                            data = await response.json();
                        } catch (err) {
                            data = {
                                status: 'error',
                                message: 'Gagal parsing response server.'
                            };
                        }

                        if (response.ok && data.status !== 'error') {
                            Swal.fire('Berhasil', data.message, 'success').then(() => location
                                .reload());
                        } else {
                            let pesan = 'Silakan periksa kembali isian Anda.';
                            if (data.errors) {
                                pesan = Object.values(data.errors).map(arr => arr[0]).join('<br>');
                            } else if (data.message) {
                                pesan = data.message;
                            }
                            document.getElementById('formMenuAlert').innerHTML =
                                '<div class="alert-custom alert-danger"><i class="bi bi-exclamation-circle"></i>' +
                                pesan + '</div>';
                            Swal.fire('Gagal', pesan, 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-check"></i> Simpan';
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengirim data.', 'error');
                    });
            });

            // Tombol Hapus Menu (sama seperti sebelumnya)
            document.getElementById('tabel-menu').addEventListener('click', function(e) {
                if (e.target.closest('.btnHapusMenu')) {
                    let btn = e.target.closest('.btnHapusMenu');
                    let id = btn.getAttribute('data-id');
                    let url = document.getElementById('tabel-menu').getAttribute('data-url').replace(/0$/,
                        id);

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Data menu ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showLoading('Menghapus data menu...');
                            fetch(url, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(async response => {
                                    hideLoading();
                                    let data;
                                    try {
                                        data = await response.json();
                                    } catch (err) {
                                        data = {
                                            status: 'error',
                                            message: 'Gagal parsing response server.'
                                        };
                                    }
                                    if (response.ok && data.status !== 'error') {
                                        Swal.fire('Terhapus!', data.message, 'success')
                                            .then(() => location.reload());
                                    } else {
                                        Swal.fire('Gagal', data.message ||
                                            'Terjadi kesalahan saat menghapus data.',
                                            'error');
                                    }
                                })
                                .catch(error => {
                                    hideLoading();
                                    Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.',
                                        'error');
                                });
                        }
                    });
                }
            });

            const modalKelola = new bootstrap.Modal(document.getElementById('modalKelolaPilihan'));
            const modalGroup = new bootstrap.Modal(document.getElementById('modalGroupForm'));
            const formGroup = document.getElementById('formGroup');
            const modalModifier = new bootstrap.Modal(document.getElementById('modalModifierForm'));
            const formModifier = document.getElementById('formModifier');

            // 1. Buka Modal Utama
            // 1. Buka Modal Utama
            document.getElementById('btnKelolaPilihan').addEventListener('click', () => modalKelola.show());

            // 2. Buka form Tambah Grup
            document.getElementById('btnTambahGroupModal').addEventListener('click', function() {
                formGroup.action = "{{ route('kitchen.modifier-groups.store') }}";
                formGroup.querySelector('#methodGroup').innerHTML = '';
                document.getElementById('modalGroupLabel').textContent = 'Tambah Grup Pilihan Baru';
                formGroup.reset();
                modalGroup.show();
            });

            // 3. Buka form Edit Grup
            document.querySelectorAll('.btnEditGroup').forEach(btn => {
                btn.addEventListener('click', function() {
                    formGroup.action = this.dataset.action;
                    formGroup.querySelector('#methodGroup').innerHTML =
                        '<input type="hidden" name="_method" value="PUT">';
                    document.getElementById('modalGroupLabel').textContent = 'Edit Grup Pilihan';
                    document.getElementById('group_name').value = this.dataset.name;
                    document.getElementById('group_selection_type').value = this.dataset
                        .selection_type;
                    modalGroup.show();
                });
            });

            // 4. Buka form Tambah Pilihan (Modifier)
            document.querySelectorAll('.btnTambahModifierModal').forEach(btn => {
                btn.addEventListener('click', function() {
                    const groupId = this.dataset.groupId;
                    const actionUrl = `{{ url('kitchen/modifier-groups') }}/${groupId}/modifiers`;
                    formModifier.action = actionUrl;
                    formModifier.querySelector('#methodModifier').innerHTML = '';
                    document.getElementById('modalModifierLabel').textContent =
                        'Tambah Pilihan Baru';
                    formModifier.reset();
                    modalModifier.show();
                });
            });

            // 5. Buka form Edit Pilihan (Modifier)
            document.querySelectorAll('.btnEditModifier').forEach(btn => {
                btn.addEventListener('click', function() {
                    formModifier.action = this.dataset.action;
                    formModifier.querySelector('#methodModifier').innerHTML =
                        '<input type="hidden" name="_method" value="PUT">';
                    document.getElementById('modalModifierLabel').textContent = 'Edit Pilihan';
                    formModifier.reset();
                    document.getElementById('modifier_name').value = this.dataset.name;
                    document.getElementById('modifier_price').value = this.dataset.price;
                    document.getElementById('modifier_ingredient_id').value = this.dataset
                        .ingredient_id;
                    document.getElementById('modifier_quantity_used').value = this.dataset
                        .quantity_used;
                    modalModifier.show();
                });
            });

            // 6. Handler submit form pakai fetch dan swal
            function handleFormSubmit(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Konfirmasi submit pakai swal
                    Swal.fire({
                        title: 'Apakah data sudah benar?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, simpan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        const url = form.action;
                        const method = form.querySelector('input[name="_method"]')?.value || 'POST';
                        const submitButton = form.querySelector('button[type="submit"]');
                        const originalButtonHtml = submitButton.innerHTML;

                        submitButton.disabled = true;
                        submitButton.innerHTML =
                            `<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...`;

                        fetch(url, {
                                method: 'POST', // method tetap POST, untuk PUT/Hapus pakai _method
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: new FormData(form)
                            })
                            .then(async response => {
                                let data;
                                try {
                                    data = await response.json();
                                } catch (err) {
                                    data = {
                                        status: 'error',
                                        message: 'Gagal parsing respon server.'
                                    };
                                }
                                if (response.ok && (!data.status || data.status !==
                                    'error')) {
                                    return Swal.fire('Berhasil!', data.message ||
                                            'Data berhasil disimpan.', 'success')
                                        .then(() => location.reload());
                                } else {
                                    let errorMessage = data.message || 'Terjadi kesalahan.';
                                    if (data.errors) errorMessage = Object.values(data
                                        .errors).map(e => e[0]).join('<br>');
                                    return Swal.fire('Gagal!', errorMessage, 'error');
                                }
                            })
                            .catch(() => {
                                Swal.fire('Gagal!', 'Terjadi kesalahan koneksi server.',
                                    'error');
                            })
                            .finally(() => {
                                submitButton.disabled = false;
                                submitButton.innerHTML = originalButtonHtml;
                            });
                    });
                });
            }

            // Terapkan handler ke kedua form
            handleFormSubmit(formGroup);
            handleFormSubmit(formModifier);

            // 7. Handler tombol hapus pakai swal dan fetch
            document.querySelectorAll('.btnHapus').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.dataset.url;
                    const label = this.dataset.label;

                    Swal.fire({
                        title: `Yakin ingin menghapus ${label} ini?`,
                        text: "Data akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: `Menghapus ${label}...`,
                                didOpen: () => {
                                    Swal.showLoading()
                                },
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false
                            });

                            fetch(url, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(async response => {
                                    let data;
                                    try {
                                        data = await response.json();
                                    } catch (err) {
                                        data = {
                                            status: 'error',
                                            message: 'Gagal parsing respon server.'
                                        };
                                    }
                                    if (response.ok && (!data.status || data
                                            .status !== 'error')) {
                                        return Swal.fire('Terhapus!', data
                                                .message ||
                                                `${label} berhasil dihapus.`,
                                                'success')
                                            .then(() => location.reload());
                                    } else {
                                        return Swal.fire('Gagal!', data.message ||
                                            'Terjadi kesalahan saat menghapus.',
                                            'error');
                                    }
                                })
                                .catch(() => {
                                    Swal.fire('Gagal!',
                                        'Terjadi kesalahan koneksi server.', 'error'
                                        );
                                });
                        }
                    });
                });
            });
        });
    </script>
@endpush
