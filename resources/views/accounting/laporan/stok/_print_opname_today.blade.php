<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: 'sans-serif'; line-height: 1.4; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header h2 { margin: 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-size: 11px; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #d9534f; }
        .text-success { color: #5cb85c; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $settings['store_name'] ?? 'Laporan' }}</h1>
        <h2>{{ $reportTitle }}</h2>
        <p>{{ $settings['store_address'] ?? '-' }} | {{ $settings['store_phone'] ?? '-' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">Waktu</th>
                <th>Nama Item</th>
                <th>Tipe</th>
                <th class="text-end">Stok Sistem</th>
                <th class="text-end">Stok Fisik</th>
                <th class="text-end">Selisih (Qty)</th>
                <th>User</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($todayOpnames as $opname)
            <tr>
                <td class="text-center">{{ $opname->timestamp ? \Carbon\Carbon::parse($opname->timestamp)->format('H:i:s') : '-' }}</td>
                <td>{{ $opname->item_name }}</td>
                <td>{{ $opname->item_type }}</td>
                <td class="text-end">{{ number_format($opname->stock_before, 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($opname->stock_after, 2, ',', '.') }}</td>
                <td class="text-end fw-bold @if($opname->adjustment_qty < 0) text-danger @elseif($opname->adjustment_qty > 0) text-success @endif">
                    {{ number_format($opname->adjustment_qty, 2, ',', '.') }}
                </td>
                <td>{{ $opname->user_name }}</td>
                <td>{{ $opname->notes }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Belum ada data stock opname hari ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>