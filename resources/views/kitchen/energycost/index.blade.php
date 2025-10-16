@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Manajemen Biaya Energi</h1>
                            <p class="page-subtitle">Kelola data biaya energi dapur</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahEnergyCost">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Biaya Energi</span>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-lightning"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $energycosts->count() }}</h3>
                        <p class="stat-label">Total Data Energi</p>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-icon">
                        <i class="bi bi-cash"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">Rp
                            {{ number_format($energycosts->sum('cost'), 0, ',', '.') }}</h3>
                        <p class="stat-label">Total Biaya Energi</p>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Biaya Energi</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari energi..." id="searchInput">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-energycost"
                            data-url="{{ route('kitchen.energycost.destroy', ['energycost' => 0]) }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama Energi</th>
                                    <th class="col-currency">Biaya</th>
                                    <th class="col-date">Periode</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($energycosts as $key => $energycost)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span class="item-name">{{ $energycost->name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-currency">
                                            <span class="price-value">Rp
                                                {{ number_format($energycost->cost, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="col-date">
                                            <span class="date-value">{{ \Carbon\Carbon::parse($energycost->period)->format('d-m-Y') }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditEnergyCost"
                                                    data-id="{{ $energycost->id }}" data-name="{{ $energycost->name }}"
                                                    data-cost="{{ $energycost->cost }}"
                                                    data-period="{{ $energycost->period }}"
                                                    data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusEnergyCost"
                                                    data-id="{{ $energycost->id }}" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-lightning-charge"></i>
                                                <h4>Belum ada data biaya energi</h4>
                                                <p>Klik tombol "Tambah Biaya Energi" untuk memulai</p>
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
    <div class="modal fade" id="modalEnergyCost" tabindex="-1" aria-labelledby="modalEnergyCostLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <form id="formEnergyCost" data-url="{{ route('kitchen.energycost.submit') }}">
                    @csrf
                    <input type="hidden" name="id" id="energycost_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon">
                                <i class="bi bi-lightning-charge"></i>
                            </div>
                            <h5 class="modal-title" id="modalEnergyCostLabel">Tambah Biaya Energi</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formEnergyCostAlert"></div>
                        <div class="form-group-custom">
                            <label for="energycost_name" class="form-label-custom">
                                <i class="bi bi-lightning"></i> Nama Energi
                            </label>
                            <input type="text" class="form-control-custom" id="energycost_name" name="name"
                                placeholder="Contoh: Listrik PLN" required>
                        </div>
                        <div class="form-row">
                            {{-- Tipe Energi dihapus karena tidak ada di database dan controller --}}
                            <div class="form-group-custom">
                                <label for="energycost_cost" class="form-label-custom">
                                    <i class="bi bi-cash-stack"></i> Biaya
                                </label>
                                <input type="number" step="0.01" min="0" class="form-control-custom"
                                    id="energycost_cost" name="cost" placeholder="0" required>
                            </div>
                        </div>
                        <div class="form-group-custom">
                            <label for="energycost_period" class="form-label-custom">
                                <i class="bi bi-calendar"></i> Periode
                            </label>
                            <input type="date" class="form-control-custom" id="energycost_period" name="period" required>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i> Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanEnergyCost">
                            <i class="bi bi-check"></i> Simpan
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
                    const rows = document.querySelectorAll('#tabel-energycost tbody tr.data-row');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }

            // Tombol Tambah EnergyCost
            // Tombol Tambah EnergyCost
            document.getElementById('btnTambahEnergyCost').addEventListener('click', function() {
                document.getElementById('modalEnergyCostLabel').textContent = 'Tambah Biaya Energi';
                document.querySelector('.modal-icon i').className = 'bi bi-plus-circle';
                document.getElementById('formEnergyCost').reset();
                document.getElementById('energycost_id').value = '';
                document.getElementById('formEnergyCostAlert').innerHTML = '';
                var modal = new bootstrap.Modal(document.getElementById('modalEnergyCost'));
                modal.show();
            });

            // Tombol Edit EnergyCost
            document.getElementById('tabel-energycost').addEventListener('click', function(e) {
                if (e.target.closest('.btnEditEnergyCost')) {
                    let btn = e.target.closest('.btnEditEnergyCost');
                    let id = btn.getAttribute('data-id');
                    let name = btn.getAttribute('data-name');
                    let cost = btn.getAttribute('data-cost');
                    let period = btn.getAttribute('data-period');

                    document.getElementById('modalEnergyCostLabel').textContent = 'Edit Biaya Energi';
                    document.querySelector('.modal-icon i').className = 'bi bi-pencil-square';
                    document.getElementById('energycost_id').value = id;
                    document.getElementById('energycost_name').value = name;
                    document.getElementById('energycost_cost').value = cost;
                    document.getElementById('energycost_period').value = period;
                    document.getElementById('formEnergyCostAlert').innerHTML = '';

                    var modal = new bootstrap.Modal(document.getElementById('modalEnergyCost'));
                    modal.show();
                }
            });

            // Submit Form
            document.getElementById('formEnergyCost').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const url = form.getAttribute('data-url');
                const btn = document.getElementById('btnSimpanEnergyCost');
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

                showLoading('Menyimpan data biaya energi...');

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
                            document.getElementById('formEnergyCostAlert').innerHTML =
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

            // Tombol Hapus EnergyCost
            document.getElementById('tabel-energycost').addEventListener('click', function(e) {
                if (e.target.closest('.btnHapusEnergyCost')) {
                    let btn = e.target.closest('.btnHapusEnergyCost');
                    let id = btn.getAttribute('data-id');
                    let url = document.getElementById('tabel-energycost').getAttribute('data-url').replace(/0$/,
                        id);

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Data biaya energi ini akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showLoading('Menghapus data biaya energi...');
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
        });
    </script>
@endpush
