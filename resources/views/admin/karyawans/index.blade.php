@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Master Karyawan</h1>
                            <p class="page-subtitle">Kelola data karyawan, posisi, dan riwayat penggajian</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahKaryawan" type="button">
                        <i class="bi bi-plus-circle"></i>
                        <span>Tambah Karyawan</span>
                    </button>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Karyawan</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari karyawan..." id="searchInput">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-karyawans" data-url="{{ url('admin/karyawan') }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">No. Karyawan</th>
                                    <th class="col-main">Nama</th>
                                    <th class="col-secondary">Departemen</th>
                                    <th class="col-secondary">Posisi</th>
                                    <th class="col-secondary">No HP</th> {{-- <-- KOLOM BARU --}}
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($karyawans as $key => $karyawan)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <span class="item-info">{{ $karyawan->no_karyawan }}</span>
                                        </td>
                                        <td class="col-main">
                                            <span class="item-name">{{ $karyawan->nama }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $karyawan->department ?? '-' }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $karyawan->position ?? '-' }}</span>
                                        </td>
                                        <td class="col-secondary"> {{-- <-- DATA BARU --}}
                                            <span class="item-info">{{ $karyawan->no_hp ?? '-' }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditKaryawan"
                                                    data-id="{{ $karyawan->id }}"
                                                    data-no_karyawan="{{ $karyawan->no_karyawan }}"
                                                    data-nama="{{ $karyawan->nama }}"
                                                    data-department="{{ $karyawan->department }}"
                                                    data-position="{{ $karyawan->position }}"
                                                    data-alamat="{{ $karyawan->alamat }}" {{-- <-- BARU --}}
                                                    data-no_hp="{{ $karyawan->no_hp }}" {{-- <-- BARU --}}
                                                    data-kontak_darurat="{{ $karyawan->kontak_darurat }}"
                                                    {{-- <-- BARU --}} data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusKaryawan"
                                                    data-id="{{ $karyawan->id }}" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-people-fill"></i>
                                                <h4>Belum ada data karyawan</h4>
                                                <p>Klik tombol "Tambah Karyawan" untuk memulai</p>
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
<div class="modal fade" id="modalKaryawan" tabindex="-1" aria-labelledby="modalKaryawanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <form id="formKaryawan" data-url="{{ route('admin.karyawan.store') }}">
                @csrf
                <input type="hidden" name="id" id="karyawan_id">
                <div class="modal-header custom-modal-header">
                    <div class="modal-header-content">
                        <div class="modal-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h5 class="modal-title" id="modalKaryawanLabel">Tambah Karyawan</h5>
                    </div>
                    <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <div class="modal-body custom-modal-body">
                    <div id="formKaryawanAlert"></div>
                    <ul class="nav nav-tabs" id="karyawanTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="datadiri-tab" data-bs-toggle="tab" data-bs-target="#datadiri" type="button" role="tab" aria-controls="datadiri" aria-selected="true">
                                <i class="bi bi-person-lines-fill"></i> Data Diri
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pekerjaan-tab" data-bs-toggle="tab" data-bs-target="#pekerjaan" type="button" role="tab" aria-controls="pekerjaan" aria-selected="false">
                                <i class="bi bi-briefcase-fill"></i> Data Jabatan
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3" id="karyawanTabContent">
                        {{-- Tab Data Diri --}}
                        <div class="tab-pane fade show active" id="datadiri" role="tabpanel" aria-labelledby="datadiri-tab">
                            <div class="form-group-custom">
                                <label for="karyawan_no_karyawan" class="form-label-custom">
                                    <i class="bi bi-person-vcard"></i>
                                    No. Karyawan
                                </label>
                                <input type="text" class="form-control-custom" id="karyawan_no_karyawan"
                                    name="no_karyawan" required>
                            </div>
                            <div class="form-group-custom">
                                <label for="karyawan_nama" class="form-label-custom">
                                    <i class="bi bi-person-badge"></i>
                                    Nama Karyawan
                                </label>
                                <input type="text" class="form-control-custom" id="karyawan_nama" name="nama"
                                    required>
                            </div>
                            <div class="form-group-custom">
                                <label for="karyawan_no_hp" class="form-label-custom">
                                    <i class="bi bi-phone"></i> No HP
                                </label>
                                <input type="tel" class="form-control-custom" id="karyawan_no_hp" name="no_hp"
                                    placeholder="Contoh: 08123456789">
                            </div>
                            <div class="form-group-custom">
                                <label for="karyawan_kontak_darurat" class="form-label-custom">
                                    <i class="bi bi-person-plus"></i> Kontak Darurat
                                </label>
                                <input type="text" class="form-control-custom" id="karyawan_kontak_darurat"
                                    name="kontak_darurat" placeholder="Contoh: Budi (Ayah) - 08xxxxxxxx">
                            </div>
                            <div class="form-group-custom">
                                <label for="karyawan_alamat" class="form-label-custom">
                                    <i class="bi bi-geo-alt"></i> Alamat
                                </label>
                                <textarea class="form-control-custom" id="karyawan_alamat" name="alamat" rows="3"></textarea>
                            </div>
                        </div>
                        {{-- Tab Data Jabatan --}}
                        <div class="tab-pane fade" id="pekerjaan" role="tabpanel" aria-labelledby="pekerjaan-tab">
                            <div class="form-group-custom">
                                <label for="dept" class="form-label-custom">
                                    <i class="bi bi-diagram-3"></i>
                                    Departemen
                                </label>
                                <select id="dept" name="department" class="form-control-custom" required>
                                    <option value="Kitchen">Kitchen</option>
                                    <option value="Bar">Bar</option>
                                    <option value="Accounting">Accounting</option>
                                    <option value="Purchasing">Purchasing</option>
                                    <option value="Lainnya">Lainnya (Isi Manual)</option>
                                </select>
                            </div>
                            <div class="form-group-custom">
                                <label for="karyawan_position" class="form-label-custom">
                                    <i class="bi bi-briefcase"></i>
                                    Posisi
                                </label>
                                <input type="text" class="form-control-custom" id="karyawan_position" name="position">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer custom-modal-footer">
                    <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn-primary-custom" id="btnSimpanKaryawan">
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
            // Modal instance agar bisa show/hide dari JS
            let modalKaryawan = new bootstrap.Modal(document.getElementById('modalKaryawan'), {
                backdrop: 'static',
                keyboard: false
            });

            // Tampilkan modal Tambah Karyawan
            document.getElementById('btnTambahKaryawan').addEventListener('click', function() {
                document.getElementById('formKaryawan').reset();
                document.getElementById('karyawan_id').value = "";
                document.getElementById('modalKaryawanLabel').textContent = "Tambah Karyawan";
                // Hilangkan alert/form messages manual
                document.getElementById('formKaryawanAlert').innerHTML = '';
                modalKaryawan.show();
                // Handle manual department input jika perlu setelah reset form
                if (manualDeptInput) {
                    manualDeptInput.style.display = 'none';
                    manualDeptInput.value = "";
                }
                deptSelect.value = "Kitchen";
                toggleManualDeptInput();
            });

            // === Departemen custom input logika ===
            const deptSelect = document.getElementById('dept');
            let manualDeptInput = null;

            function toggleManualDeptInput() {
                if (deptSelect.value === 'Lainnya') {
                    if (!manualDeptInput) {
                        manualDeptInput = document.createElement('input');
                        manualDeptInput.type = 'text';
                        manualDeptInput.className = 'form-control-custom mt-2';
                        manualDeptInput.id = 'manual_department_input';
                        manualDeptInput.name = 'department_manual';
                        manualDeptInput.placeholder = 'Isi nama departemen secara manual';
                        deptSelect.parentNode.appendChild(manualDeptInput);
                    }
                    manualDeptInput.style.display = 'block';
                } else {
                    if (manualDeptInput) {
                        manualDeptInput.style.display = 'none';
                        manualDeptInput.value = '';
                    }
                }
            }
            if (deptSelect) {
                deptSelect.addEventListener('change', toggleManualDeptInput);
                toggleManualDeptInput();
            }

            document.getElementById('formKaryawan').addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Pastikan data sudah benar. Simpan karyawan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-success mx-2',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitKaryawanForm();
                    }
                });
            });

            function submitKaryawanForm() {
                const formElement = document.getElementById('formKaryawan');
                const url = formElement.getAttribute('data-url');
                const formData = new FormData(formElement);

                // Jika departemen "Lainnya" gunakan isian manual (di-backend juga harus di-handle!)
                if (deptSelect.value === 'Lainnya' && manualDeptInput && manualDeptInput.value !== '') {
                    formData.set('department', manualDeptInput.value);
                }

                Swal.showLoading();
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': formElement.querySelector('input[name=_token]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        Swal.close();
                        let data;
                        try {
                            data = await response.json();
                        } catch (e) {
                            data = { message: "Terjadi kesalahan server internal." };
                        }
                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message || 'Data karyawan berhasil disimpan',
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                        else if (response.status === 422) {
                            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : (
                                data.message || 'Data tidak valid');

                            const alertDiv = document.getElementById('formKaryawanAlert');
                            alertDiv.innerHTML =
                                `<div class="alert alert-danger" role="alert">${errors}</div>`;
                        }
                        else {
                            const errors = data.message || "Gagal menyimpan data";
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                html: errors,
                                showConfirmButton: true
                            }).then(() => {
                                window.location.reload();
                            });
                        }

                    })
                    .catch((error) => {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: error.message || 'Tidak dapat menyimpan data',
                            showConfirmButton: true
                        }).then(() => {
                            window.location.reload();
                        });
                    });
            }

            document.querySelectorAll('.btnEditKaryawan').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('formKaryawan').reset();
                    document.getElementById('karyawan_id').value = btn.getAttribute('data-id');
                    document.getElementById('karyawan_no_karyawan').value = btn.getAttribute(
                        'data-no_karyawan');
                    document.getElementById('karyawan_nama').value = btn.getAttribute('data-nama');
                    document.getElementById('karyawan_position').value = btn.getAttribute(
                        'data-position');
                    document.getElementById('karyawan_alamat').value = btn.getAttribute(
                        'data-alamat');
                    document.getElementById('karyawan_no_hp').value = btn.getAttribute(
                    'data-no_hp');
                    document.getElementById('karyawan_kontak_darurat').value = btn.getAttribute(
                        'data-kontak_darurat');
                    const deptValue = btn.getAttribute('data-department');
                    const standardDepts = ["Kitchen", "Bar", "Accounting", "Purchasing", "Lainnya"];

                    if (standardDepts.includes(deptValue)) {
                        deptSelect.value = deptValue || 'Kitchen';
                        toggleManualDeptInput(); // Sembunyikan input manual jika tidak perlu
                    } else {
                        deptSelect.value = 'Lainnya';
                        toggleManualDeptInput(); // Tampilkan input manual
                        if (manualDeptInput) {
                            manualDeptInput.value = deptValue;
                        }
                    }
                    document.getElementById('modalKaryawanLabel').textContent = "Edit Karyawan";
                    document.getElementById('formKaryawanAlert').innerHTML = '';
                    modalKaryawan.show();
                });
            });

            // Fitur Hapus Karyawan
            document.querySelectorAll('.btnHapusKaryawan').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = btn.getAttribute('data-id');
                    const url = "{{ route('admin.karyawan.destroy', ':id') }}".replace(':id', id);
                    Swal.fire({
                        title: 'Hapus Karyawan?',
                        text: 'Data yang dihapus tidak dapat dikembalikan!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-danger mx-2',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.showLoading();
                            fetch(url, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'input[name=_token]').value,
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(async response => {
                                    Swal.close();
                                    if (response.ok) {
                                        const data = await response.json();
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil!',
                                            text: data.message ||
                                                'Data karyawan berhasil dihapus',
                                            showConfirmButton: true
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        let data;
                                        try {
                                            data = await response.json();
                                        } catch (e) {
                                            data = {
                                                message: "Gagal menghapus data"
                                            };
                                        }
                                        const errors = data.errors ? Object.values(
                                                data
                                                .errors).flat().join('<br>') : data
                                            .message;
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            html: errors,
                                            showConfirmButton: true
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    }
                                })
                                .catch((error) => {
                                    Swal.close();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Terjadi Kesalahan',
                                        text: error.message ||
                                            'Tidak dapat menghapus data',
                                        showConfirmButton: true
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                });
                        }
                    });
                });
            });

            // Optional: Close modal reset form
            document.getElementById('modalKaryawan').addEventListener('hidden.bs.modal', function() {
                document.getElementById('formKaryawan').reset();
                document.getElementById('karyawan_id').value = "";
                document.getElementById('modalKaryawanLabel').textContent = "Tambah Karyawan";
                document.getElementById('formKaryawanAlert').innerHTML = '';
                if (manualDeptInput) {
                    manualDeptInput.style.display = 'none';
                    manualDeptInput.value = "";
                }
                deptSelect.value = "Kitchen";
                toggleManualDeptInput();
            });

        });
    </script>
@endpush
