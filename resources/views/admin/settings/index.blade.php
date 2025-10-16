@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-gear"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Pengaturan Sistem</h1>
                            <p class="page-subtitle">Atur informasi toko dan konfigurasi printer</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <form id="form-settings" action="{{ route('admin.settings.update') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <!-- Pengaturan Umum Toko -->
                    <div class="data-card mb-4">
                        <div class="data-card-header">
                            <div class="data-card-title">
                                <i class="bi bi-shop"></i>
                                <span>Pengaturan Umum Toko</span>
                            </div>
                        </div>
                        <div class="data-card-body px-3 px-md-4 py-4">
                            <div class="row">
                                <div class="col-md-6 mb-3" style="padding-right: 2rem;">
                                    <div class="form-group-custom mb-4">
                                        <label for="store_name" class="form-label-custom">
                                            <i class="bi bi-building"></i> Nama Toko/Cabang
                                        </label>
                                        <input type="text" class="form-control-custom" id="store_name" name="store_name"
                                            value="{{ $settings['store_name'] ?? '' }}" placeholder="Nama Toko/Cabang">
                                    </div>
                                    <div class="form-group-custom mb-4">
                                        <label for="store_phone" class="form-label-custom">
                                            <i class="bi bi-telephone"></i> Telepon
                                        </label>
                                        <input type="text" class="form-control-custom" id="store_phone"
                                            name="store_phone" value="{{ $settings['store_phone'] ?? '' }}"
                                            placeholder="Nomor Telepon">
                                    </div>
                                    <div class="form-group-custom mb-4">
                                        <label for="tax" class="form-label-custom">
                                            <i class="bi bi-percent"></i> Pajak (%)
                                        </label>
                                        <input type="number" class="form-control-custom" id="tax" name="tax"
                                            min="0" max="100" step="0.01"
                                            value="{{ isset($settings['tax']) ? $settings['tax'] : '' }}"
                                            placeholder="Masukkan persentase pajak, contoh: 10">
                                        <small class="form-text text-muted">Masukkan persentase pajak, contoh: 10 untuk
                                            10%</small>
                                    </div>
                                    <div class="form-group-custom">
                                        <label for="store_address" class="form-label-custom">
                                            <i class="bi bi-geo-alt"></i> Alamat
                                        </label>
                                        <textarea class="form-control-custom" id="store_address" name="store_address" rows="3" placeholder="Alamat Toko">{{ $settings['store_address'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3" style="padding-left: 2rem;">
                                    <div class="form-group-custom mb-4">
                                        <label for="store_logo" class="form-label-custom">
                                            <i class="bi bi-image"></i> Logo Toko
                                        </label>
                                        <input class="form-control-custom" type="file" id="store_logo" name="store_logo">
                                    </div>
                                    @if (isset($settings['store_logo']))
                                        <div class="form-group-custom">
                                            <label class="form-label-custom">Logo Saat Ini:</label><br>
                                            <img src="{{ asset('storage/' . $settings['store_logo']) }}" alt="Logo Toko"
                                                style="max-height: 80px; background: #f0f0f0; padding: 8px 12px; border-radius: 8px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Printer -->
                    <div class="data-card mb-4">
                        <div class="data-card-header">
                            <div class="data-card-title">
                                <i class="bi bi-printer"></i>
                                <span>Pengaturan Printer</span>
                            </div>
                        </div>
                        <div class="data-card-body px-3 px-md-4 py-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label for="printer_kasir" class="form-label-custom">
                                            <i class="bi bi-printer"></i> Printer Kasir (Struk)
                                        </label>
                                        <select class="form-select form-control-custom" id="printer_kasir"
                                            name="printer_kasir">
                                            <option value="">-- Pilih Printer --</option>
                                            @forelse ($printers as $printer)
                                                <option value="{{ $printer['name'] }}"
                                                    {{ ($settings['printer_kasir'] ?? '') == $printer['name'] ? 'selected' : '' }}>
                                                    {{ $printer['name'] }}
                                                </option>
                                            @empty
                                                <option value="" disabled>Tidak ada printer terdeteksi</option>
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label for="receipt_footer_text" class="form-label-custom">
                                            <i class="bi bi-card-text"></i> Teks Tambahan di Struk (Footer)
                                        </label>
                                        <textarea class="form-control-custom" id="receipt_footer_text" name="receipt_footer_text" rows="3"
                                            placeholder="Teks footer struk">{{ $settings['receipt_footer_text'] ?? 'Terima Kasih Atas Kunjungan Anda' }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label for="printer_dapur" class="form-label-custom">
                                            <i class="bi bi-printer"></i> Printer Dapur
                                        </label>
                                        <select class="form-select form-control-custom" id="printer_dapur" name="printer_dapur">
                                            <option value="">-- Pilih Printer --</option>
                                            @forelse ($printers as $printer)
                                                <option value="{{ $printer['name'] }}" {{ ($settings['printer_dapur'] ?? '') == $printer['name'] ? 'selected' : '' }}>
                                                    {{ $printer['name'] }}
                                                </option>
                                            @empty
                                                <option value="" disabled>Tidak ada printer terdeteksi</option>
                                            @endforelse
                                        </select>
                                    </div>
                                    <div class="form-group-custom">
                                        <label for="printer_akunting" class="form-label-custom">
                                            <i class="bi bi-printer"></i> Printer Akunting (Laporan A4)
                                        </label>
                                        <select class="form-select form-control-custom" id="printer_akunting"
                                            name="printer_akunting">
                                            <option value="">-- Pilih Printer --</option>
                                            @forelse ($printers as $printer)
                                                <option value="{{ $printer['name'] }}"
                                                    {{ ($settings['printer_akunting'] ?? '') == $printer['name'] ? 'selected' : '' }}>
                                                    {{ $printer['name'] }}
                                                </option>
                                            @empty
                                                <option value="" disabled>Tidak ada printer terdeteksi</option>
                                            @endforelse
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Simpan -->
                    <div class="data-card">
                        <div class="data-card-body text-center">
                            <button type="submit" class="btn-primary-custom w-100 text-center" id="btn-save-settings">
                                <i class="bi bi-check"></i> Simpan Semua Pengaturan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-settings');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Simpan Pengaturan?',
                    text: "Apakah Anda yakin ingin menyimpan semua perubahan pengaturan?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim AJAX request pakai fetch API
                        const url = form.getAttribute('action');
                        const formData = new FormData(form);

                        // Tampilkan loading
                        Swal.fire({
                            title: 'Menyimpan...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            })
                            .then(async response => {
                                let data;
                                try {
                                    data = await response.json();
                                } catch (err) {
                                    throw new Error('Gagal parsing response');
                                }
                                if (!response.ok) {
                                    throw new Error(data.message || 'Terjadi kesalahan');
                                }
                                return data;
                            })
                            .then(data => {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: data.message ||
                                        'Pengaturan berhasil disimpan.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: error.message ||
                                        'Terjadi kesalahan saat menyimpan pengaturan.'
                                });
                            });
                    }
                });
            });
        });
    </script>
@endpush
