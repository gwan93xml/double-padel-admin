@extends('print.layout')
@section('content')
<div class="container">
    <div class="header">
        <h1>Slip Retur Penjualan</h1>
    </div>
    <div class="company-info">
        <strong>{{$setting->company_name}}</strong><br>
        {{$setting->company_address}}<br>
        {{$setting->company_phone}}
    </div>

    <div class="date-info">
        <div>Date:{{Carbon\Carbon::parse($salesReturn->sale_date)->format('d/m/Y')}}</div>
        <div>Sales Slip No.:{{$salesReturn->id}}</div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="info-header">Vendor</div>
                <div style="padding: 10px;">
                    {{$setting->company_name}}<br>
                    {{$setting->company_address}}<br>
                    {{$setting->company_phone}}
                </div>
            </td>
            <td>
                <div class="info-header">Ship To</div>
                <div style="padding: 10px;">
                    {{$salesReturn->sales->customer->name}}<br>
                    {{$salesReturn->sales->customer->address}}<br>
                    {{$salesReturn->sales->customer->phone}}
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Kode Barang</th>
                <th>Nama Barang</th>
                <th>Kuantitas</th>
                <th>Harga</th>
                <th>Jumlah sebelum pajak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesReturn->items as $item)
            <tr>
                <td>{{$item->item?->code ?? "-"}}</td>
                <td>{{$item->item?->name ?? $item->item_name}}</td>
                <td class="text-end">{{number($item->quantity)}}</td>
                <td class="text-end">{{rupiah($item->price)}}</td>
                <td class="text-end">{{rupiah($item->subtotal)}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>SUBTOTAL</td>
            <td>{{rupiah($salesReturn->subtotal)}}</td>
        </tr>
        <tr>
            <td>TAX</td>
            <td>{{rupiah($salesReturn->tax)}}</td>
        </tr>
        <tr>
            <td>TOTAL</td>
            <td>{{rupiah($salesReturn->total)}}</td>
        </tr>
    </table>
</div>
@endsection
