@extends('app')
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Manajemen User</h1>
                            <p class="page-subtitle">Kelola akun user aplikasi</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahUser">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah User</span>
                    </button>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar User</span>
                    </div>
                    {{-- searchbox jika mau --}}
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-users">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama</th>
                                    <th class="col-secondary">Email</th>
                                    <th class="col-secondary">Role</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $i => $user)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $i + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <div class="item-info">
                                                <span class="item-name">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $user->email }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            @foreach ($user->roles as $role)
                                                <span class="badge bg-info">{{ $role->name }}</span>
                                            @endforeach
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditUser"
                                                    data-id="{{ $user->id }}"
                                                    data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}"
                                                    data-roles="{{ json_encode($user->roles->pluck('name')) }}"
                                                    data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusUser"
                                                    data-id="{{ $user->id }}"
                                                    data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                @if ($users->count() == 0)
                                    <tr>
                                        <td colspan="5" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-people"></i>
                                                <h4>Belum ada data user</h4>
                                                <p>Klik tombol "Tambah User" untuk memulai</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal User -->
    <div class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="modalUserLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formUser">
                    @csrf
                    <input type="hidden" name="id" id="user_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon">
                                <i class="bi bi-person"></i>
                            </div>
                            <h5 class="modal-title" id="modalUserLabel">Tambah User</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formUserAlert"></div>
                        <div class="form-group-custom">
                            <label for="user_name" class="form-label-custom required">
                                <i class="bi bi-person-badge"></i> Nama
                            </label>
                            <input type="text" class="form-control-custom" id="user_name" name="name" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="user_email" class="form-label-custom required">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control-custom" id="user_email" name="email" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="user_password" class="form-label-custom">
                                <i class="bi bi-key"></i> Password
                                <span id="passwordHelp" class="text-muted"></span>
                            </label>
                            <input type="password" class="form-control-custom" id="user_password" name="password">
                        </div>
                        <div class="form-group-custom">
                            <label for="user_roles" class="form-label-custom required">
                                <i class="bi bi-person-gear"></i> Role
                            </label>
                            <select class="form-control-custom" id="user_roles" name="roles[]" required multiple="multiple" style="width: 100%">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanUser">
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
            // Inisialisasi Select2 jika tersedia
            if (typeof Select2 !== 'undefined') {
                var userRoles = document.getElementById('user_roles');
                if (userRoles) {
                    $(userRoles).select2({
                        theme: "bootstrap-5",
                        dropdownParent: $("#modalUser")
                    });
                }
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            // Inisialisasi modal
            const modalElement = document.getElementById('modalUser');
            const modalUser = new bootstrap.Modal(modalElement);

            // Tombol Tambah User
            document.getElementById('btnTambahUser').addEventListener('click', function() {
                document.getElementById('modalUserLabel').textContent = 'Tambah User';
                document.getElementById('formUser').reset();
                document.getElementById('user_id').value = '';
                document.getElementById('user_password').setAttribute('required', 'required');
                document.getElementById('passwordHelp').textContent = '(wajib diisi)';
                document.getElementById('formUserAlert').innerHTML = '';
                modalUser.show();
            });

            // Tombol Edit User
            document.querySelectorAll('.btnEditUser').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');
                    let name = this.getAttribute('data-name');
                    let email = this.getAttribute('data-email');
                    let roles = this.getAttribute('data-roles'); // roles dalam bentuk string array, misal: '["admin","user"]'

                    document.getElementById('modalUserLabel').textContent = 'Edit User';
                    document.getElementById('user_id').value = id;
                    document.getElementById('user_name').value = name;
                    document.getElementById('user_email').value = email;

                    // Set value untuk multi-select
                    try {
                        let rolesArr = JSON.parse(roles);
                        let userRoles = document.getElementById('user_roles');
                        for (let i = 0; i < userRoles.options.length; i++) {
                            userRoles.options[i].selected = rolesArr.includes(userRoles.options[i].value);
                        }
                        // Trigger event change jika perlu (untuk select2)
                        let event = new Event('change', { bubbles: true });
                        userRoles.dispatchEvent(event);
                    } catch (e) {
                        // Jika gagal parsing, kosongkan semua
                        let userRoles = document.getElementById('user_roles');
                        for (let i = 0; i < userRoles.options.length; i++) {
                            userRoles.options[i].selected = false;
                        }
                        let event = new Event('change', { bubbles: true });
                        userRoles.dispatchEvent(event);
                    }

                    document.getElementById('user_password').value = '';
                    document.getElementById('user_password').removeAttribute('required');
                    document.getElementById('passwordHelp').textContent =
                        '(kosongkan jika tidak ingin mengubah password)';
                    document.getElementById('formUserAlert').innerHTML = '';
                    modalUser.show();
                });
            });

            // Submit Form
            document.getElementById('formUser').addEventListener('submit', function(e) {
                e.preventDefault();
                let form = this;
                let btn = document.getElementById('btnSimpanUser');
                let btnIcon = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
                document.getElementById('formUserAlert').innerHTML = '';

                showLoading('Menyimpan data user...');
                fetch("{{ route('admin.users.submit') }}", {
                        method: "POST",
                        body: new FormData(form),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.content || "{{ csrf_token() }}"
                        }
                    })
                    .then(response => response.json())
                    .then(res => {
                        hideLoading();
                        if (res.status === 'success') {
                            modalUser.hide();
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message
                            });
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        let msg = 'Terjadi kesalahan.';
                        if (error.response) {
                            error.response.json().then(res => {
                                msg = res.message || msg;
                                if (res.errors) {
                                    msg += '<ul style="text-align:left">';
                                    Object.keys(res.errors).forEach(k => {
                                        msg += '<li>' + res.errors[k][0] + '</li>';
                                    });
                                    msg += '</ul>';
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    html: msg
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = btnIcon;
                    });
            });

            // Tombol Hapus User
            document.querySelectorAll('.btnHapusUser').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    let id = this.getAttribute('data-id');

                    Swal.fire({
                        title: 'Yakin ingin menghapus user ini?',
                        text: "Tindakan ini tidak dapat dibatalkan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showLoading('Menghapus user...');
                            fetch("{{ url('admin/users') }}/" + id, {
                                    method: "DELETE",
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                    }
                                })
                                .then(response => response.json())
                                .then(res => {
                                    hideLoading();
                                    if (res.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil',
                                            text: res.message,
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            text: res.message
                                        });
                                    }
                                })
                                .catch(error => {
                                    hideLoading();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: 'Terjadi kesalahan.'
                                    });
                                });
                        }
                    });
                });
            });
        });
    </script>
@endpush
