<table class="header-table">
    <thead>
        <tr>
            <td colspan="9" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN PEMBELIAN
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
            <th>VENDOR</th>
            <th>HARGA</th>
            <th>DISC</th>
            <th>TAX</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchases as $key => $item)
        <tr>
            <td>
                {{$key+1 }}
            </td>
            <td>{{$item->division->name }}</td>
            <td>{{$item->invoice_number }}</td>
            <td>{{ Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y') }}</td>
            <td>{{$item->vendor->name }}</td>
            <td class="text-end">{{$item->subtotal}}</td>
            <td class="text-end">{{$item->discount + $item->purchase_discount}}</td>
            <td class="text-end">{{$item->tax}}</td>
            <td class="text-end">{{$item->total_amount}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot> 
        @if($purchases->count() > 0)
        <tr>
            <td colspan="5"><strong>TOTAL</strong></td>
            <td class="text-end"><strong>{{ $purchases->sum('subtotal') }}</strong></td>
            <td class="text-end"><strong>{{ $purchases->sum(fn($item) => $item->discount + $item->purchase_discount) }}</strong></td>
            <td class="text-end"><strong>{{ $purchases->sum('tax') }}</strong></td>
            <td class="text-end"><strong>{{ $purchases->sum('total_amount') }}</strong></td>
        </tr>
        @endif
    </tfoot>
</table>
