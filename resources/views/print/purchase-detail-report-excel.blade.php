<table class="header-table">
    <thead>
        <tr>
            <td colspan="9" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN DETAIL PEMBELIAN
            </td>
        </tr>
    </thead>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th colspan="2">INVOICE</th>
            <th>TANGGAL</th>
            <th>DIVISI</th>
            <th>VENDOR</th>
            <th>@HARGA</th>
            <th>HARGA</th>
            <th>DISC</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchases as $key => $item)
        <tr>
            <td class="border-0 border-l border-t">
                {{$key+1 }}
            </td>
            <td class="border-0 border-t" colspan="2">{{ $item->invoice_number }}</td>
            <td class="border-0 border-t" >{{ Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y') }}</td>
            <td class="border-0 border-t">{{$item->division->code }} {{$item->division->name }}</td>
            <td class="border-0 border-t">{{$item->vendor->code }} {{$item->vendor->name }}</td>
            <td class="border-0 text-end border-t"></td>
            <td class="border-0 text-end border-t"></td>
            <td class="border-0 text-end border-t"></td>
            <td class="border-0 text-end border-t border-r"></td>
        </tr>
        @foreach($item->items as $purchaseItem)
        <tr>
            <td class="border-0 border-l">
            </td>
            <td class="border-0">
                {{$purchaseItem->quantity}}
            </td>
            <td class="border-0">
                {{$purchaseItem->unit ?? ''}}
            </td>
            <td class="border-0" colspan="3">
                {{$purchaseItem->item?->name ?? $purchaseItem->item_name}}
            </td>
            <td class="border-0 text-end">
                {{$purchaseItem->price}}
            </td>
            <td class="border-0 text-end">
                {{$purchaseItem->subtotal}}
            </td>
            <td class="border-0 text-end ">
                {{$purchaseItem->discount}}
            </td>
            <td class="border-0 text-end border-r">
                {{$purchaseItem->total}}
            </td>
        </tr>
        @endforeach
        <tr>
            <td class="border-0 border-l border-b">

            </td>
            <td class="border-0 border-b" colspan="2">
                DISC : {{$item->discount}}
            </td>
            <td class="border-0 border-b" colspan="3">
                TAX : {{$item->tax}}
            </td>
            <td class="border-0 border-b border-t">IDR</td>
            <td class="border-0 border-b border-t text-end">{{ $item->subtotal }}</td>
            <td class="border-0 border-b border-t text-end">{{$item->discount + $item->purchase_discount}}</td>
            <td class="border-0 border-b border-t text-end border-r">{{$item->total_amount}}</td>
        </tr>
        @endforeach
    </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="border-1 border-r-0 text-end">
                    GRAND TOTAL
                </td>
                <td class="border-1 text-end">
                    IDR
                </td>
                <td class="border-1 text-end">
                    {{$purchases->sum('subtotal')}}
                </td>
                <td class="border-1 text-end">
                    {{$purchases->sum('discount') + $purchases->sum('purchase_discount')}}
                </td>
                <td class="border-1 text-end border-r">
                    {{$purchases->sum('total_amount')}}
                </td>
            </tr>
        </tfoot>
</table>
