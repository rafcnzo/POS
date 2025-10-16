@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-tags"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Manajemen Kategori Menu</h1>
                            <p class="page-subtitle">Kelola data kategori menu makanan & minuman</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahKategori">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Kategori</span>
                    </button>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Kategori Menu</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari kategori..." id="searchInputKategori">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-kategori" data-url="{{ route('kitchen.kategori.destroy', ['kategori' => 0]) }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama Kategori</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoris as $key => $kategori)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span class="item-name">{{ $kategori->name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditKategori"
                                                    data-id="{{ $kategori->id }}"
                                                    data-name="{{ $kategori->name }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusKategori"
                                                    data-id="{{ $kategori->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-tags"></i>
                                                <h4>Belum ada data kategori</h4>
                                                <p>Klik tombol "Tambah Kategori" untuk memulai</p>
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
    <div class="modal fade" id="modalKategori" tabindex="-1" aria-labelledby="modalKategoriLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formKategori" data-url="{{ route('kitchen.kategori.submit') }}">
                    @csrf
                    <input type="hidden" name="id" id="kategori_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon">
                                <i class="bi bi-tags"></i>
                            </div>
                            <h5 class="modal-title" id="modalKategoriLabel">Tambah Kategori</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formKategoriAlert"></div>
                        <div class="form-group-custom">
                            <label for="kategori_name" class="form-label-custom">
                                <i class="bi bi-tag"></i>
                                Nama Kategori
                            </label>
                            <input type="text" class="form-control-custom" id="kategori_name" name="name" placeholder="Contoh: Makanan" required>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanKategori">
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
    const searchInput = document.getElementById('searchInputKategori');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tabel-kategori tbody tr.data-row');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Tombol Tambah Kategori
    document.getElementById('btnTambahKategori').addEventListener('click', function() {
        document.getElementById('modalKategoriLabel').textContent = 'Tambah Kategori';
        document.querySelector('#modalKategori .modal-icon i').className = 'bi bi-plus-circle';
        document.getElementById('formKategori').reset();
        document.getElementById('kategori_id').value = '';
        document.getElementById('formKategoriAlert').innerHTML = '';
        var modal = new bootstrap.Modal(document.getElementById('modalKategori'));
        modal.show();
    });

    // Tombol Edit Kategori
    document.getElementById('tabel-kategori').addEventListener('click', function(e) {
        if (e.target.closest('.btnEditKategori')) {
            let btn = e.target.closest('.btnEditKategori');
            let id = btn.getAttribute('data-id');
            let name = btn.getAttribute('data-name');

            document.getElementById('modalKategoriLabel').textContent = 'Edit Kategori';
            document.querySelector('#modalKategori .modal-icon i').className = 'bi bi-pencil-square';
            document.getElementById('kategori_id').value = id;
            document.getElementById('kategori_name').value = name;
            document.getElementById('formKategoriAlert').innerHTML = '';

            var modal = new bootstrap.Modal(document.getElementById('modalKategori'));
            modal.show();
        }
    });

    // Submit Form (Tambah & Edit)
    document.getElementById('formKategori').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const url = form.getAttribute('data-url');
        const btn = document.getElementById('btnSimpanKategori');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

        showLoading('Menyimpan data kategori...');

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
                data = { status: 'error', message: 'Gagal parsing response server.' };
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
                document.getElementById('formKategoriAlert').innerHTML =
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

    // Tombol Hapus Kategori
    document.getElementById('tabel-kategori').addEventListener('click', function(e) {
        if (e.target.closest('.btnHapusKategori')) {
            let btn = e.target.closest('.btnHapusKategori');
            let id = btn.getAttribute('data-id');
            let url = document.getElementById('tabel-kategori').getAttribute('data-url').replace(/0$/, id);

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data kategori ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Menghapus data kategori...');
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
                            data = { status: 'error', message: 'Gagal parsing response server.' };
                        }
                        if (response.ok && data.status !== 'error') {
                            Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error');
                    });
                }
            });
        }
    });
});
</script>
@endpush