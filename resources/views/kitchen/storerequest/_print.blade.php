<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Store Requisition - {{ $storeRequest->request_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
        }
        .container {
            width: 95%;
            margin: 20px auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .header-table td {
            border: none;
        }
        .items-table th {
            text-align: center;
            font-weight: bold;
        }
        .items-table td {
            height: 25px; /* Memberi tinggi pada baris kosong */
        }
        .approval-table td {
            border: none;
            padding: 4px 8px;
        }
        .text-center {
            text-align: center;
        }
        .no-border {
            border: none;
        }
        .remarks-header {
            font-weight: bold;
            border-right: none;
        }
        .remarks-content {
            border-left: none;
        }

        /* Styling untuk tombol print agar tidak ikut tercetak */
        .print-button-container {
            text-align: center;
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none;
            }
            @page {
                size: A4;
                margin: 20mm;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="no-print print-button-container">
            <button onclick="window.print()">Cetak Halaman</button>
        </div>

        <h2>STORE REQUISITION</h2>

        {{-- BAGIAN HEADER --}}
        <table class="header-table">
            <tr>
                <td style="width: 15%;">DEPT. :</td>
                <td style="width: 45%;"></td>
                <td style="width: 15%;">DATE</td>
                <td style="width: 25%;">: {{ $storeRequest->created_at->toDateString() }}</td>
            </tr>
            <tr>
                <td>DEPT. TO CHARGE :</td>
                <td></td>
                <td>STORE</td>
                <td>: </td> {{-- Bisa dibuat dinamis jika perlu --}}
            </tr>
        </table>

        {{-- BAGIAN ITEM --}}
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 15%;">STORE CODE</th>
                    <th style="width: 45%;">DESCRIPTION</th>
                    <th style="width: 20%;">REQUISITION QUANTITY</th>
                    <th style="width: 20%;">ISSUED QUANTITY</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop untuk item yang ada di store request --}}
                @foreach($storeRequest->items as $item)
                <tr>
                    <td class="text-center">{{ $item->ingredient->id }}</td>
                    <td>{{ $item->ingredient->name }}</td>
                    <td class="text-center">{{ $item->requested_quantity }} {{ $item->ingredient->unit }}</td>
                    <td class="text-center">{{ $item->issued_quantity }} {{ $item->ingredient->unit }}</td>
                </tr>
                @endforeach

                {{-- Loop untuk membuat baris kosong agar tabel terlihat penuh --}}
                @for ($i = count($storeRequest->items); $i < 15; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            </tbody>
        </table>

        {{-- BAGIAN REMARKS --}}
        <table>
            <tr>
                <th class="remarks-header" style="width: 15%;">REMARKS</th>
                <td class="remarks-content">{{ $storeRequest->remarks }}</td>
            </tr>
        </table>

        {{-- BAGIAN PERSETUJUAN --}}
        <table class="approval-table">
            <tr>
                <td style="width: 15%;">APPROVED BY</td>
                <td style="width: 2%;">:</td>
                <td style="width: 43%;"></td>
                <td style="width: 10%;">DATE</td>
                <td style="width: 2%;">:</td>
                <td style="width: 28%;"></td>
            </tr>
            <tr>
                <td>ISSUED BY</td>
                <td>:</td>
                <td>
                    {{ $storeRequest->issuer && $storeRequest->issuer->name ? $storeRequest->issuer->name : '..............................' }}
                </td>
                <td>DATE</td>
                <td>:</td>
                <td>
                    {{ $storeRequest->issued_at ? \Carbon\Carbon::parse($storeRequest->issued_at)->format('d-m-Y') : '..............................' }}
                </td>
            </tr>
            <tr>
                <td>RECEIVED BY</td>
                <td>:</td>
                <td></td>
                <td>DATE</td>
                <td>:</td>
                <td></td>
            </tr>
        </table>

    </div>

    <script>
        // Otomatis membuka dialog print saat halaman dimuat
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>