<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Purchase Price Change Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 30px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
        }

        th {
            background: #f3f3f3;
            text-align: center;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .item-name {
            width: 400px;
            min-width: 400px;
            max-width: 400px;
        }

        .bg-footer {
            background: #efefef;
        }

        .vendor-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .vendor-info {
            font-size: 12px;
            margin-bottom: 15px;
        }

    </style>
</head>
<body>

    @foreach ($reports as $report)
    <div class="vendor-title">
        {{ $report['vendor']['code'] }} — {{ $report['vendor']['name'] }}
    </div>
    <div class="vendor-info">
        Total Pembelian: Rp{{ number_format($report['total_purchase'], 0, ',', '.') }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="item-name" rowspan="2">Nama Item</th>
                @foreach ($report['months'] as $month)
                <th colspan="3">{{ $month['name'] }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($report['months'] as $month)
                <th class="text-right">Harga</th>
                <th class="text-right">Jumlah</th>
                <th class="text-right">Total</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
                @for ($i = 0; $i < count($report['months'][0]['items']); $i++) <tr>
                    <td class="item-name">{{ $report['months'][0]['items'][$i]['name'] }}
                    </td>
                    @foreach ($report['months'] as $month)
                    <td class="text-right">Rp{{ number_format($month['items'][$i]['harga'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ $month['items'][$i]['jumlah'] }}</td>
                    <td class="text-right">Rp{{ number_format($month['items'][$i]['total'], 0, ',', '.') }}</td>
                    @endforeach
                    </tr>
                    
                    <!-- Warehouse breakdown rows -->
                    @php
                        $allWarehouses = [];
                        foreach ($report['months'] as $month) {
                            if (isset($month['items'][$i]['warehouses'])) {
                                foreach ($month['items'][$i]['warehouses'] as $warehouse) {
                                    $allWarehouses[$warehouse['warehouse_name']] = $warehouse['warehouse_name'];
                                }
                            }
                        }
                        $allWarehouses = array_values($allWarehouses);
                    @endphp
                    @if(count($allWarehouses) > 0)
                        @foreach($allWarehouses as $warehouseName)
                        <tr style="background-color: #f9f9f9;">
                            <td style="padding-left: 20px; font-weight: bold;">{{ $warehouseName }}</td>
                            
                            @foreach ($report['months'] as $month)
                                @php
                                    $warehouseData = collect($month['items'][$i]['warehouses'])->where('warehouse_name', $warehouseName)->first();
                                @endphp
                                @if($warehouseData)
                                <td class="text-right">Rp{{ number_format($warehouseData['harga'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ $warehouseData['jumlah'] }}</td>
                                <td class="text-right">Rp{{ number_format($warehouseData['total'], 0, ',', '.') }}</td>
                                @else
                                <td class="text-right">-</td>
                                <td class="text-right">-</td>
                                <td class="text-right">-</td>
                                @endif
                            @endforeach
                        </tr>
                        @endforeach
                    @endif
                    
                    @endfor
        </tbody>
        <tfoot>
            <tr class="bg-footer font-bold">
                <td class="item-name">Total</td>
                @foreach ($report['months'] as $month)
                <td class="text-right">
                    Rp{{ number_format(collect($month['items'])->sum('harga'), 0, ',', '.') }}
                </td>
                <td class="text-right">
                    {{ collect($month['items'])->sum('jumlah') }}
                </td>
                <td class="text-right">
                    Rp{{ number_format($month['monthTotal'], 0, ',', '.') }}
                </td>
                @endforeach
            </tr>
        </tfoot>
    </table>
    @endforeach

</body>
</html>