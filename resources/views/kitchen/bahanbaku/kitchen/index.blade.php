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
                            <h1 class="page-title">Manajemen Bahan Baku</h1>
                            <p class="page-subtitle">Kelola data bahan baku dan stok kitchen</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahBahan">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Bahan Baku</span>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-layers"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $bahanbakus->count() }}</h3>
                        <p class="stat-label">Total Bahan</p>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">Rp
                            {{ number_format($bahanbakus->sum(function ($b) {return $b->stock * $b->cost_price;}),0,',','.') }}
                        </h3>
                        <p class="stat-label">Total Nilai Stok</p>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Bahan Baku</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari bahan baku..." id="searchInput">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-bahanbaku"
                            data-url="{{ route('kitchen.bahanbaku.destroy', ['bahanbaku' => 0]) }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama Bahan</th>
                                    <th class="col-secondary">Satuan</th>
                                    <th class="col-secondary">Supplier</th>
                                    <th class="col-secondary">Status Stok</th>
                                    <th class="col-currency">Harga Beli/Unit</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bahanbakus as $key => $bahan)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span class="item-name">{{ $bahan->name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $bahan->unit }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="item-supplier">{{ $bahan->supplier->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            @if ($bahan->stock <= 0)
                                                <span class="badge-status badge-danger">Habis</span>
                                            @elseif ($bahan->stock <= $bahan->minimum_stock)
                                                <span class="badge-status badge-warning">Menipis</span>
                                            @else
                                                <span class="badge-status badge-success">Aman</span>
                                            @endif
                                            <span class="stock-value">{{ $bahan->stock }} {{ $bahan->unit }}</span>
                                        </td>
                                        <td class="col-currency">
                                            <span class="price-value">Rp
                                                {{ number_format($bahan->cost_price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditBahan"
                                                    data-id="{{ $bahan->id }}"
                                                    data-name="{{ $bahan->name }}"
                                                    data-cost_price="{{ $bahan->cost_price }}"
                                                    data-unit="{{ $bahan->unit }}"
                                                    data-supplier_id="{{ $bahan->supplier_id }}"
                                                    data-minimum_stock="{{ $bahan->minimum_stock }}"
                                                    data-stock="{{ $bahan->stock }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusBahan"
                                                    data-id="{{ $bahan->id }}"
                                                    data-bs-toggle="tooltip" title="Hapus">
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
                                                <h4>Belum ada data bahan baku</h4>
                                                <p>Klik tombol "Tambah Bahan Baku" untuk memulai</p>
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
    <div class="modal fade" id="modalBahan" tabindex="-1" aria-labelledby="modalBahanLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formBahan" data-url="{{ route('kitchen.bahanbaku.submit') }}">
                    @csrf
                    <input type="hidden" name="id" id="bahan_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon">
                                <i class="bi bi-box"></i>
                            </div>
                            <h5 class="modal-title" id="modalBahanLabel">Tambah Bahan Baku</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formBahanAlert"></div>
                        <div class="form-group-custom">
                            <label for="bahan_name" class="form-label-custom">
                                <i class="bi bi-box"></i>
                                Nama Bahan
                            </label>
                            <input type="text" class="form-control-custom" id="bahan_name" name="name"
                                placeholder="Contoh: Tepung Terigu" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group-custom">
                                <label for="bahan_unit" class="form-label-custom">
                                    <i class="bi bi-rulers"></i>
                                    Satuan
                                </label>
                                <input type="text" class="form-control-custom" id="bahan_unit" name="unit"
                                    placeholder="Contoh: Kg" required>
                            </div>
                        </div>
                        <div class="form-group-custom">
                            <label for="bahan_supplier_id" class="form-label-custom">
                                <i class="bi bi-truck"></i> Supplier
                            </label>
                            <select class="form-control-custom" id="bahan_supplier_id" name="supplier_id" required>
                                <option value="" disabled selected>-- Pilih Supplier --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group-custom">
                            <label for="bahan_cost_price" class="form-label-custom">
                                <i class="bi bi-cash-stack"></i>
                                Harga Beli/Unit
                            </label>
                            <input type="number" step="0.01" min="0" class="form-control-custom"
                                id="bahan_cost_price" name="cost_price" placeholder="0" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="bahan_minimum_stock" class="form-label-custom">
                               <i class="bi bi-exclamation-triangle"></i> Stok Minimum
                           </label>
                           <input type="number" step="0.01" min="0" class="form-control-custom"
                                  id="bahan_minimum_stock" name="minimum_stock" placeholder="0" required>
                       </div>
                    </div>
                    <input type="hidden" name="category" value="kitchen">
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanBahan">
                            <i class="bi bi-check"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#tabel-bahanbaku tbody tr.data-row');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }

            // Tombol Tambah Bahan
            document.getElementById('btnTambahBahan').addEventListener('click', function() {
                document.getElementById('modalBahanLabel').textContent = 'Tambah Bahan Baku';
                document.querySelector('.modal-icon i').className = 'bi bi-plus-circle';
                document.getElementById('formBahan').reset();
                document.getElementById('bahan_id').value = '';
                document.getElementById('formBahanAlert').innerHTML = '';
                var modal = new bootstrap.Modal(document.getElementById('modalBahan'));
                modal.show();
            });

            // Tombol Edit Bahan
            document.getElementById('tabel-bahanbaku').addEventListener('click', function(e) {
                if (e.target.closest('.btnEditBahan')) {
                    withAuth(function() {
                        let btn = e.target.closest('.btnEditBahan');
                        let id = btn.getAttribute('data-id');
                        let name = btn.getAttribute('data-name');
                        let unit = btn.getAttribute('data-unit');
                        let cost_price = btn.getAttribute('data-cost_price');
                        let supplier_id = btn.getAttribute('data-supplier_id');
                        let minimum_stock = btn.getAttribute('data-minimum_stock');

                        document.getElementById('modalBahanLabel').textContent = 'Edit Bahan Baku';
                        document.querySelector('.modal-icon i').className = 'bi bi-pencil-square';
                        document.getElementById('bahan_id').value = id;
                        document.getElementById('bahan_name').value = name;
                        document.getElementById('bahan_unit').value = unit;
                        document.getElementById('bahan_cost_price').value = cost_price;
                        document.getElementById('bahan_supplier_id').value = supplier_id;
                        document.getElementById('bahan_minimum_stock').value = minimum_stock;
                        document.getElementById('formBahanAlert').innerHTML = '';

                        var modal = new bootstrap.Modal(document.getElementById('modalBahan'));
                        modal.show();
                    });
                }
            });

            // Submit Form (Tambah & Edit)
            document.getElementById('formBahan').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const url = form.getAttribute('data-url');
                const btn = document.getElementById('btnSimpanBahan');
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

                showLoading('Menyimpan data bahan baku...');

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
                            Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                        } else {
                            let pesan = 'Silakan periksa kembali isian Anda.';
                            if (data.errors) {
                                pesan = Object.values(data.errors).map(arr => arr[0]).join('<br>');
                            } else if (data.message) {
                                pesan = data.message;
                            }
                            document.getElementById('formBahanAlert').innerHTML =
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

            // Tombol Hapus Bahan
            document.getElementById('tabel-bahanbaku').addEventListener('click', function(e) {
                if (e.target.closest('.btnHapusBahan')) {
                    withAuth(function() {
                        let btn = e.target.closest('.btnHapusBahan');
                        let id = btn.getAttribute('data-id');
                        let url = document.getElementById('tabel-bahanbaku').getAttribute('data-url').replace(
                            /0$/, id);

                        Swal.fire({
                            title: 'Yakin ingin menghapus?',
                            text: "Data bahan baku ini akan dihapus permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonText: 'Batal',
                            confirmButtonText: 'Ya, hapus!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showLoading('Menghapus data bahan baku...');
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
                    });
                }
            });
        });
    </script>
@endpush
