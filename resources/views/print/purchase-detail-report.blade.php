@extends('print.layout-landscape')
@section('content')
<div class="container">
    <div class="header">
        <h1>Daftar Pembelian Periode {{$month_from_query}} - {{$month_to_query}}</h1>
    </div>
    <table class="items-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>INVOICE</th>
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
                <td class="border-0 border-t">{{ $item->invoice_number }}</td>
                <td class="border-0 border-t">{{ Carbon\Carbon::parse($item->purchase_date)->format('d/m/Y') }}</td>
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
                <td class="border-0" >
                    {{$purchaseItem->quantity}} {{$purchaseItem->unit ?? ''}}
                </td>
                <td class="border-0" colspan="3">
                    {{$purchaseItem->item?->name ?? $purchaseItem->item_name}}
                </td>
                <td class="border-0 text-end">
                    {{rupiah($purchaseItem->price)}}
                </td>
                <td class="border-0 text-end">
                    {{rupiah($purchaseItem->subtotal)}}
                </td>
                <td class="border-0 text-end ">
                    {{rupiah(0)}}
                </td>
                <td class="border-0 text-end border-r">
                    {{rupiah($purchaseItem->total)}}
                </td>
            </tr>
            @endforeach
            <tr>
                <td class="border-0 border-l border-b">

                </td>
                <td class="border-0 border-b">
                    DISC : {{rupiah($item->discount)}}
                </td>
                <td class="border-0 border-b" colspan="3">
                    TAX : {{rupiah($item->tax)}}
                </td>
                <td class="border-0 border-b border-t">IDR</td>
                <td class="border-0 border-b border-t text-end">{{ rupiah($item->subtotal) }}</td>
                <td class="border-0 border-b border-t text-end">{{rupiah($item->discount + $item->purchase_discount)}}</td>
                <td class="border-0 border-b border-t text-end border-r">{{rupiah($item->total_amount)}}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="border-1 border-r-0 text-end">
                    GRAND TOTAL
                </td>
                <td class="border-1 text-end">
                    IDR
                </td>
                <td class="border-1 text-end">
                    {{rupiah($purchases->sum('subtotal'))}}
                </td>
                <td class="border-1 text-end">
                    {{rupiah($purchases->sum('discount') + $purchases->sum('purchase_discount'))}}
                </td>
                <td class="border-1 text-end border-r">
                    {{rupiah($purchases->sum('total_amount'))}}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
@endsection
