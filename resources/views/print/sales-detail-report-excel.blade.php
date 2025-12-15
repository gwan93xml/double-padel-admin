<table class="header-table">
    <thead>
        <tr>
            <td colspan="9" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN DETAIL PENJUALAN
            </td>
        </tr>
    </thead>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th>No. Faktur</th>
            <th>No. PO</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>@HARGA</th>
            <th>HARGA</th>
            <th>DISC</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sales as $key => $item)
        <tr>
            <td class="border-0 border-l border-t">
                {{$key+1 }}
            </td>
            <td class="border-0 border-t">{{ $item->no }}</td>
            <td class="border-0 border-t" colspan="2">{{ $item->purchase_order_number ?? '-' }}</td>
            <td class="border-0 border-t">{{ Carbon\Carbon::parse($item->sale_date)->format('d/m/Y') }}</td>
            <td class="border-0 border-t">{{$item->customer->code }} {{$item->customer->name }}</td>
            <td class="border-0 text-end border-t"></td>
            <td class="border-0 text-end border-t"></td>
            <td class="border-0 text-end border-t"></td>
            <td class="border-0 text-end border-t border-r"></td>
        </tr>
        @foreach($item->items as $saleItem)
        <tr>
            <td class="border-0 border-l">
            </td>
            <td class="border-0">
                {{$saleItem->quantity}}
            </td>
            <td class="border-0">
                {{$saleItem->unit ?? ''}}
            </td>
            <td class="border-0" colspan="3">
                {{$saleItem->item?->name ?? $saleItem->item_name}}
            </td>
            <td class="border-0 text-end">
                {{$saleItem->price}}
            </td>
            <td class="border-0 text-end">
                {{$saleItem->subtotal}}
            </td>
            <td class="border-0 text-end ">
                {{0}}
            </td>
            <td class="border-0 text-end border-r">
                {{$saleItem->total}}
            </td>
        </tr>
        @endforeach
        <tr>
            <td class="border-0 border-l border-b">

            </td>
            <td class="border-0 border-b" colspan="3">
                DISC : {{$item->discount}}
            </td>
            <td class="border-0 border-b" colspan="2">
                TAX : {{$item->tax}}
            </td>
            <td class="border-0 border-b border-t">IDR</td>
            <td class="border-0 border-b border-t text-end">{{ $item->subtotal }}</td>
            <td class="border-0 border-b border-t text-end">{{$item->discount + $item->sales_discount}}</td>
            <td class="border-0 border-b border-t text-end border-r">{{$item->total_amount}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="total">
            <td colspan="6" style="text-align:center;">TOTAL</td>
            <td class="text-end">{{$sales->sum('subtotal')}}</td>
            <td class="text-end">{{$sales->sum('discount') + $sales->sum('sales_discount')}}</td>
            <td class="text-end">{{$sales->sum('tax')}}</td>
            <td class="text-end">{{$sales->sum('total_amount')}}</td>
        </tr>
    </tfoot>
</table>
