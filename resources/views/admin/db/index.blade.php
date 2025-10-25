@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                {{-- ... (header halaman) ... --}}
                <div class="page-title-wrapper">
                    <div class="page-icon"><i class="bi bi-database-gear"></i></div>
                    <div>
                        <h1 class="page-title">Pengaturan Database</h1>
                        <p class="page-subtitle">Atur koneksi ke database server (MySQL) atau file (SQLite)</p>
                    </div>
                </div>
            </div>

            <div class="data-card">
                <div class="data-card-body p-4" style="max-width: 800px;">
                    <div id="formAlert" class="mb-3" style="display: none;"></div>

                    <form id="formDatabaseSettings" data-save-url="{{ route('admin.setup.database.save') }}"
                        data-test-url="{{ route('admin.setup.database.test') }}">
                        @csrf
                        
                        {{-- Dropdown Tipe Koneksi (Sudah Benar) --}}
                        <div class="form-group-custom mb-3">
                            <label for="db_connection" class="form-label required">Tipe Koneksi</label>
                            <select class="form-select" id="db_connection" name="db_connection">
                                <option value="mysql" {{ ($dbSettings['DB_CONNECTION'] ?? 'mysql') == 'mysql' ? 'selected' : '' }}>
                                    MySQL (Server Jaringan / IP)
                                </option>
                                <option value="sqlite" {{ ($dbSettings['DB_CONNECTION'] ?? '') == 'sqlite' ? 'selected' : '' }}>
                                    SQLite (File Lokal / Offline)
                                </option>
                            </select>
                        </div>

                        {{-- === GRUP FORM UNTUK MYSQL === --}}
                        <div id="mysql_settings_group" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label for="db_host" class="form-label required">Alamat Server (Host/IP)</label>
                                    <input type="text" class="form-control" id="db_host" name="db_host"
                                        value="{{ $dbSettings['DB_HOST'] ?? '127.0.0.1' }}">
                                    <small class="form-text">Contoh: 192.168.1.100 (IP server kasir)</small>
                                </div>
                                <div class="col-md-4">
                                    <label for="db_port" class="form-label required">Port</label>
                                    <input type="number" class="form-control" id="db_port" name="db_port"
                                        value="{{ $dbSettings['DB_PORT'] ?? '3306' }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="db_mysql_database" class="form-label required">Nama Database</label>
                                    {{-- PERBAIKAN: Gunakan value dari controller --}}
                                    <input type="text" class="form-control" id="db_mysql_database" name="db_database_mysql" 
                                           value="{{ $dbSettings['DB_MYSQL_DB_DEFAULT'] }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="db_username" class="form-label required">Username</label>
                                    <input type="text" class="form-control" id="db_username" name="db_username"
                                        value="{{ $dbSettings['DB_USERNAME'] ?? 'root' }}">
                                </div>
                                <div class="col-md-12">
                                    <label for="db_password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="db_password" name="db_password"
                                        value="{{ $dbSettings['DB_PASSWORD'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                        
                        {{-- === GRUP FORM UNTUK SQLITE === --}}
                        <div id="sqlite_settings_group" style="display: none;">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="db_sqlite_database" class="form-label required">Path File Database</label>
                                    {{-- PERBAIKAN: Gunakan value dari controller --}}
                                    <input type="text" class="form-control" id="db_sqlite_database" name="db_database_sqlite" 
                                           value="{{ $dbSettings['DB_SQLITE_PATH_DEFAULT'] }}">
                                    <small class="form-text">Gunakan path absolut. Contoh default: `{{ $defaultSqlitePath }}`</small>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Input 'db_database' utama (hidden) --}}
                        <input type="hidden" name="db_database" id="db_database_hidden">

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="btnTestConnection">
                                <i class="bi bi-plug"></i> Test Koneksi
                            </button>
                            <button type="submit" class="btn btn-primary" id="btnSaveConnection">
                                <i class="bi bi-save"></i> Simpan & Restart Aplikasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formDatabaseSettings');
            const btnTest = document.getElementById('btnTestConnection');
            const btnSave = document.getElementById('btnSaveConnection');
            const alertDiv = document.getElementById('formAlert');

            const dbConnectionSelect = document.getElementById('db_connection');
            const mysqlGroup = document.getElementById('mysql_settings_group');
            const sqliteGroup = document.getElementById('sqlite_settings_group');

            const mysqlDbInput = document.getElementById('db_mysql_database');
            const sqliteDbInput = document.getElementById('db_sqlite_database');
            const hiddenDbInput = document.getElementById('db_database_hidden');

            // --- 1. FUNGSI UNTUK TOGGLE TAMPILAN FORM ---
            function toggleFormGroups() {
                if (dbConnectionSelect.value === 'mysql') {
                    mysqlGroup.style.display = 'block';
                    sqliteGroup.style.display = 'none';
                    // Set input MySQL sebagai 'required'
                    mysqlGroup.querySelectorAll('input').forEach(input => input.setAttribute('required',
                        'required'));
                    // Hapus 'required' dari SQLite
                    sqliteDbInput.removeAttribute('required');
                    // Isi hidden input 'db_database' dengan nilai dari form MySQL
                    hiddenDbInput.value = mysqlDbInput.value;

                } else { // 'sqlite'
                    mysqlGroup.style.display = 'none';
                    sqliteGroup.style.display = 'block';
                    // Hapus 'required' dari MySQL
                    mysqlGroup.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
                    // Set 'required' untuk SQLite
                    sqliteDbInput.setAttribute('required', 'required');
                    // Isi hidden input 'db_database' dengan nilai dari form SQLite
                    hiddenDbInput.value = sqliteDbInput.value;
                }
            }

            // --- 2. FUNGSI UNTUK SINKRONKAN HIDDEN INPUT ---
            function syncHiddenDatabaseInput() {
                if (dbConnectionSelect.value === 'mysql') {
                    hiddenDbInput.value = mysqlDbInput.value;
                } else {
                    hiddenDbInput.value = sqliteDbInput.value;
                }
            }

            // Panggil saat dropdown berubah
            dbConnectionSelect.addEventListener('change', toggleFormGroups);
            // Panggil saat input database di salah satu form berubah
            mysqlDbInput.addEventListener('input', syncHiddenDatabaseInput);
            sqliteDbInput.addEventListener('input', syncHiddenDatabaseInput);

            // Panggil sekali saat halaman dimuat
            toggleFormGroups();


            // --- 3. FUNGSI TEST KONEKSI (Sudah Benar) ---
            btnTest.addEventListener('click', function() {
                syncHiddenDatabaseInput(); // Pastikan data 'db_database' terbaru
                const formData = new FormData(form);
                const url = form.dataset.testUrl;

                btnTest.disabled = true;
                btnTest.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Testing...';
                alertDiv.style.display = 'none';

                fetch(url, {
                        method: 'POST',
                        body: formData, // FormData sudah berisi 'db_connection', 'db_database' (dari hidden), dll.
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': formData.get('_token')
                        }
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Koneksi Gagal');
                        }
                        return data;
                    })
                    .then(data => {
                        alertDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        alertDiv.style.display = 'block';
                    })
                    .catch(error => {
                        alertDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
                        alertDiv.style.display = 'block';
                    })
                    .finally(() => {
                        btnTest.disabled = false;
                        btnTest.innerHTML = '<i class="bi bi-plug"></i> Test Koneksi';
                    });
            });

            // --- 4. FUNGSI SIMPAN KONEKSI (Sudah Benar) ---
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                syncHiddenDatabaseInput(); // Pastikan data 'db_database' terbaru

                const formData = new FormData(form);
                const url = form.dataset.saveUrl;

                btnSave.disabled = true;
                btnSave.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';
                alertDiv.style.display = 'none';

                fetch(url, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': formData.get('_token')
                        }
                    })
                    .then(async response => {
                        const data = await response.json();
                        if (!response.ok) {
                            throw new Error(data.message || 'Gagal menyimpan');
                        }
                        return data;
                    })
                    .then(data => {
                        // Sukses, tampilkan pesan dan restart
                        Swal.fire({
                            title: 'Berhasil Disimpan!',
                            text: 'Pengaturan database telah disimpan. Aplikasi akan di-restart untuk menerapkan perubahan.',
                            icon: 'success',
                            confirmButtonText: 'Restart Sekarang',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (typeof Native !== 'undefined' && typeof Native.App !==
                                    'undefined') {
                                    Native.App.restart(); // Restart NativePHP
                                } else {
                                    alert(
                                        'Silakan tutup dan buka kembali aplikasi Anda secara manual.');
                                    location.reload(); // Fallback untuk browser
                                }
                            }
                        });
                    })
                    .catch(error => {
                        alertDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
                        alertDiv.style.display = 'block';
                        btnSave.disabled = false;
                        btnSave.innerHTML = '<i class="bi bi-save"></i> Simpan & Restart Aplikasi';
                    });
            });

        });
    </script>
@endpush
