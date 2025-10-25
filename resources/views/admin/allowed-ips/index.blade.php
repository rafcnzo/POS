@extends('app')

@section('style')
    <style>
        .white-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 32px 0 rgba(88,119,255,.05);
            padding: 2rem 2rem 1.5rem 2rem;
        }
        .data-card {
            border-radius: 14px;
            box-shadow: 0 4px 32px 0 rgba(88,119,255,.05);
            background: #fff;
            padding: 0;
        }
        .data-card-header {
            padding: 1.5rem 2rem 0 2rem;
            border-bottom: 1px solid #f0f0f3;
            background: none;
        }
        .data-card-title {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 600;
            font-size: 1.4rem;
            margin-bottom: 0;
        }
        .data-card-body {
            padding: 1rem 2rem 1.5rem 2rem;
        }
        .table-container {
            overflow-x: auto;
        }
        .col-action {
            width: 110px;
        }
        .btn-add-primary {
            background: #5860f7;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: .54rem 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .45rem;
            box-shadow: 0 4px 32px 0 rgba(88,119,255,.14);
            transition: background .12s;
        }
        .btn-add-primary:hover {
            background: #3740c7;
            color: #fff;
        }
        .empty-state {
            text-align: center;
            padding: 2rem 0 !important;
            color: #aaa;
            background: #f7f9fc;
            border-radius: 8px;
        }
        .empty-content i {
            font-size: 2.3rem;
            margin-bottom: .7rem;
            display: block;
        }
    </style>
@endsection

