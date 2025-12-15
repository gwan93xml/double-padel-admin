<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Perhitungan Stok</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .info {
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .summary {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-label {
            font-weight: bold;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .transaction-in {
            color: #28a745;
        }
        .transaction-out {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PERHITUNGAN STOK</h1>
    </div>

    <div class="info">
        <div class="info-row">
            <strong>Periode:</strong> {{ $from_date }} s/d {{ $to_date }}
        </div>
        <div class="info-row">
            <strong>Barang:</strong> {{ $item?->name ?? 'N/A' }}
        </div>
        @if($warehouse)
        <div class="info-row">
            <strong>Gudang:</strong> {{ $warehouse->name }}
        </div>
        @endif
        <div class="info-row">
            <strong>Tanggal Cetak:</strong> {{ date('d/m/Y H:i:s') }}
        </div>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">Saldo Awal:</span>
            <span>{{ $starting_balance }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Masuk:</span>
            <span class="transaction-in">{{ $total_in }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Keluar:</span>
            <span class="transaction-out">{{ $total_out }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Saldo Akhir:</span>
            <span><strong>{{ $closing_balance }}</strong></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">Tanggal</th>
                <th class="text-center">Referensi</th>
                <th>Keterangan</th>
                <th>Customer/Vendor</th>
                <th class="text-center">Masuk</th>
                <th class="text-center">Keluar</th>
                <th class="text-center">Saldo</th>
                @if(!$warehouse)
                <th class="text-center">Gudang</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
            <tr>
                <td class="text-center">{{ $transaction->date }}</td>
                <td class="text-center">{{ $transaction->reference_id }}</td>
                <td>{{ $transaction->description ?? '-' }}</td>
                <td>
                    @if(isset($transaction->customer))
                        {{ $transaction->customer->name }} ({{ $transaction->customer->code }})
                    @elseif(isset($transaction->vendor))
                        {{ $transaction->vendor->name }} ({{ $transaction->vendor->code }})
                    @else
                        -
                    @endif
                </td>
                <td class="text-right {{ $transaction->transaction_type === 'IN' ? 'transaction-in' : '' }}">
                    {{ $transaction->transaction_type === 'IN' ? $transaction->quantity : '-' }}
                </td>
                <td class="text-right {{ $transaction->transaction_type === 'OUT' ? 'transaction-out' : '' }}">
                    {{ $transaction->transaction_type === 'OUT' ? $transaction->quantity : '-' }}
                </td>
                <td class="text-right">{{ $transaction->balance }}</td>
                @if(!$warehouse)
                <td class="text-center">{{ $transaction->warehouse?->name ?? '-' }}</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $warehouse ? 7 : 8 }}" class="text-center">Tidak ada data transaksi</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
