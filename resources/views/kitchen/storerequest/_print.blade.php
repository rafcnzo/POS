<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Requisition Form - {{ $storeRequest->request_number ?? 'N/A' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 10mm 15mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 10px;
            background: white;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .header {
            width: 100%;
            margin-bottom: 8px;
            position: relative;
            height: 65px;
        }

        .logo-section {
            position: absolute;
            left: 0;
            top: 0;
        }

        .logo {
            width: 60px;
            height: 60px;
            float: left;
            margin-right: 12px;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 5px;
        }

        .company-name {
            float: left;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.3;
            padding-top: 10px;
            max-width: 120px;
            word-wrap: break-word;
        }

        .form-number {
            position: relative;
            right: 0;
            top: 10px;
            float: right;
            font-size: 14px;
            font-weight: bold;
        }

        .clear {
            clear: both;
        }

        .form-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            margin-top: 5px;
        }

        .info-line {
            width: 100%;
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: bold;
        }

        .info-row {
            width: 100%;
        }

        .dept-section {
            width: 48%;
            float: left;
        }

        .date-section {
            width: 48%;
            float: right;
        }

        .dept-section,
        .date-section {
            line-height: 1.5;
        }

        .label-text {
            display: inline;
            margin-right: 3px;
        }

        .underline {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-width: 453px;
            padding-bottom: 2px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .main-table {
            border: 1.5px solid #000;
        }

        .main-table th,
        .main-table td {
            border: 1px solid #000;
            padding: 8px 6px;
            font-size: 13px;
        }

        .main-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .col-no {
            width: 50px;
            text-align: center;
        }

        .col-item {
            width: auto;
        }

        .col-qty {
            width: 80px;
            text-align: center;
        }

        .col-unit {
            width: 80px;
            text-align: center;
        }

        .col-remark {
            width: 200px;
        }

        .data-row {
            height: 35px;
        }

        .data-row td {
            background-color: #f5f5f5;
        }

        /* Approval Section */
        .approval-section {
            margin-top: 20px;
            width: 100%;
        }

        .approval-labels {
            width: 100%;
            margin-bottom: 5px;
        }

        .approval-label {
            width: 33%;
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            float: left;
        }

        .signature-section {
            width: 100%;
            margin-top: 55px;
        }

        .signature-box {
            width: 33%;
            text-align: center;
            float: left;
        }

        .signature-title {
            font-size: 13px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    @if (isset($settings['store_logo']) && file_exists(public_path('storage/' . $settings['store_logo'])))
                        @php
                            $logoPath = public_path('storage/' . $settings['store_logo']);
                            $logoData = base64_encode(file_get_contents($logoPath));
                            $logoExt = pathinfo($logoPath, PATHINFO_EXTENSION);
                            $logoMime = $logoExt === 'png' ? 'png' : 'jpeg';
                        @endphp
                        <img src="data:image/{{ $logoMime }};base64,{{ $logoData }}" alt="Logo">
                    @endif
                </div>
                <div class="company-name">{{ strtoupper($settings['store_name'] ?? 'MIZUMI ONSEN') }}</div>
            </div>
            <div class="form-number">No: {{ $storeRequest->request_number ?? 'SR-0001' }}</div>
        </div>

        <div class="clear"></div>

        <div class="form-title">STORE REQUISITION</div>

        <!-- Info Line -->
        <div class="info-line">
            <div class="info-row">
                <div class="dept-section">
                    <span class="label-text">DEPT:</span>
                    <span class="underline">{{ $storeRequest->department ?? '' }}</span>
                </div>
                <div class="date-section">
                    <span class="label-text">TANGGAL:</span>
                    <span
                        class="underline">{{ $storeRequest->created_at ? $storeRequest->created_at->format('d/m/Y') : '' }}</span>
                </div>
            </div>
        </div>

        <div class="clear"></div>

        <!-- Table -->
        <table class="main-table">
            <thead>
                <tr>
                    <th class="col-no">NO.</th>
                    <th class="col-item">ITEM</th>
                    <th class="col-qty">QTY</th>
                    <th class="col-unit">UNIT</th>
                    <th class="col-remark">REMARK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($storeRequest->items as $index => $item)
                    @if ($index < 10)
                        <tr class="data-row">
                            <td class="col-no">{{ $index + 1 }}</td>
                            <td class="col-item">{{ $item->ingredient->name ?? '' }}</td>
                            <td class="col-qty">{{ $item->quantity ?? '' }}</td>
                            <td class="col-unit">{{ $item->ingredient->unit ?? '' }}</td>
                            <td class="col-remark">{{ $item->notes ?? '' }}</td>
                        </tr>
                    @endif
                @empty
                @endforelse

                @for ($i = count($storeRequest->items); $i < 10; $i++)
                    <tr class="data-row">
                        <td class="col-no">{{ $i + 1 }}</td>
                        <td class="col-item"></td>
                        <td class="col-qty"></td>
                        <td class="col-unit"></td>
                        <td class="col-remark"></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- Approval Section -->
        <div class="approval-section">
            <div class="approval-labels">
                <div class="approval-label">Requested</div>
                <div class="approval-label">Checked</div>
                <div class="approval-label">Approved</div>
            </div>

            <div class="clear"></div>

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-title">Departement</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">Accounting</div>
                </div>
                <div class="signature-box">
                    <div class="signature-title">General Manager</div>
                </div>
            </div>

            <div class="clear"></div>
        </div>
    </div>
</body>

</html>
