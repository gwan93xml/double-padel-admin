<table class="header-table">
    <thead>
        <tr>
            <td colspan="9" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN PENJUALAN
            </td>
        </tr>
    </thead>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th>DIVISI</th>
            <th>NO. FAKTUR</th>
            <th>TANGGAL</th>
            <th>PELANGGAN</th>
            <th>HARGA</th>
            <th>DISC</th>
            <th>TAX</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $key => $item)
        <tr>
            <td>
                {{$key+1 }}
            </td>
            <td>{{$item->division->name }}</td>
            <td>{{$item->no }}</td>
            <td>{{ Carbon\Carbon::parse($item->sale_date)->format('d/m/Y') }}</td>
            <td>{{$item->customer->name }}</td>
            <td class="text-end">{{$item->subtotal}}</td>
            <td class="text-end">{{$item->discount + $item->sales_discount}}</td>
            <td class="text-end">{{$item->tax}}</td>
            <td class="text-end">{{$item->total_amount}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td colspan="5" style="text-align:center;">TOTAL</td>
            <td class="text-end">{{$sales->sum('subtotal')}}</td>
            <td class="text-end">{{$sales->sum('discount') + $sales->sum('sales_discount')}}</td>
            <td class="text-end">{{$sales->sum('tax')}}</td>
            <td class="text-end">{{$sales->sum('total_amount')}}</td>
        </tr>
    </tfoot>
</table>
