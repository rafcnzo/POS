@extends('app')
@section('content')
<div class="page-content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Backup Database</h4>
                        <form action="{{ route('admin.backup.create') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary"><i class="bx bx-plus"></i> Buat Backup Baru</button>
                        </form>
                    </div>
                    <p>File backup disimpan di <code>storage/app/backups/</code></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama File</th>
                                    <th>Ukuran</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backups as $key => $backup)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $backup->file_name }}</td>
                                    <td>{{ number_format($backup->file_size / 1024, 2) }} KB</td>
                                    <td>{{ $backup->created_at->format('d M Y, H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('admin.backup.download', $backup->id) }}" class="btn btn-sm btn-success">
                                            <i class="bx bx-download"></i> Download
                                        </a>
                                        <form action="{{ route('admin.backup.destroy', $backup->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus file backup ini?')">
                                                <i class="bx bx-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada file backup.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection