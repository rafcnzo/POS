@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Manajemen Supplier</h1>
                            <p class="page-subtitle">Kelola data supplier dan informasi kredit</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahSupplier">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Supplier</span>
                    </button>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Supplier</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari supplier..." id="searchInput">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-suppliers" data-url="{{ url('admin/suppliers') }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama Supplier</th>
                                    <th class="col-secondary">Contact Person</th>
                                    <th class="col-secondary">Telepon</th>
                                    <th class="col-currency">Limit Kredit</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $key => $supplier)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span class="item-name">{{ $supplier->name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $supplier->contact_person }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $supplier->phone }}</span>
                                        </td>
                                        <td class="col-currency">
                                            <span class="price-value">Rp {{ number_format($supplier->credit_limit, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditSupplier"
                                                    data-id="{{ $supplier->id }}"
                                                    data-name="{{ $supplier->name }}"
                                                    data-contact_person="{{ $supplier->contact_person }}"
                                                    data-phone="{{ $supplier->phone }}"
                                                    data-address="{{ $supplier->address }}"
                                                    data-credit_limit="{{ $supplier->credit_limit }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusSupplier"
                                                    data-id="{{ $supplier->id }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-truck"></i>
                                                <h4>Belum ada data supplier</h4>
                                                <p>Klik tombol "Tambah Supplier" untuk memulai</p>
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
    <div class="modal fade" id="modalSupplier" tabindex="-1" aria-labelledby="modalSupplierLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formSupplier" data-url="{{ route('acc.suppliers.submit') }}">
                    @csrf
                    <input type="hidden" name="id" id="supplier_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon">
                                <i class="bi bi-truck"></i>
                            </div>
                            <h5 class="modal-title" id="modalSupplierLabel">Tambah Supplier</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formSupplierAlert"></div>
                        <div class="form-group-custom">
                            <label for="supplier_name" class="form-label-custom">
                                <i class="bi bi-person-badge"></i>
                                Nama Supplier
                            </label>
                            <input type="text" class="form-control-custom" id="supplier_name" name="name" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="supplier_contact_person" class="form-label-custom">
                                <i class="bi bi-person"></i>
                                Contact Person
                            </label>
                            <input type="text" class="form-control-custom" id="supplier_contact_person" name="contact_person">
                        </div>
                        <div class="form-group-custom">
                            <label for="supplier_phone" class="form-label-custom">
                                <i class="bi bi-telephone"></i>
                                Telepon
                            </label>
                            <input type="text" class="form-control-custom" id="supplier_phone" name="phone">
                        </div>
                        <div class="form-group-custom">
                            <label for="supplier_address" class="form-label-custom">
                                <i class="bi bi-geo-alt"></i>
                                Alamat
                            </label>
                            <textarea class="form-control-custom" id="supplier_address" name="address" rows="3"></textarea>
                        </div>
                        <div class="form-group-custom">
                            <label for="supplier_credit_limit" class="form-label-custom">
                                <i class="bi bi-cash"></i>
                                Limit Kredit
                            </label>
                            <input type="number" class="form-control-custom" id="supplier_credit_limit" name="credit_limit" value="0" required>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanSupplier">
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
            const rows = document.querySelectorAll('#tabel-suppliers tbody tr.data-row');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Tombol Tambah Supplier
    document.getElementById('btnTambahSupplier').addEventListener('click', function() {
        document.getElementById('modalSupplierLabel').textContent = 'Tambah Supplier';
        document.querySelector('.modal-icon i').className = 'bi bi-plus-circle';
        document.getElementById('formSupplier').reset();
        document.getElementById('supplier_id').value = '';
        document.getElementById('formSupplierAlert').innerHTML = '';
        var modal = new bootstrap.Modal(document.getElementById('modalSupplier'));
        modal.show();
    });

    // Tombol Edit Supplier
    document.getElementById('tabel-suppliers').addEventListener('click', function(e) {
        if (e.target.closest('.btnEditSupplier')) {
            let btn = e.target.closest('.btnEditSupplier');
            let id = btn.getAttribute('data-id');
            let name = btn.getAttribute('data-name');
            let contact_person = btn.getAttribute('data-contact_person');
            let phone = btn.getAttribute('data-phone');
            let address = btn.getAttribute('data-address');
            let credit_limit = btn.getAttribute('data-credit_limit');

            document.getElementById('modalSupplierLabel').textContent = 'Edit Supplier';
            document.querySelector('.modal-icon i').className = 'bi bi-pencil-square';
            document.getElementById('supplier_id').value = id;
            document.getElementById('supplier_name').value = name;
            document.getElementById('supplier_contact_person').value = contact_person;
            document.getElementById('supplier_phone').value = phone;
            document.getElementById('supplier_address').value = address;
            document.getElementById('supplier_credit_limit').value = credit_limit;
            document.getElementById('formSupplierAlert').innerHTML = '';

            var modal = new bootstrap.Modal(document.getElementById('modalSupplier'));
            modal.show();
        }
    });

    // Submit Form (Tambah & Edit)
    document.getElementById('formSupplier').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const url = form.getAttribute('data-url');
        const btn = document.getElementById('btnSimpanSupplier');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

        showLoading('Menyimpan data supplier...');

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
                document.getElementById('formSupplierAlert').innerHTML =
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

    // Tombol Hapus Supplier
    document.getElementById('tabel-suppliers').addEventListener('click', function(e) {
        if (e.target.closest('.btnHapusSupplier')) {
            let btn = e.target.closest('.btnHapusSupplier');
            let id = btn.getAttribute('data-id');
            let url = document.getElementById('tabel-suppliers').getAttribute('data-url');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data supplier ini akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading('Menghapus data supplier...');
                    fetch(url + '/' + id, {
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