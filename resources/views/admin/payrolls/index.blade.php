@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Data Payroll Karyawan</h1>
                            <p class="page-subtitle">Manajemen data karyawan & riwayat payroll gaji</p>
                        </div>
                    </div>
                    <!-- Tempatkan tombol tambah fitur lain di sini jika diperlukan -->
                </div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>List Payroll Karyawan</span>
                    </div>
                    <div class="data-card-actions">
                        {{-- Tempat filter/pencarian jika diinginkan --}}
                    </div>
                </div>

                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-payroll">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">Nama Karyawan</th>
                                    <th class="col-secondary">Periode</th>
                                    <th class="col-number">Absensi</th>
                                    <th class="col-number">Nominal</th>
                                    <th class="col-secondary">Status</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($karyawans as $key => $karyawan)
                                    @php
                                        $payroll = $karyawan->payroll->first();
                                    @endphp
                                    <tr class="data-row">
                                        <td class="col-number">{{ $loop->iteration }}</td>
                                        <td class="col-main">
                                            <span class="item-name">{{ $karyawan->nama }}</span>
                                            <span class="item-info">{{ $karyawan->no_karyawan }} /
                                                {{ $karyawan->department ?? '-' }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            <span
                                                class="badge-unit">{{ $months[$bulan] ?? $bulan }}-{{ $tahun }}</span>
                                        </td>
                                        @if ($payroll)
                                            <td class="col-number">
                                                <span class="item-info">{{ $payroll->jumlah_absensi }} Hari</span>
                                            </td>
                                            <td class="col-number">
                                                <span
                                                    class="item-name">Rp{{ number_format($payroll->nominal_gaji, 0, ',', '.') }}</span>
                                            </td>
                                            <td class="col-secondary">
                                                @if ($payroll->status_pembayaran == 'dibayar')
                                                    <span class="badge-status-success">Dibayar</span>
                                                    <span class="item-info">Tgl:
                                                        {{ $payroll->tanggal_pembayaran ? \Carbon\Carbon::parse($payroll->tanggal_pembayaran)->format('d/m/Y') : '-' }}</span>
                                                    @if ($payroll->file_bukti)
                                                        <a href="{{ route('acc.payroll.download', $payroll->id) }}"
                                                            target="_blank" class="item-info"
                                                            style="font-size: 12px; text-decoration: underline;">
                                                            Lihat Bukti
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="badge-status-pending">Pending</span>
                                                @endif
                                            </td>
                                            <td class="col-action">
                                                <div class="action-buttons">
                                                    <button class="btn-action btn-edit btnEdit"
                                                        data-id="{{ $payroll->id }}"
                                                        data-karyawan-id="{{ $karyawan->id }}"
                                                        data-karyawan-nama="{{ $karyawan->nama }}"
                                                        data-absensi="{{ $payroll->jumlah_absensi }}"
                                                        data-gaji="{{ $payroll->nominal_gaji }}"
                                                        data-status="{{ $payroll->status_pembayaran }}"
                                                        data-tanggal="{{ $payroll->tanggal_pembayaran }}"
                                                        data-bukti="{{ $payroll->file_bukti ? Storage::url($payroll->file_bukti) : '' }}"
                                                        data-bs-toggle="tooltip" title="Edit / Bayar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    @if ($payroll->status_pembayaran == 'pending')
                                                        <button class="btn-action btn-delete btnHapus"
                                                            data-id="{{ $payroll->id }}"
                                                            data-url="{{ route('acc.payroll.destroy', $payroll->id) }}"
                                                            data-bs-toggle="tooltip" title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        @else
                                            <td colspan="3" class="empty-state-cell">
                                                <span class="badge-status-danger">Belum Diinput</span>
                                            </td>
                                            <td class="col-action text-center">
                                                <button class="btn-action btn-primary btnInputGaji"
                                                    data-karyawan-id="{{ $karyawan->id }}"
                                                    data-karyawan-nama="{{ $karyawan->nama }}" data-bs-toggle="tooltip"
                                                    title="Input Gaji Karyawan">
                                                    <i class="bi bi-plus-circle"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPayroll" tabindex="-1" aria-labelledby="modalPayrollLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content custom-modal">
                <form id="formPayroll" data-url="{{ route('acc.payroll.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="payroll_id">
                    <input type="hidden" name="karyawan_id" id="payroll_karyawan_id">
                    <input type="hidden" name="bulan" id="payroll_bulan" value="{{ $bulan }}">
                    <input type="hidden" name="tahun" id="payroll_tahun" value="{{ $tahun }}">

                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon"><i class="bi bi-cash-coin"></i></div>
                            <h5 class="modal-title" id="modalPayrollLabel">Input Payroll Karyawan</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formPayrollAlert"></div>

                        <div class="form-group-custom">
                            <label class="form-label-custom"><i class="bi bi-person-badge"></i> Nama Karyawan</label>
                            <input type="text" class="form-control-custom" id="payroll_karyawan_nama" readonly>
                        </div>

                        <div class="form-group-custom">
                            <label class="form-label-custom"><i class="bi bi-calendar-check"></i> Periode</label>
                            <input type="text" class="form-control-custom" id="payroll_periode"
                                value="{{ $months[$bulan] ?? $bulan }} {{ $tahun }}" readonly>
                        </div>

                        <div class="form-group-custom">
                            <label for="payroll_jumlah_absensi" class="form-label-custom">
                                <i class="bi bi-calendar-day"></i> Jumlah Absensi (Hari)
                            </label>
                            <input type="number" class="form-control-custom" id="payroll_jumlah_absensi"
                                name="jumlah_absensi" required>
                        </div>

                        <div class="form-group-custom">
                            <label for="payroll_nominal_gaji" class="form-label-custom">
                                <i class="bi bi-wallet2"></i> Nominal Gaji (Rp)
                            </label>
                            <input type="number" class="form-control-custom" id="payroll_nominal_gaji"
                                name="nominal_gaji" required>
                        </div>

                        <hr style="border-top: 2px dashed #ddd; margin: 20px 0;">

                        <div class="form-group-custom">
                            <label for="payroll_status_pembayaran" class="form-label-custom">
                                <i class="bi bi-patch-check-fill"></i> Status Bayar
                            </label>
                            <select id="payroll_status_pembayaran" name="status_pembayaran" class="form-control-custom"
                                required>
                                <option value="pending">Pending</option>
                                <option value="dibayar">Dibayar</option>
                            </select>
                        </div>

                        <div id="payment-details" style="display: none;">
                            <div class="form-group-custom">
                                <label for="payroll_tanggal_pembayaran" class="form-label-custom">
                                    <i class="bi bi-calendar-event"></i> Tgl Pembayaran
                                </label>
                                <input type="date" class="form-control-custom" id="payroll_tanggal_pembayaran"
                                    name="tanggal_pembayaran">
                            </div>

                            <div class="form-group-custom">
                                <label for="payroll_file_bukti" class="form-label-custom">
                                    <i class="bi bi-file-earmark-arrow-up"></i> Upload Bukti (JPG, PNG, PDF)
                                </label>
                                <input type="file" class="form-control-custom" id="payroll_file_bukti"
                                    name="file_bukti" accept=".jpg,.jpeg,.png,.pdf">
                                <small id="file_help_text" class="form-text text-muted"></small>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i> Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanPayroll">
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
            let modalPayroll = new bootstrap.Modal(document.getElementById('modalPayroll'), {
                backdrop: 'static',
                keyboard: false
            });

            const formPayroll = document.getElementById('formPayroll');
            const modalPayrollLabel = document.getElementById('modalPayrollLabel');
            const formPayrollAlert = document.getElementById('formPayrollAlert');
            const statusSelect = document.getElementById('payroll_status_pembayaran');
            const paymentDetails = document.getElementById('payment-details');
            const tanggalBayarInput = document.getElementById('payroll_tanggal_pembayaran');
            const fileBuktiInput = document.getElementById('payroll_file_bukti');
            const fileHelpText = document.getElementById('file_help_text');
            const bulanDefault = "{{ $bulan }}";
            const tahunDefault = "{{ $tahun }}";
            const periodeDefault = "{{ $months[$bulan] ?? $bulan }} {{ $tahun }}";

            // Helper untuk reset form payroll dengan state default
            function resetPayrollForm() {
                formPayroll.reset();
                formPayrollAlert.innerHTML = '';
                document.getElementById('payroll_id').value = "";
                document.getElementById('payroll_status_pembayaran').value = 'pending';
                paymentDetails.style.display = 'none';
                fileHelpText.innerText = '';

                document.getElementById('payroll_bulan').value = bulanDefault;
                document.getElementById('payroll_tahun').value = tahunDefault;
                document.getElementById('payroll_periode').value = periodeDefault;

                tanggalBayarInput.removeAttribute('required');
                fileBuktiInput.removeAttribute('required');

                // Untuk preview file bukti jika ada
                if (document.getElementById('bukti_preview')) {
                    document.getElementById('bukti_preview').style.display = 'none';
                    document.getElementById('bukti_preview').href = '#';
                }
            }

            // Tombol Input Payroll Baru
            document.querySelectorAll('.btnInputGaji').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    resetPayrollForm();
                    modalPayrollLabel.textContent = "Input Payroll Karyawan";
                    document.getElementById('payroll_karyawan_id').value = btn.getAttribute(
                        'data-karyawan-id');
                    document.getElementById('payroll_karyawan_nama').value = btn.getAttribute(
                        'data-karyawan-nama');
                    modalPayroll.show();
                });
            });

            // Tombol Edit/Update Payroll
            document.querySelectorAll('.btnEdit').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    resetPayrollForm();
                    modalPayrollLabel.textContent = "Edit Payroll Karyawan";

                    document.getElementById('payroll_id').value = btn.getAttribute('data-id');
                    document.getElementById('payroll_karyawan_id').value = btn.getAttribute(
                        'data-karyawan-id');
                    document.getElementById('payroll_karyawan_nama').value = btn.getAttribute(
                        'data-karyawan-nama');
                    document.getElementById('payroll_jumlah_absensi').value = btn.getAttribute(
                        'data-absensi');
                    document.getElementById('payroll_nominal_gaji').value = btn.getAttribute(
                        'data-gaji');

                    const status = btn.getAttribute('data-status');
                    const tanggal = btn.getAttribute('data-tanggal');
                    document.getElementById('payroll_status_pembayaran').value = status ||
                        'pending';
                    tanggalBayarInput.value = tanggal || '';

                    if (status === 'dibayar') {
                        paymentDetails.style.display = 'block';
                        tanggalBayarInput.setAttribute('required', true);
                        fileBuktiInput.removeAttribute('required');
                        fileHelpText.innerText = 'Kosongkan file jika tidak ingin ubah bukti.';

                        // Preview file bukti jika ada atribut data-bukti
                        if (btn.dataset.bukti) {
                            if (document.getElementById('bukti_preview')) {
                                document.getElementById('bukti_preview').href = btn.dataset.bukti;
                                document.getElementById('bukti_preview').style.display =
                                    "inline-block";
                            }
                        }
                    } else {
                        paymentDetails.style.display = 'none';
                        tanggalBayarInput.removeAttribute('required');
                        fileBuktiInput.removeAttribute('required');
                        fileHelpText.innerText = "";
                    }
                    modalPayroll.show();
                });
            });

            // Jika status pembayaran berubah, tampilkan/ubah properti detail pembayaran
            statusSelect.addEventListener('change', function() {
                if (this.value === 'dibayar') {
                    paymentDetails.style.display = 'block';
                    tanggalBayarInput.setAttribute('required', true);

                    const isEdit = document.getElementById('payroll_id').value;
                    if (isEdit) {
                        fileBuktiInput.removeAttribute('required');
                        fileHelpText.innerText = 'Kosongkan file jika tidak ingin ubah bukti.';
                    } else {
                        fileBuktiInput.setAttribute('required', true);
                        fileHelpText.innerText = "";
                    }
                } else {
                    paymentDetails.style.display = 'none';
                    tanggalBayarInput.removeAttribute('required');
                    fileBuktiInput.removeAttribute('required');
                    fileHelpText.innerText = "";
                }
            });

            // Validasi preview nama file yang dipilih (optional, user friendly)
            fileBuktiInput?.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileHelpText.innerText = "File: " + this.files[0].name;
                } else if (!this.value) {
                    fileHelpText.innerText = "";
                }
            });

            // Submit payroll form
            formPayroll.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi',
                    text: 'Simpan data payroll ini?',
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
                        submitPayrollForm();
                    }
                });
            });

            function submitPayrollForm() {
                const url = formPayroll.getAttribute('data-url');
                const formData = new FormData(formPayroll);

                // Disable tombol submit agar tidak double submit
                document.getElementById('btnSimpanPayroll').setAttribute('disabled', true);

                Swal.showLoading();
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': formPayroll.querySelector('input[name=_token]').value,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        Swal.close();
                        document.getElementById('btnSimpanPayroll').removeAttribute('disabled');
                        let data;
                        try {
                            data = await response.json();
                        } catch {
                            data = {
                                message: "Respon server tidak valid."
                            };
                        }
                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message ?? 'Data payroll berhasil disimpan.',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') :
                                data.message;
                            formPayrollAlert.innerHTML =
                                `<div class="alert alert-danger" role="alert">${errors}</div>`;
                            formPayrollAlert.scrollIntoView({
                                behavior: 'smooth'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        document.getElementById('btnSimpanPayroll').removeAttribute('disabled');
                        formPayrollAlert.innerHTML =
                            `<div class="alert alert-danger" role="alert">Terjadi kesalahan: ${error.message}</div>`;
                    });
            }

            // Tombol hapus payroll (dengan konfirmasi user friendly)
            document.querySelectorAll('.btnHapus').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const url = btn.getAttribute('data-url');
                    Swal.fire({
                        title: 'Hapus Data Payroll?',
                        text: 'Payroll akan dihapus permanen dan tindakan ini tidak dapat dibatalkan. Lanjutkan?',
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
                            executeDelete(url);
                        }
                    });
                });
            });

            function executeDelete(url) {
                Swal.showLoading();
                fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(async response => {
                        Swal.close();
                        let data;
                        try {
                            data = await response.json();
                        } catch {
                            data = {
                                message: "Respon server tidak valid."
                            };
                        }
                        if (response.ok) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Data dihapus!',
                                text: data.message ?? 'Payroll berhasil dihapus.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message ?? 'Gagal menghapus data'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan koneksi ke server. Silakan coba lagi.'
                        });
                    });
            }

            // Tooltip for better UX
            if (window.bootstrap && window.bootstrap.Tooltip) {
                document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                    new bootstrap.Tooltip(el);
                });
            }
        });
    </script>
@endpush