@section('content')
<div class="page-content">
    <div class="data-card">
        <div class="data-card-header d-flex justify-content-between align-items-center">
            <div class="data-card-title">
                <i class="bi bi-shield-lock"></i>
                <span>Daftar IP Whitelist</span>
            </div>
            <button class="btn-add-primary" id="btnAddIp">
                <i class="bi bi-plus-circle"></i>
                <span>Tambah IP</span>
            </button>
        </div>
        <div class="data-card-body">
            <div id="alert-container"></div>
            <div class="table-container">
                <table class="data-table" id="tabel-ip-whitelist">
                    <thead>
                        <tr>
                            <th class="col-main">IP Address</th>
                            <th class="col-main">Deskripsi</th>
                            <th class="col-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ip-table-body">
                        @forelse ($ips as $ip)
                            <tr class="data-row" id="ip-row-{{ $ip->id }}">
                                <td class="col-main">{{ $ip->ip }}</td>
                                <td class="col-main">{{ $ip->label ? $ip->label : '-' }}</td>
                                <td class="col-action">
                                    <div class="action-buttons">
                                        <button class="btn-action btn-edit btnEditIp" data-id="{{ $ip->id }}" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button class="btn-action btn-delete btnDeleteIp" data-id="{{ $ip->id }}" data-bs-toggle="tooltip" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">
                                    <div class="empty-content">
                                        <i class="bi bi-shield-lock"></i>
                                        <h4>Belum ada data IP Whitelist</h4>
                                        <p>Klik tombol "Tambah IP" untuk mulai menambahkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pt-2">{{ $ips->links() }}</div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalIp" tabindex="-1" aria-labelledby="modalIpLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
            <form id="formIp" data-url="{{ route('admin.allowed-ips.store') }}">
                @csrf
                <input type="hidden" name="id" id="ip_id">
                <div class="modal-header custom-modal-header">
                    <div class="modal-header-content">
                        <div class="modal-icon"><i class="bi bi-shield-lock"></i></div>
                        <h5 class="modal-title" id="modalIpLabel">Tambah IP</h5>
                    </div>
                    <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup"><i class="bi bi-x"></i></button>
                </div>
                <div class="modal-body custom-modal-body">
                    <div id="formIpAlert"></div>
                    <div class="form-group-custom">
                        <label for="ip" class="form-label-custom required">
                            <i class="bi bi-lan"></i> IP Address
                        </label>
                        <input type="text" class="form-control-custom" id="ip" name="ip" required>
                        <div class="invalid-feedback" id="ip_error"></div>
                    </div>
                    <div class="form-group-custom">
                        <label for="label" class="form-label-custom">
                            <i class="bi bi-card-text"></i> Deskripsi
                        </label>
                        <input type="text" class="form-control-custom" id="label" name="label" placeholder="Contoh: IP Kantor/Site">
                    </div>
                </div>
                <div class="modal-footer custom-modal-footer">
                    <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal"><i
                            class="bi bi-x"></i> Batal</button>
                    <button type="submit" class="btn-primary-custom" id="btnSimpanIp"><i class="bi bi-check"></i>
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script type="module">
    function initializeIpWhitelistPage() {
        const modalElement = document.getElementById('modalIp');
        const modalIp = new bootstrap.Modal(modalElement);
        const formIp = document.getElementById('formIp');
        const alertIp = document.getElementById('formIpAlert');
        const btnSimpanIp = document.getElementById('btnSimpanIp');
        const modalIpLabel = document.getElementById('modalIpLabel');

        // Tombol Tambah IP
        const btnAddIp = document.getElementById('btnAddIp');
        if(btnAddIp) {
            btnAddIp.addEventListener('click', function () {
                modalIpLabel.textContent = 'Tambah IP';
                formIp.reset();
                document.getElementById('ip_id').value = '';
                alertIp.innerHTML = '';
                document.getElementById('ip').classList.remove('is-invalid');
                document.getElementById('ip_error').textContent = '';
                modalIp.show();
            });
        }

        // Tombol Edit IP
        document.querySelectorAll('.btnEditIp').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                fetch(`{{ url('admin/allowed-ips/show') }}/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        modalIpLabel.textContent = 'Edit IP';
                        formIp.reset();
                        document.getElementById('ip_id').value = data.id;
                        document.getElementById('ip').value = data.ip;
                        document.getElementById('label').value = data.label ?? '';
                        alertIp.innerHTML = '';
                        document.getElementById('ip').classList.remove('is-invalid');
                        document.getElementById('ip_error').textContent = '';
                        modalIp.show();
                    });
            });
        });

        // Submit Form IP
        formIp.addEventListener('submit', function (e) {
            e.preventDefault();
            btnSimpanIp.disabled = true;
            btnSimpanIp.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';
            alertIp.innerHTML = '';
            document.getElementById('ip').classList.remove('is-invalid');
            document.getElementById('ip_error').textContent = '';

            let formData = new FormData(formIp);

            fetch(formIp.getAttribute('data-url'), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                let data = await response.json();
                btnSimpanIp.disabled = false;
                btnSimpanIp.innerHTML = '<i class="bi bi-check"></i> Simpan';

                if (response.ok && data.success) {
                    modalIp.hide();
                    Swal.fire('Berhasil', data.success, 'success').then(() => location.reload());
                } else if (response.status === 422) {
                    // Validasi gagal
                    if (data.errors && data.errors.ip) {
                        document.getElementById('ip').classList.add('is-invalid');
                        document.getElementById('ip_error').textContent = data.errors.ip[0];
                    }
                    let msg = data.message || 'Terjadi kesalahan validasi.';
                    alertIp.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
                } else {
                    let msg = data.message || 'Terjadi kesalahan.';
                    alertIp.innerHTML = `<div class="alert alert-danger">${msg}</div>`;
                }
            })
            .catch(error => {
                btnSimpanIp.disabled = false;
                btnSimpanIp.innerHTML = '<i class="bi bi-check"></i> Simpan';
                alertIp.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan jaringan.</div>`;
            });
        });

        // Tombol Hapus IP
        document.querySelectorAll('.btnDeleteIp').forEach(function(btn) {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Yakin ingin menghapus IP ini?',
                    text: "IP yang dihapus tidak dapat dikembalikan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`{{ url('admin/allowed-ips/destroy') }}/${id}`, {
                            method: "DELETE",
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(async response => {
                            let data = await response.json();
                            if (response.ok && data.success) {
                                // Hapus baris dari tabel
                                document.getElementById('ip-row-' + id)?.remove();
                                Swal.fire('Berhasil', data.success, 'success');
                            } else {
                                let msg = data.error || data.message || 'Gagal menghapus IP.';
                                Swal.fire('Gagal', msg, 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
                        });
                    }
                });
            });
        });

        // Reset form dan validasi saat modal ditutup
        $('#modalIp').on('hidden.bs.modal', function() {
            formIp.reset();
            document.getElementById('ip').classList.remove('is-invalid');
            document.getElementById('ip_error').textContent = '';
            alertIp.innerHTML = '';
            btnSimpanIp.disabled = false;
            btnSimpanIp.innerHTML = '<i class="bi bi-check"></i> Simpan';
        });

        // Tooltip Bootstrap
        if (typeof bootstrap !== 'undefined') {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new bootstrap.Tooltip(el);
            });
        }
    }

    if(typeof $ !== 'undefined') {
        // jQuery is ready
        $(initializeIpWhitelistPage);
    } else {
        window.addEventListener('DOMContentLoaded', initializeIpWhitelistPage);
    }
</script>
@endpush
