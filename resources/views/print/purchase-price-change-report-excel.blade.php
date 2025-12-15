<table class="header-table">
    <thead>
        <tr>
            <td colspan="{{ 4 + (count($reports[0]['months']) * 3) }}" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN PERUBAHAN HARGA PEMBELIAN
            </td>
        </tr>
    </thead>
</table>
<table>
    <thead>
        <tr>
            <th rowspan="2">Kode Vendor</th>
            <th rowspan="2">Nama Vendor</th>
            <th rowspan="2">Nama Item</th>
            @foreach ($reports[0]['months'] as $month)
            <th colspan="3">{{ $month['name'] }}</th>
            @endforeach
            <th rowspan="2">TOTAL KESELURUHAN</th>
        </tr>
        <tr>
            @foreach ($reports[0]['months'] as $month)
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Total</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($reports as $report)
        @for ($i = 0; $i < count($report['months'][0]['items']); $i++)
        <tr>
            <td>{{ $report['vendor']['code'] }}</td>
            <td>{{ $report['vendor']['name'] }}</td>
            <td>{{ $report['months'][0]['items'][$i]['name'] }}
           
            </td>

            @foreach ($report['months'] as $month)
            <td>{{ $month['items'][$i]['harga'] }}</td>
            <td>{{ $month['items'][$i]['jumlah'] }}</td>
            <td>{{ $month['items'][$i]['total'] }}</td>
            @endforeach

            <!-- Total keseluruhan bulan untuk item ini -->
            <td>{{ rupiah(collect($report['months'])->sum(function($month) use ($i) { return $month['items'][$i]['total']; })) }}</td>
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
                <td></td>
                <td></td>
                <td style="padding-left: 20px; font-weight: bold;">{{ $warehouseName }}</td>
                
                @foreach ($report['months'] as $month)
                    @php
                        $warehouseData = collect($month['items'][$i]['warehouses'])->where('warehouse_name', $warehouseName)->first();
                    @endphp
                    @if($warehouseData)
                    <td>{{ $warehouseData['harga'] }}</td>
                    <td>{{ $warehouseData['jumlah'] }}</td>
                    <td>{{ $warehouseData['total'] }}</td>
                    @else
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    @endif
                @endforeach
                
                <!-- Total keseluruhan warehouse untuk semua bulan -->
                <td>{{ rupiah(collect($report['months'])->sum(function($month) use ($i, $warehouseName) { 
                    $warehouseData = collect($month['items'][$i]['warehouses'])->where('warehouse_name', $warehouseName)->first();
                    return $warehouseData ? $warehouseData['total'] : 0;
                })) }}</td>
            </tr>
            @endforeach
        @endif
        
        @endfor

        <!-- Total Pembelian per Vendor -->
        <tr style="background-color: #f0f0f0; font-weight: bold;">
            <td>{{ $report['vendor']['code'] }}</td>
            <td>{{ $report['vendor']['name'] }}</td>
            <td><strong>TOTAL PEMBELIAN</strong></td>

            @foreach ($report['months'] as $month)
            <td>-</td>
            <td>-</td>
            <td><strong>{{ rupiah(collect($month['items'])->sum('total')) }}</strong></td>
            @endforeach

            <!-- Total keseluruhan pembelian vendor semua bulan -->
            <td style="background-color: #d0d0d0;"><strong>{{ rupiah($report['total_purchase']) }}</strong></td>
        </tr>

        <!-- Empty row for spacing between vendors -->
        <tr>
            <td colspan="{{ 4 + (count($report['months']) * 3) }}">&nbsp;</td>
        </tr>
        @endforeach
    </tbody>
</table>