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
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        @if($search)
        <p>Search: {{ $search }}</p>
        @endif
        @if($selectedWarehouses && count($selectedWarehouses) > 0)
        <p>Selected Warehouses: {{ $warehouses->pluck('name')->implode(', ') }}</p>
        @endif
        <p>Generated on: {{ date('d/m/Y H:i:s') }}</p>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>KODE ITEM</th>
                <th>NAMA ITEM</th>
                <th>STOCK TOTAL</th>
                @if($selectedWarehouses && count($selectedWarehouses) > 0)
                    @foreach($warehouses as $warehouse)
                    <th>{{ $warehouse->name }}</th>
                    @endforeach
                @else
                    @foreach($warehouses as $warehouse)
                    <th>{{ $warehouse->name }}</th>
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($items as $key => $item)
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                <td class="text-center">{{ $item->stock }}</td>
                @if($selectedWarehouses && count($selectedWarehouses) > 0)
                    @foreach($warehouses as $warehouseIndex => $warehouse)
                    <td class="text-center">
                        @if($item->is_linked)
                            0
                        @else
                            {{ $item->warehouses[$warehouseIndex]->quantity ?? '0' }}
                        @endif
                    </td>
                    @endforeach
                @else
                    @foreach($warehouses as $warehouseIndex => $warehouse)
                    <td class="text-center">
                        @if($item->is_linked)
                            0
                        @else
                            {{ $item->warehouses[$warehouseIndex]->quantity ?? '0' }}
                        @endif
                    </td>
                    @endforeach
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
