<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rincian Sisa Stok</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 11px;
        }

        .filter-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #dee2e6;
        }

        .filter-info h3 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #333;
        }

        .filter-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .filter-row:last-child {
            margin-bottom: 0;
        }

        .filter-label {
            font-weight: bold;
            color: #555;
            width: 120px;
        }

        .filter-value {
            color: #333;
            flex: 1;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }

        .summary-card {
            flex: 1;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
        }

        .summary-card.starting {
            border-left: 4px solid #007bff;
        }

        .summary-card.in {
            border-left: 4px solid #28a745;
        }

        .summary-card.out {
            border-left: 4px solid #dc3545;
        }

        .summary-card.closing {
            border-left: 4px solid #6f42c1;
        }

        .summary-card h4 {
            margin: 0 0 5px 0;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }

        .summary-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .table-container {
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        th {
            background: #f8f9fa;
            color: #333;
            font-weight: bold;
            padding: 8px 6px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        td {
            padding: 6px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        tr:hover {
            background: #e3f2fd;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }

        .badge.in {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .badge.out {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .badge.balance-positive {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .badge.balance-negative {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .badge.balance-zero {
            background: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .customer-vendor {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .customer-vendor .icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
            color: white;
        }

        .customer-vendor .icon.customer {
            background: #28a745;
        }

        .customer-vendor .icon.vendor {
            background: #007bff;
        }

        .customer-vendor .details {
            flex: 1;
        }

        .customer-vendor .name {
            font-weight: bold;
            margin: 0;
            font-size: 11px;
        }

        .customer-vendor .code {
            color: #666;
            margin: 0;
            font-size: 9px;
        }

        .warehouse-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .warehouse-info .icon {
            width: 16px;
            height: 16px;
            background: #6c757d;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: white;
        }

        .warehouse-info .details {
            flex: 1;
        }

        .warehouse-info .name {
            font-weight: bold;
            margin: 0;
            font-size: 11px;
        }

        .warehouse-info .code {
            color: #666;
            margin: 0;
            font-size: 9px;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }

        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            
            .header {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>RINCIAN SISA STOK</h1>
        <p>Detail Pergerakan Stok Item Per Transaksi</p>
    </div>

    <div class="filter-info">
        <h3>Informasi Filter</h3>
        <div class="filter-row">
            <span class="filter-label">Periode:</span>
            <span class="filter-value">{{ \Carbon\Carbon::parse($from_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($to_date)->format('d/m/Y') }}</span>
        </div>
        @if($item)
        <div class="filter-row">
            <span class="filter-label">Item:</span>
            <span class="filter-value">{{ $item->code }} - {{ $item->name }}</span>
        </div>
        @endif
        @if($warehouse)
        <div class="filter-row">
            <span class="filter-label">Gudang:</span>
            <span class="filter-value">{{ $warehouse->code }} - {{ $warehouse->name }}</span>
        </div>
        @else
        <div class="filter-row">
            <span class="filter-label">Gudang:</span>
            <span class="filter-value">Semua Gudang</span>
        </div>
        @endif
        <div class="filter-row">
            <span class="filter-label">Dicetak pada:</span>
            <span class="filter-value">{{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</span>
        </div>
    </div>

    <div class="summary-cards">
        <div class="summary-card starting">
            <h4>Saldo Awal</h4>
            <p class="value">{{ $starting_balance }}</p>
        </div>
        <div class="summary-card in">
            <h4>Total Masuk</h4>
            <p class="value">{{ $total_in }}</p>
        </div>
        <div class="summary-card out">
            <h4>Total Keluar</h4>
            <p class="value">{{ $total_out }}</p>
        </div>
        <div class="summary-card closing">
            <h4>Saldo Akhir</h4>
            <p class="value">{{ $closing_balance }}</p>
        </div>
    </div>

    @if(count($transactions) > 0)
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Tanggal</th>
                    <th style="width: 200px;">Keterangan</th>
                    <th style="width: 150px;">Customer/Vendor</th>
                    <th style="width: 80px;" class="text-center">Debet</th>
                    <th style="width: 80px;" class="text-center">Kredit</th>
                    <th style="width: 90px;" class="text-right">Saldo</th>
                    <th style="width: 120px;">Gudang</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($transaction->date)->format('d/m/Y') }}
                    </td>
                    <td>
                        <div>{{ $transaction->remarks }}</div>
                        @if($transaction->linking_item)
                        <div style="margin-top: 3px;">
                            <small style="color: #666;">{{ $transaction->linking_item->name }}</small>
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($transaction->customer)
                        <div class="customer-vendor">
                            <div class="icon customer">C</div>
                            <div class="details">
                                <p class="name">{{ $transaction->customer->name }}</p>
                                <p class="code">{{ $transaction->customer->code }}</p>
                            </div>
                        </div>
                        @elseif($transaction->vendor)
                        <div class="customer-vendor">
                            <div class="icon vendor">V</div>
                            <div class="details">
                                <p class="name">{{ $transaction->vendor->name }}</p>
                                <p class="code">{{ $transaction->vendor->code }}</p>
                            </div>
                        </div>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($transaction->transaction_type === 'IN')
                        <span class="badge in">+{{ $transaction->quantity }}</span>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($transaction->transaction_type === 'OUT')
                        <span class="badge out">-{{ $transaction->quantity }}</span>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @php
                            $balanceFloat = (float) str_replace(['+', '-', ' '], '', $transaction->balance);
                        @endphp
                        <span class="badge {{ $balanceFloat > 0 ? 'balance-positive' : ($balanceFloat < 0 ? 'balance-negative' : 'balance-zero') }}">
                            {{ $transaction->balance }}
                        </span>
                    </td>
                    <td>
                        @if($transaction->warehouse)
                        <div class="warehouse-info">
                            <div class="icon">W</div>
                            <div class="details">
                                <p class="name">{{ $transaction->warehouse->name }}</p>
                                <p class="code">{{ $transaction->warehouse->code }}</p>
                            </div>
                        </div>
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="no-data">
        <p>Tidak ada transaksi untuk periode yang dipilih</p>
    </div>
    @endif

    <div class="footer">
        <p>Laporan Rincian Sisa Stok - Dicetak pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
