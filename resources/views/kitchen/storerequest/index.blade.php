@extends('app')

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-header-content">
                    <div class="page-title-wrapper">
                        <div class="page-icon">
                            <i class="bi bi-box"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Manajemen Permintaan Bahan</h1>
                            <p class="page-subtitle">Kelola permintaan bahan baku dari dapur ke gudang</p>
                        </div>
                    </div>
                    <button class="btn-add-primary" id="btnTambahRequest">
                        <i class="bi bi-plus-circle"></i>
                        <span>Buat Permintaan</span>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-primary">
                    <div class="stat-icon">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-value">{{ $storerequests->count() }}</h3>
                        <p class="stat-label">Total Permintaan</p>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <div class="data-card-title">
                        <i class="bi bi-list-ul"></i>
                        <span>Daftar Permintaan Bahan</span>
                    </div>
                    <div class="data-card-actions">
                        <div class="search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" placeholder="Cari permintaan..." id="searchInput">
                        </div>
                    </div>
                </div>
                <div class="data-card-body">
                    <div class="table-container">
                        <table class="data-table" id="tabel-request"
                            data-url="{{ route('kitchen.storerequest.destroy', ['storerequest' => 0]) }}">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-main">No. Permintaan</th>
                                    <th class="col-main">Tanggal</th>
                                    <th class="col-secondary">Status</th>
                                    <th class="col-secondary">Bahan Baku</th>
                                    <th class="col-secondary">Keterangan</th>
                                    <th class="col-action">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($storerequests as $key => $request)
                                    <tr class="data-row">
                                        <td class="col-number">
                                            <span class="row-number">{{ $key + 1 }}</span>
                                        </td>
                                        <td class="col-main">
                                            <span class="item-name">{{ $request->request_number }}</span>
                                        </td>
                                        <td class="col-main">
                                            <span
                                                class="item-name">{{ \Carbon\Carbon::parse($request->created_at)->format('d M Y H:i') }}</span>
                                        </td>
                                        <td class="col-secondary">
                                            @if ($request->status == 'proses')
                                                <span class="badge-status badge-info">Proses</span>
                                            @elseif ($request->status == 'po')
                                                <span class="badge-status badge-warning">PO</span>
                                            @endif
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">
                                                @foreach ($request->items as $item)
                                                    {{-- Periksa apakah itemable ada & valid --}}
                                                    @if ($item->itemable)
                                                        @php
                                                            $itemName = '';
                                                            $itemSatuan = '';

                                                            // Cek tipe modelnya
                                                            if ($item->itemable instanceof \App\Models\Ingredient) {
                                                                $itemName = $item->itemable->name;
                                                                $itemSatuan = $item->itemable->unit;
                                                            } elseif ($item->itemable instanceof \App\Models\Ffne) {
                                                                $itemName = $item->itemable->nama_ffne;
                                                                $itemSatuan = $item->itemable->satuan_ffne;
                                                            } else {
                                                                $itemName = 'N/A';
                                                                $itemSatuan = 'N/A';
                                                            }
                                                        @endphp

                                                        {{ $itemName }}
                                                        ({{ number_format($item->requested_quantity, 1) }}
                                                        {{ $itemSatuan }})
                                                        <br>
                                                    @else
                                                        [Item Dihapus]<br>
                                                    @endif
                                                @endforeach
                                            </span>
                                        </td>
                                        <td class="col-secondary">
                                            <span class="badge-unit">{{ $request->remarks ?? '-' }}</span>
                                        </td>
                                        <td class="col-action">
                                            <div class="action-buttons">
                                                <button class="btn-action btn-edit btnEditRequest"
                                                    data-id="{{ $request->id }}" data-note="{{ $request->remarks }}"
                                                    data-items='{{ json_encode($request->items->map(fn($i) => ['ingredient_id' => $i->ingredient_id, 'requested_quantity' => $i->requested_quantity])) }}'
                                                    data-bs-toggle="tooltip" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action btn-delete btnHapusRequest"
                                                    data-id="{{ $request->id }}" data-bs-toggle="tooltip" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <a href="{{ route('kitchen.storerequest.print', ['id' => $request->id]) }}"
                                                    class="btn-action btn-print" target="_blank" data-bs-toggle="tooltip"
                                                    title="Print">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <div class="empty-content">
                                                <i class="bi bi-box"></i>
                                                <h4>Belum ada permintaan bahan</h4>
                                                <p>Klik tombol "Buat Permintaan" untuk memulai</p>
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
    <div class="modal fade" id="modalRequest" tabindex="-1" aria-labelledby="modalRequestLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content custom-modal">
                <form id="formRequest" data-url="{{ route('kitchen.storerequest.submit') }}">
                    @csrf
                    <input type="hidden" name="id" id="request_id">
                    <div class="modal-header custom-modal-header">
                        <div class="modal-header-content">
                            <div class="modal-icon">
                                <i class="bi bi-box"></i>
                            </div>
                            <h5 class="modal-title" id="modalRequestLabel">Buat Permintaan</h5>
                        </div>
                        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Tutup">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <div class="modal-body custom-modal-body">
                        <div id="formRequestAlert"></div>
                        <div class="form-group-custom">
                            <label for="request_note" class="form-label-custom">
                                <i class="bi bi-text-paragraph"></i> Keterangan
                            </label>
                            <textarea class="form-control-custom" id="request_note" name="note"
                                placeholder="Keterangan permintaan (opsional)"></textarea>
                        </div>
                        <div class="form-group-custom">
                            <label class="form-label-custom">
                                <i class="bi bi-list-check"></i> Bahan Baku yang Diminta
                            </label>
                            <div id="items-container">
                                <!-- Dynamic rows will be added here -->
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="btnAddItem">
                                <i class="bi bi-plus-circle"></i> Tambah Bahan
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer custom-modal-footer">
                        <button type="button" class="btn-secondary-custom" data-bs-dismiss="modal">
                            <i class="bi bi-x"></i> Batal
                        </button>
                        <button type="submit" class="btn-primary-custom" id="btnSimpanRequest">
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
        const bahanbakus = @json($bahanbakus->values());

        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('#tabel-request tbody tr.data-row');
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }

            // Fungsi untuk tambah row item
            function addItemRow(itemData = {}) { // itemData bisa berisi itemable_id, itemable_type, requested_quantity
                const container = document.getElementById('items-container');
                const index = container.children.length;
                const row = document.createElement('div');
                row.className = 'item-row d-flex align-items-center mb-2 gap-2'; // Tambah gap

                // --- BUAT OPTIONS UNTUK SELECT ---
                // 'itemData.itemable_id' dan 'itemData.itemable_type' digunakan untuk set 'selected'
                const optionsHtml = bahanbakus.map(b => {
                    let costPrice = Number(b.cost_price || b.cost || 0);

                    // --- Kunci Perbaikan: Buat value berisi ID dan TIPE ---
                    const optionValue = `${b.id}:${b.type}`; // Cth: "12:App\Models\Ingredient"

                    // Cek apakah item ini harus terpilih (untuk mode edit)
                    const isSelected = (b.id == itemData.itemable_id && b.type == itemData.itemable_type);

                    return `<option value="${optionValue}" ${isSelected ? 'selected' : ''}>
                                ${b.name} (Rp ${costPrice.toLocaleString('id-ID')})
                            </option>`;
                }).join('');
                // --- AKHIR BUAT OPTIONS ---

                // --- BUAT HTML ROW BARU ---
                // Nama input diganti menjadi 'item_id_type' (untuk di-split) dan 'requested_quantity'
                row.innerHTML = `
                    <select name="items[${index}][item_id_type]" class="form-control item-select" required style="flex: 1;">
                        <option value="">Pilih Bahan</option>
                        ${optionsHtml}
                    </select>
                    <input type="number" name="items[${index}][requested_quantity]" 
                        value="${itemData.requested_quantity || ''}" 
                        step="0.01" min="0.01" class="form-control" placeholder="Jumlah" required style="flex-basis: 120px;">
                    <button type="button" class="btn btn-danger btn-sm remove-item"><i class="bi bi-trash"></i></button>
                    
                    <input type="hidden" name="items[${index}][item_id]" value="${itemData.itemable_id || ''}">
                    <input type="hidden" name="items[${index}][item_type]" value="${itemData.itemable_type || ''}">
                `;
                // --- AKHIR HTML ROW BARU ---
                
                container.appendChild(row);

                // --- Listener untuk hapus row (index di-update) ---
                row.querySelector('.remove-item').addEventListener('click', function() {
                    row.remove();
                    // Update index 'name' untuk semua row yang tersisa
                    Array.from(container.children).forEach((r, i) => {
                        r.querySelector('select').name = `items[${i}][item_id_type]`;
                        r.querySelector('input[type="number"]').name = `items[${i}][requested_quantity]`;
                        r.querySelector('input[type="hidden"][name$="[item_id]"]').name = `items[${i}][item_id]`;
                        r.querySelector('input[type="hidden"][name$="[item_type]"]').name = `items[${i}][item_type]`;
                    });
                });

                // --- Listener untuk select item (PENTING) ---
                // Saat memilih, split valuenya dan isi hidden input
                row.querySelector('.item-select').addEventListener('change', function() {
                    const [id, type] = this.value.split(':');
                    const currentRow = this.closest('.item-row');
                    currentRow.querySelector('input[name$="[item_id]"]').value = id || '';
                    currentRow.querySelector('input[name$="[item_type]"]').value = type || '';
                });
                // Trigger change sekali (jika edit) untuk mengisi hidden input
                if (itemData.itemable_id) {
                    row.querySelector('.item-select').dispatchEvent(new Event('change'));
                }
            }

            document.getElementById('btnAddItem').addEventListener('click', () => addItemRow());

            document.getElementById('btnTambahRequest').addEventListener('click', function() {
                document.getElementById('modalRequestLabel').textContent = 'Buat Permintaan';
                document.querySelector('.modal-icon i').className = 'bi bi-plus-circle';
                document.getElementById('formRequest').reset();
                document.getElementById('request_id').value = '';
                document.getElementById('formRequestAlert').innerHTML = '';
                document.getElementById('items-container').innerHTML = '';
                addItemRow();
                var modal = new bootstrap.Modal(document.getElementById('modalRequest'));
                modal.show();
            });

            // Tombol Edit Request
            // Edit Request dengan withAuth
            document.getElementById('tabel-request').addEventListener('click', function(e) {
                if (e.target.closest('.btnEditRequest')) {
                    let btn = e.target.closest('.btnEditRequest');
                    withAuth(function() {
                        let id = btn.getAttribute('data-id');
                        let note = btn.getAttribute('data-note');
                        let items = JSON.parse(btn.getAttribute('data-items') || '[]');

                        document.getElementById('modalRequestLabel').textContent =
                        'Edit Permintaan';
                        document.querySelector('.modal-icon i').className = 'bi bi-pencil-square';
                        document.getElementById('request_id').value = id;
                        document.getElementById('request_note').value = note || '';
                        document.getElementById('formRequestAlert').innerHTML = '';
                        document.getElementById('items-container').innerHTML = '';

                        if (items.length === 0) {
                            addItemRow();
                        } else {
                            items.forEach(item => addItemRow(item.ingredient_id, item
                                .requested_quantity));
                        }

                        var modal = new bootstrap.Modal(document.getElementById('modalRequest'));
                        modal.show();
                    });
                }
            });

            // Submit Form
            document.getElementById('formRequest').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const url = form.getAttribute('data-url');
                const btn = document.getElementById('btnSimpanRequest');
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-repeat bx-spin"></i> Menyimpan...';

                showLoading('Menyimpan data permintaan...');

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
                            data = {
                                status: 'error',
                                message: 'Gagal parsing response server.'
                            };
                        }

                        if (response.ok && data.status !== 'error') {
                            Swal.fire('Berhasil', data.message, 'success').then(() => location
                                .reload());
                        } else {
                            let pesan = 'Silakan periksa kembali isian Anda.';
                            if (data.errors) {
                                pesan = Object.values(data.errors).map(arr => arr[0]).join('<br>');
                            } else if (data.message) {
                                pesan = data.message;
                            }
                            document.getElementById('formRequestAlert').innerHTML =
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

            // Tombol Hapus Request dengan withAuth
            document.getElementById('tabel-request').addEventListener('click', function(e) {
                if (e.target.closest('.btnHapusRequest')) {
                    let btn = e.target.closest('.btnHapusRequest');
                    withAuth(function() {
                        let id = btn.getAttribute('data-id');
                        let url = document.getElementById('tabel-request').getAttribute('data-url')
                            .replace(
                                /0$/, id);

                        Swal.fire({
                            title: 'Yakin ingin menghapus?',
                            text: "Data permintaan ini akan dihapus permanen!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonText: 'Batal',
                            confirmButtonText: 'Ya, hapus!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                showLoading('Menghapus data permintaan...');
                                fetch(url, {
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
                                            data = {
                                                status: 'error',
                                                message: 'Gagal parsing response server.'
                                            };
                                        }
                                        if (response.ok && data.status !==
                                            'error') {
                                            Swal.fire('Terhapus!', data.message,
                                                    'success')
                                                .then(() => location.reload());
                                        } else {
                                            Swal.fire('Gagal', data.message ||
                                                'Terjadi kesalahan saat menghapus data.',
                                                'error');
                                        }
                                    })
                                    .catch(error => {
                                        hideLoading();
                                        Swal.fire('Gagal',
                                            'Terjadi kesalahan saat menghapus data.',
                                            'error');
                                    });
                            }
                        });
                    });
                }
            });
        });
    </script>
@endpush
