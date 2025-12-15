<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-end {
            text-align: right !important;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>Period: {{ Carbon\Carbon::parse($monthFrom)->format('F Y') }} - {{ Carbon\Carbon::parse($monthTo)->format('F Y') }}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>TANGGAL</th>
                <th>DIVISI</th>
                <th>TRANSAKSI</th>
                <th>NOMOR INVOICE</th>
                <th>CATATAN</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taxTransactions as $key => $item)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td class="text-center">{{ Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y') }}</td>
                <td>{{ $item->purchase->division->name }}</td>
                <td>{{ $item->purchase->no ?? '-' }}</td>
                <td class="text-center">
                    {{ $item->purchase->invoice_number ?? '-'   }}
                </td>
                <td>{{ $item->purchase->note ?? '-' }}</td>
                <td class="text-end">{{ rupiah($item->amount) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end">TOTAL</th>
                <th class="text-end">{{ rupiah($taxTransactions->sum('amount')) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
