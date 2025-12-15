<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Deposit Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 4px;
            text-align: left;
        }

        .no-border {
            border: none;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .total {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table tr td {
            border: none;
            padding: 2px 0;
        }

    </style>
</head>
<body>
    <div class="header">
        <h3 style="margin: 0;">LAPORAN DEPOSIT PENJUALAN</h3>
        <p>Periode: {{ $month_from_query }} s/d {{ $month_to_query }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th class="text-center" style="width: 15%;">Nomor</th>
                <th class="text-center" style="width: 12%;">Tanggal</th>
                <th class="text-center" style="width: 15%;">No. SO</th>
                <th class="text-center" style="width: 25%;">Customer</th>
                <th class="text-center" style="width: 15%;">Divisi</th>
                <th class="text-center" style="width: 13%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deposits as $index => $deposit)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $deposit->number }}</td>
                <td class="text-center">{{ Carbon\Carbon::parse($deposit->date)->format('d/m/Y') }}</td>
                <td>{{ $deposit->sales_order_number ?? '-' }}</td>
                <td>{{ $deposit->customer->name ?? '-' }}</td>
                <td>{{ $deposit->division->name ?? '-' }}</td>
                <td class="text-right">{{ rupiah($deposit->amount) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
            
            @if($deposits->count() > 0)
            <tr class="total">
                <td colspan="6" class="text-center">TOTAL</td>
                <td class="text-right">{{ rupiah($total_amount) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <br>
    <div style="font-size: 10px;">
        <p>Dicetak: {{ auth()->user()->name }} - {{ now()->format('d F Y H:i:s') }}</p>
    </div>

</body>
</html>