@extends('print.layout')
@section('content')
<div class="header">
    <h1>Daftar Pembelian Periode {{$month_from_query}} - {{$month_to_query}}</h1>
</div>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th>DIVISI</th>
            <th>NO. FAKTUR</th>
            <th>TANGGAL</th>
            <th>VENDOR</th>
            <th class="text-end">HARGA</th>
            <th class="text-end">DISC</th>
            <th class="text-end">TAX</th>
            <th class="text-end">BY.KIRIM</th>
            <th class="text-end">BY.MATERAI</th>
            <th class="text-end">TOTAL</th>
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
            <td class="text-end">{{rupiah($item->subtotal)}}</td>
            <td class="text-end">{{rupiah($item->discount + $item->purchase_discount)}}</td>
            <td class="text-end">{{rupiah($item->tax)}}</td>
            <td class="text-end">{{rupiah($item->shipping_cost)}}</td>
            <td class="text-end">{{rupiah($item->stamp_duty)}}</td>
            <td class="text-end">{{rupiah($item->total_amount)}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="font-bold">
        <tr>
            <td colspan="4" class="border-1 border-r-0 text-end">
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
            <td class="border-1 text-end">
                {{rupiah($purchases->sum('tax'))}}
            </td>
            <td class="border-1 text-end">
                {{rupiah($purchases->sum('shipping_cost'))}}
            </td>
            <td class="border-1 text-end">
                {{rupiah($purchases->sum('stamp_duty'))}}
            </td>
            <td class="border-1 text-end">
                {{rupiah($purchases->sum('total_amount'))}}
            </td>
        </tr>
    </tfoot>
</table>
@endsection
