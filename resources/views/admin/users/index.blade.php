@extends('app')
@section('content')
    <div class="page-content">
        <div class="row mb-3">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Manajemen User</h4>
                            <button class="btn btn-primary" id="btnTambahUser"><i class="bi bi-plus"></i> Tambah User</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="tabel-users">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $i => $user)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <span class="badge bg-info">{{ $role->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning btnEditUser" 
                                                    data-id="{{ $user->id }}"
                                                    data-name="{{ $user->name }}"
                                                    data-email="{{ $user->email }}"
                                                    data-roles="{{ json_encode($user->roles->pluck('name')) }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btnHapusUser" 
                                                    data-id="{{ $user->id }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($users->count() == 0)
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada user terdaftar.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal User -->
    <div class="modal fade" id="modalUser" tabindex="-1" aria-labelledby="modalUserLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formUser">
                @csrf
                <input type="hidden" name="id" id="user_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalUserLabel">Tambah User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div id="formUserAlert"></div>
                        <div class="mb-3">
                            <label for="user_name" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="user_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="user_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="user_password" class="form-label">Password <span id="passwordHelp"
                                    class="text-muted"></span></label>
                            <input type="password" class="form-control" id="user_password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="user_roles" class="form-label">Role</label>
                            <select class="form-select" id="user_roles" name="roles[]" required multiple="multiple"
                                style="width: 100%">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btnSimpanUser">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
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
