@extends('app')

@section('content')
<div class="page-content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Monitoring Credit Limit Supplier</h4>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Supplier</th>
                                    <th>Total Limit Kredit</th>
                                    <th>Utang Saat Ini</th>
                                    <th>Sisa Limit</th>
                                    <th style="width: 25%;">Persentase Penggunaan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliersData as $supplier)
                                <tr>
                                    <td>{{ $supplier->name }}</td>
                                    <td>Rp {{ number_format($supplier->credit_limit, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($supplier->current_debt, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($supplier->remaining_credit, 0, ',', '.') }}</td>
                                    <td>
                                        <div class="progress">
                                            @php
                                                // Tentukan warna progress bar berdasarkan persentase
                                                $barColor = 'bg-success'; // Hijau (aman)
                                                if ($supplier->usage_percentage > 75) {
                                                    $barColor = 'bg-danger'; // Merah (awas)
                                                } elseif ($supplier->usage_percentage > 50) {
                                                    $barColor = 'bg-warning'; // Kuning (hati-hati)
                                                }
                                            @endphp
                                            <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $supplier->usage_percentage }}%;" aria-valuenow="{{ $supplier->usage_percentage }}" aria-valuemin="0" aria-valuemax="100">
                                                {{ $supplier->usage_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada supplier dengan limit kredit yang diatur.</td>
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