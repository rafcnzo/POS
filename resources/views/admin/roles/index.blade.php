@extends('app')
{{-- Select2 sudah di-include di app.js, gunakan class select2 di select pada permissions --}}
@section('style')
    <style>
        .permission-badge {
            font-size: 0.8em;
            margin: 2px;
        }
    </style>
@endsection

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Roles & Permissions</h1>
                            <p class="page-subtitle">Kelola role dan izin akses untuk setiap role</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahRole">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Role</span>
                    </button>
                </div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Role</span>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-roles">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama Role</th>
                                    <th class="col-secondary">Total User</th>
                                    <th class="col-main">Permissions</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $i => $role)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $i + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span class="item-name fw-bold">{{ $role->name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge bg-secondary">{{ $role->users_count }} User</span>
                                        </td>
                                        <td class="col-main" style="white-space: normal;">
                                            @if ($role->name == 'Super Admin')
                                                <span class="badge bg-success permission-badge">All Access (Default)</span>
                                            @else
                                                @forelse ($role->permissions->sortBy('name') as $permission)
                                                    <span
                                                        class="badge bg-info text-dark permission-badge">{{ $permission->name }}</span>
                                                @empty
                                                    <span class="text-muted">Tidak ada permission</span>
                                                @endforelse
                                            @endif
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                @if ($role->name != 'Super Admin')
                                                    <button class="btn-action btn-edit btnEditRole"
                                                        data-id="{{ $role->id }}" data-name="{{ $role->name }}"
                                                        data-permissions="{{ json_encode($role->permissions->pluck('name')) }}"
                                                        data-bs-toggle="tooltip" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn-action btn-delete btnHapusRole"
                                                        data-id="{{ $role->id }}" data-bs-toggle="tooltip"
                                                        title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted" data-bs-toggle="tooltip"
                                                        title="Tidak dapat diubah"><i class="bi bi-lock-fill"></i></span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-shield-lock"></i>
                                                <h4>Belum ada data role</h4>
                                                <p>Klik tombol "Tambah Role" untuk memulai</p>
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

    <div class="modal fade" id="modalRole" tabindex="-1" aria-labelledby="modalRoleLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formRole" data-url="{{ route('admin.roles.submit') }}">
                    @csrf
                    <input type="hidden" name="id" id="role_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon"><i class="bi bi-shield-lock"></i></div>
                            <h5 class="modal-title" id="modalRoleLabel">Tambah Role</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup"><i
                                class="bi bi-x"></i></button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formRoleAlert"></div>
                        <div class="form-group-custom">
                            <label for="role_name" class="form-label-custom required">
                                <i class="bi bi-tag-fill"></i> Nama Role
                            </label>
                            <input type="text" class="form-control-custom" id="role_name" name="name" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="role_permissions" class="form-label-custom">
                                <i class="bi bi-key-fill"></i> Izin Akses (Permissions)
                            </label>
                            <select class="form-control-custom select2" id="role_permissions" name="permissions[]" multiple
                                style="width:100%">
                                @foreach ($permissions as $permission)
                                    <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal"><i
                                class="bi bi-x"></i> Batal</button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanRole"><i class="bi bi-check"></i>
                            Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script type="module">
        // Tunggu event dari app.js
        function initializeRolesPage() {
            console.log('Initializing roles page...');

            const modalElement = document.getElementById('modalRole');
            const modalRole = new bootstrap.Modal(modalElement);
            const formRole = document.getElementById('formRole');
            const alertRole = document.getElementById('formRoleAlert');
            const btnSimpanRole = document.getElementById('btnSimpanRole');
            const modalRoleLabel = document.getElementById('modalRoleLabel');

            // Gunakan jQuery untuk select2
            const permissionsSelect = $('#role_permissions');

            // Inisialisasi Select2
            permissionsSelect.select2({
                dropdownParent: $('#modalRole'),
                theme: "bootstrap-5",
                width: '100%',
                placeholder: 'Pilih permissions...',
                allowClear: true
            });

            console.log('Select2 initialized on #role_permissions');

            // Tombol Tambah Role
            const btnTambahRole = document.getElementById('btnTambahRole');
            if (btnTambahRole) {
                btnTambahRole.addEventListener('click', function() {
                    modalRoleLabel.textContent = 'Tambah Role';
                    formRole.reset();
                    document.getElementById('role_id').value = '';
                    alertRole.innerHTML = '';

                    permissionsSelect.val(null).trigger('change');
                    document.getElementById('role_name').disabled = false;
                    permissionsSelect.prop('disabled', false);

                    modalRole.show();
                });
            }

            // Tombol Edit Role
            document.querySelectorAll('.btnEditRole').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const permissions = this.getAttribute('data-permissions');

                    modalRoleLabel.textContent = 'Edit Role: ' + name;
                    formRole.reset();
                    document.getElementById('role_id').value = id;
                    document.getElementById('role_name').value = name;
                    alertRole.innerHTML = '';

                    try {
                        const permissionsArr = JSON.parse(permissions);
                        permissionsSelect.val(permissionsArr).trigger('change');
                    } catch (e) {
                        console.error('Error parsing permissions:', e);
                        permissionsSelect.val(null).trigger('change');
                    }

                    if (name === 'Super Admin') {
                        document.getElementById('role_name').disabled = true;
                        permissionsSelect.prop('disabled', true);
                    } else {
                        document.getElementById('role_name').disabled = false;
                        permissionsSelect.prop('disabled', false);
                    }

                    modalRole.show();
                });
            });

            // Submit Form Role
            formRole.addEventListener('submit', function(e) {
                e.preventDefault();
                btnSimpanRole.disabled = true;
                btnSimpanRole.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
                alertRole.innerHTML = '';

                if (typeof showLoading === 'function') {
                    showLoading('Menyimpan data role...');
                }

                let formData = new FormData(formRole);
                formData.delete('permissions[]');

                const selectedPermissions = permissionsSelect.val() || [];
                selectedPermissions.forEach(val => formData.append('permissions[]', val));

                const url = formRole.getAttribute('data-url');

                fetch(url, {
                        method: "POST",
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        const data = await response.json();

                        if (typeof hideLoading === 'function') hideLoading();

                        btnSimpanRole.disabled = false;
                        btnSimpanRole.innerHTML = '<i class="bi bi-check"></i> Simpan';

                        if (response.ok && data.status === 'success') {
                            modalRole.hide();
                            Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                        } else {
                            let errorMsg = data.message || 'Terjadi kesalahan.';
                            if (data.errors) {
                                errorMsg = Object.values(data.errors).map(e => Array.isArray(e) ? e[0] : e)
                                    .join('<br>');
                            }
                            alertRole.innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
                        }
                    })
                    .catch(error => {
                        console.error('Submit error:', error);
                        if (typeof hideLoading === 'function') hideLoading();
                        btnSimpanRole.disabled = false;
                        btnSimpanRole.innerHTML = '<i class="bi bi-check"></i> Simpan';
                        alertRole.innerHTML =
                        `<div class="alert alert-danger">Terjadi kesalahan jaringan</div>`;
                    });
            });

            // Tombol Hapus Role
            document.querySelectorAll('.btnHapusRole').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const url = `{{ url('admin/roles') }}/${id}`;

                    Swal.fire({
                        title: 'Yakin ingin menghapus role ini?',
                        text: "User yang memiliki role ini akan kehilangan hak aksesnya!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (typeof showLoading === 'function') showLoading('Menghapus role...');

                            fetch(url, {
                                    method: "DELETE",
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content,
                                        'Accept': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (typeof hideLoading === 'function') hideLoading();
                                    if (data.status === 'success') {
                                        Swal.fire('Terhapus!', data.message, 'success').then(
                                        () => location.reload());
                                    } else {
                                        Swal.fire('Gagal', data.message, 'error');
                                    }
                                })
                                .catch(error => {
                                    console.error('Delete error:', error);
                                    if (typeof hideLoading === 'function') hideLoading();
                                    Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                                });
                        }
                    });
                });
            });

            // Reset saat modal ditutup
            $('#modalRole').on('hidden.bs.modal', function() {
                permissionsSelect.val(null).trigger('change');
                document.getElementById('role_name').disabled = false;
                permissionsSelect.prop('disabled', false);
                alertRole.innerHTML = '';
                formRole.reset();
            });

            console.log('Roles page initialized successfully');
        }

        // Tunggu event dari app.js
        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            // Sudah ready, langsung init
            initializeRolesPage();
        } else {
            // Tunggu event
            window.addEventListener('app-libraries-loaded', function() {
                console.log('Received app-libraries-loaded event');
                initializeRolesPage();
            });
        }
    </script>
@endpush
