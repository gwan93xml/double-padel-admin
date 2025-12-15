@extends('print.layout')
@section('content')
<div class="header">
    <h1>Daftar Penjualan Periode {{$month_from_query}} - {{$month_to_query}}</h1>
</div>
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
            <td class="text-end">{{rupiah($item->subtotal)}}</td>
            <td class="text-end">{{rupiah($item->discount + $item->sales_discount)}}</td>
            <td class="text-end">{{rupiah($item->tax)}}</td>
            <td class="text-end">{{rupiah($item->total_amount)}}</td>
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="5" class="border-0 p-0 m-0">
                <strong>TOTAL</strong>
            </td>
            <td class="border-1 text-end">
                {{rupiah($sales->sum('subtotal'))}}
            </td>
            <td class="border-1 text-end">
                {{rupiah($sales->sum('discount'))}}
            </td>
            <td class="border-1 text-end">
                {{rupiah($sales->sum('tax'))}}
            </td>
            <td class="border-1 text-end">
                {{rupiah($sales->sum('total_amount'))}}
            </td>
        </tr>
    </tfoot>
</table>
@endsection
