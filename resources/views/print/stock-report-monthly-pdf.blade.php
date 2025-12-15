<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Stok Bulanan</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 4px;
        }

        th {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

    </style>
</head>
<body>
    <h2 style="text-align:center">Laporan Rekap Stok Bulanan</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($fromDate)->format('F Y') }}
        – {{ \Carbon\Carbon::parse($toDate)->format('F Y') }}</p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Item</th>
                @foreach($warehouses as $w)
                <th colspan="1">{{ $w->name }}</th>
                @endforeach
                <th rowspan="2">Total</th>
            </tr>
            <tr>
                @foreach($warehouses as $w)
                <th>Balance</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td>{{ $item['name'] }} <small>{{ $item['code'] }}</small></td>
                @foreach($item['warehouses'] as $wh)
                <td class="text-right">{{ $wh['ending_balance'] }}</td>
                @endforeach
                <td class="text-right">{{ $item['total'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
