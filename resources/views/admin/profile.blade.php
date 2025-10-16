@extends('app')

@section('style')
    <style>
        .profile-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            margin-top: 2rem;
        }

        .profile-card {
            flex: 1 1 300px;
            max-width: 350px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-avatar-wrapper {
            margin-bottom: 1rem;
        }

        .profile-avatar,
        .profile-preview-img {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #0d6efd;
            background: #f8f9fa;
        }

        .profile-info {
            text-align: center;
        }

        .profile-name {
            margin-bottom: 0.25rem;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .profile-email {
            color: #6c757d;
            font-size: 1rem;
        }

        .profile-form-card {
            flex: 2 1 400px;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            padding: 2rem 2rem 1.5rem 2rem;
        }

        .form-group-custom {
            margin-bottom: 1.25rem;
        }

        .form-label-custom {
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control-custom {
            width: 100%;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            font-size: 1rem;
            background: #f8f9fa;
        }

        .btn-primary-custom {
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary-custom:hover {
            background: #0b5ed7;
        }

        @media (max-width: 900px) {
            .profile-grid {
                flex-direction: column;
            }

            .profile-card,
            .profile-form-card {
                max-width: 100%;
            }
        }
    </style>
@endsection
@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Profil Penjual</h1>
                            <p class="page-subtitle">Kelola data profil Anda</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-grid">
                <div class="profile-card">
                    <div class="profile-avatar-wrapper">
                        <img src="{{ $user->photo ? Illuminate\Support\Facades\Storage::url($user->photo) : url('upload/no_image.jpg') }}"
                            alt="Penjual" class="profile-avatar" id="showImage">
                    </div>
                    <div class="profile-info">
                        <h3 class="profile-name">{{ $user->name ?? $user->nama }}</h3>
                        <p class="profile-email">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="profile-form-card">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                        class="form-custom" id="profileForm">
                        @csrf
                        @method('PATCH')
                        <div id="profileFormAlert"></div>
                        <div class="form-group-custom">
                            <label for="name" class="form-label-custom">
                                <i class="bi bi-person"></i>
                                Nama
                            </label>
                            <input type="text" name="name" id="name" class="form-control-custom"
                                value="{{ old('name', $user->name ?? $user->nama) }}" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="email" class="form-label-custom">
                                <i class="bi bi-envelope"></i>
                                Email
                            </label>
                            <input type="email" name="email" id="email" class="form-control-custom"
                                value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="form-group-custom">
                            <label for="phone" class="form-label-custom">
                                <i class="bi bi-telephone"></i>
                                Telepon
                            </label>
                            <input type="text" name="phone" id="phone" class="form-control-custom"
                                value="{{ old('phone', $user->telepon ?? '') }}">
                        </div>
                        <div class="form-group-custom">
                            <label for="alamat" class="form-label-custom">
                                <i class="bi bi-geo-alt"></i>
                                Alamat
                            </label>
                            <textarea class="form-control-custom" name="alamat" id="alamat" rows="2">{{ old('alamat', $user->alamat ?? '') }}</textarea>
                        </div>
                        <div class="form-group-custom">
                            <label for="image" class="form-label-custom">
                                <i class="bi bi-image"></i>
                                Foto
                            </label>
                            <input type="file" name="photo" class="form-control-custom" id="image"
                                accept="image/*">
                        </div>
                        <div class="form-group-custom">
                            <label class="form-label-custom d-block">&nbsp;</label>
                            <img id="showImage"
                                src="{{ $user->photo ? Illuminate\Support\Facades\Storage::url($user->photo) : url('upload/no_image.jpg') }}"
                                alt="user avatar" class="profile-preview-img">
                        </div>
                        <div class="form-group-custom">
                            <button type="submit" class="btn-primary-custom w-100" id="btnSimpanProfile">
                                <i class="bi bi-check"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                    <!-- Section: Ubah Password -->
                    <hr class="my-4">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                <i class="bi bi-key"></i> Ubah Password
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-3">
                                Pastikan password Anda cukup panjang dan acak untuk keamanan akun.
                            </p>
                        </header>
                        <form method="post" action="{{ route('password.update') }}" class="mt-2" id="passwordForm">
                            @csrf
                            @method('put')
                            <div id="passwordFormAlert"></div>
                            <div class="form-group-custom position-relative">
                                <label for="update_password_current_password" class="form-label-custom">
                                    <i class="bi bi-lock"></i>
                                    Password Saat Ini
                                </label>
                                <input id="update_password_current_password" name="current_password" type="password"
                                    class="form-control-custom pe-5" autocomplete="current-password" required
                                    style="padding-right: 2.5rem;">
                                <button type="button" class="btn btn-link btn-sm password-toggle-btn" tabindex="-1"
                                    onclick="togglePassword('update_password_current_password', this)"
                                    style="position: absolute; top: 75%; right: 1rem; transform: translateY(-50%); padding: 0; border: none;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-group-custom position-relative">
                                <label for="update_password_password" class="form-label-custom">
                                    <i class="bi bi-shield-lock"></i>
                                    Password Baru
                                </label>
                                <input id="update_password_password" name="password" type="password"
                                    class="form-control-custom pe-5" autocomplete="new-password" required
                                    style="padding-right: 2.5rem;">
                                <button type="button" class="btn btn-link btn-sm password-toggle-btn" tabindex="-1"
                                    onclick="togglePassword('update_password_password', this)"
                                    style="position: absolute; top: 75%; right: 1rem; transform: translateY(-50%); padding: 0; border: none;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-group-custom position-relative">
                                <label for="update_password_password_confirmation" class="form-label-custom">
                                    <i class="bi bi-shield-check"></i>
                                    Konfirmasi Password Baru
                                </label>
                                <input id="update_password_password_confirmation" name="password_confirmation"
                                    type="password" class="form-control-custom pe-5" autocomplete="new-password" required
                                    style="padding-right: 2.5rem;">
                                <button type="button" class="btn btn-link btn-sm password-toggle-btn" tabindex="-1"
                                    onclick="togglePassword('update_password_password_confirmation', this)"
                                    style="position: absolute; top: 75%; right: 1rem; transform: translateY(-50%); padding: 0; border: none;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-group-custom">
                                <button type="submit" class="btn-primary-custom w-100" id="btnSimpanPassword">
                                    <i class="bi bi-key"></i>
                                    Simpan Password
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Verifikasi Password -->
    <div class="modal fade" id="modalVerifikasiPassword" tabindex="-1" aria-labelledby="modalVerifikasiPasswordLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 1rem;">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVerifikasiPasswordLabel">
                        <i class="bi bi-shield-lock"></i> Verifikasi Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="passwordVerifyAlert"></div>
                    <div class="mb-3">
                        <label for="verifyPasswordInput" class="form-label">Masukkan password Anda untuk konfirmasi
                            perubahan profil:</label>
                        <input type="password" class="form-control" id="verifyPasswordInput"
                            autocomplete="current-password" placeholder="Password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnKonfirmasiPassword">
                        <i class="bi bi-shield-check"></i> Konfirmasi & Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageInput = document.getElementById('image');
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            document.querySelectorAll('#showImage').forEach(function(img) {
                                img.src = e.target.result;
                            });
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
            // Modal Bootstrap 5
            let modalVerifikasiPassword = null;
            if (typeof bootstrap !== 'undefined') {
                modalVerifikasiPassword = new bootstrap.Modal(document.getElementById('modalVerifikasiPassword'));
            }

            // AJAX Profile Update
            const profileForm = document.getElementById('profileForm');
            let formDataCache = null; // cache FormData for use after password verified

            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Konfirmasi',
                        text: 'Yakin ingin mengubah data profil Anda?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, simpan!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Simpan FormData sementara
                            formDataCache = new FormData(profileForm);
                            // PATCH method spoofing
                            formDataCache.append('_method', 'PATCH');

                            // Reset password modal
                            document.getElementById('verifyPasswordInput').value = '';
                            document.getElementById('passwordVerifyAlert').innerHTML = '';

                            // Tampilkan modal verifikasi password
                            if (modalVerifikasiPassword) {
                                modalVerifikasiPassword.show();
                            } else {
                                $('#modalVerifikasiPassword').modal('show');
                            }
                        }
                    });
                });

                // Handler untuk tombol konfirmasi password di modal
                document.getElementById('btnKonfirmasiPassword').addEventListener('click', function() {
                    const passwordInput = document.getElementById('verifyPasswordInput');
                    const password = passwordInput.value.trim();
                    const alertDiv = document.getElementById('passwordVerifyAlert');
                    alertDiv.innerHTML = '';

                    if (!password) {
                        alertDiv.innerHTML =
                            '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> Password wajib diisi.</div>';
                        passwordInput.focus();
                        return;
                    }

                    // Tambahkan password ke FormData
                    if (formDataCache) {
                        formDataCache.set('password', password);
                    }

                    const btn = document.getElementById('btnSimpanProfile');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

                    // Optional: show loading overlay
                    if (typeof showLoading === 'function') {
                        showLoading('Menyimpan perubahan profil...');
                    }

                    fetch(profileForm.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': profileForm.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            },
                            body: formDataCache
                        })
                        .then(async response => {
                            if (typeof hideLoading === 'function') {
                                hideLoading();
                            }
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-check"></i> Simpan Perubahan';

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
                                if (modalVerifikasiPassword) {
                                    modalVerifikasiPassword.hide();
                                } else {
                                    $('#modalVerifikasiPassword').modal('hide');
                                }
                                Swal.fire('Berhasil', data.message || 'Profil berhasil diperbarui.',
                                        'success')
                                    .then(() => location.reload());
                            } else {
                                let pesan = 'Silakan periksa kembali isian Anda.';
                                if (data.errors) {
                                    pesan = Object.values(data.errors).map(arr => arr[0]).join(
                                        '<br>');
                                } else if (data.message) {
                                    pesan = data.message;
                                }
                                document.getElementById('profileFormAlert').innerHTML =
                                    '<div class="alert-custom alert-danger"><i class="bi bi-exclamation-circle"></i> ' +
                                    pesan + '</div>';
                                alertDiv.innerHTML =
                                    '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> ' +
                                    pesan + '</div>';
                                Swal.fire('Gagal', pesan, 'error');
                            }
                        })
                        .catch(error => {
                            if (typeof hideLoading === 'function') {
                                hideLoading();
                            }
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-check"></i> Simpan Perubahan';
                            alertDiv.innerHTML =
                                '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> Terjadi kesalahan saat mengirim data.</div>';
                            Swal.fire('Gagal', 'Terjadi kesalahan saat mengirim data.', 'error');
                        });
                });

                // Enter key pada input password submit modal
                document.getElementById('verifyPasswordInput').addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.getElementById('btnKonfirmasiPassword').click();
                    }
                });
            }

            // AJAX Password Update
            const passwordForm = document.getElementById('passwordForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const btn = document.getElementById('btnSimpanPassword');
                    btn.disabled = true;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

                    if (typeof showLoading === 'function') {
                        showLoading('Menyimpan password...');
                    }

                    const formData = new FormData(passwordForm);
                    formData.append('_method', 'put');

                    fetch(passwordForm.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': passwordForm.querySelector('input[name="_token"]')
                                    .value,
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                        .then(async response => {
                            if (typeof hideLoading === 'function') {
                                hideLoading();
                            }
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-key"></i> Simpan Password';

                            let data;
                            try {
                                data = await response.json();
                            } catch (err) {
                                data = {
                                    status: 'error',
                                    message: 'Gagal parsing response server.'
                                };
                            }

                            let pesan = '';
                            if (response.ok && data.status !== 'error') {
                                pesan = data.message || 'Password berhasil diperbarui.';
                                document.getElementById('passwordFormAlert').innerHTML =
                                    '<div class="alert alert-success"><i class="bi bi-check-circle"></i> ' +
                                    pesan + '</div>';
                                passwordForm.reset();
                                Swal.fire('Berhasil', pesan, 'success');
                            } else {
                                pesan = 'Silakan periksa kembali isian Anda.';
                                if (data.errors) {
                                    pesan = Object.values(data.errors).map(arr => arr[0]).join(
                                        '<br>');
                                } else if (data.message) {
                                    pesan = data.message;
                                }
                                document.getElementById('passwordFormAlert').innerHTML =
                                    '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> ' +
                                    pesan + '</div>';
                                Swal.fire('Gagal', pesan, 'error');
                            }
                        })
                        .catch(error => {
                            if (typeof hideLoading === 'function') {
                                hideLoading();
                            }
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-key"></i> Simpan Password';
                            document.getElementById('passwordFormAlert').innerHTML =
                                '<div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i> Terjadi kesalahan saat mengirim data.</div>';
                            Swal.fire('Gagal', 'Terjadi kesalahan saat mengirim data.', 'error');
                        });
                });
            }
        });


        function togglePassword(inputId, el) {
            const input = document.getElementById(inputId);
            const icon = el.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
@endpush
