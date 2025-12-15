@extends('print.layout-landscape')
@section('content')
    <div class="header">
        <h1>Daftar Penjualan Periode {{$month_from_query}} - {{$month_to_query}}</h1>
    </div>
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
                <td class="border-0 border-t">{{ $item->purchase_order_number ?? '-' }}</td>
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
                    {{$saleItem->quantity}} {{$saleItem->unit ?? ''}}
                </td>
                <td class="border-0" colspan="3">
                    {{$saleItem->item?->name ?? $saleItem->item_name}}
                </td>
                <td class="border-0 text-end">
                    {{rupiah($saleItem->price)}}
                </td>
                <td class="border-0 text-end">
                    {{rupiah($saleItem->subtotal)}}
                </td>
                <td class="border-0 text-end ">
                    {{rupiah(0)}}
                </td>
                <td class="border-0 text-end border-r">
                    {{rupiah($saleItem->total)}}
                </td>
            </tr>
            @endforeach
            <tr>
                <td class="border-0 border-l border-b">

                </td>
                <td class="border-0 border-b" colspan="2">
                    DISC : {{rupiah($item->discount)}}
                </td>
                <td class="border-0 border-b" colspan="2">
                    TAX : {{rupiah($item->tax)}}
                </td>
                <td class="border-0 border-b border-t">IDR</td>
                <td class="border-0 border-b border-t text-end">{{ rupiah($item->subtotal) }}</td>
                <td class="border-0 border-b border-t text-end">{{rupiah($item->discount + $item->sales_discount)}}</td>
                <td class="border-0 border-b border-t text-end border-r">{{rupiah($item->total_amount)}}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="border-0 border-l border-t border-b" colspan="5">TOTAL</td>
                <td class="border-0 border-t border-b"></td>
                <td class="border-0 border-t border-b text-end">{{ rupiah($sales->sum('subtotal')) }}</td>
                <td class="border-0 border-t border-b text-end">{{ rupiah($sales->sum('discount') + $sales->sum('sales_discount')) }}</td>       
                <td class="border-0 border-t border-b text-end border-r">{{ rupiah($sales->sum('total_amount')) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
