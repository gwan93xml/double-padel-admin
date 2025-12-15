@extends('print.layout')
@section('content')
<div class="header">
    <h1>Slip Retur Pembelian</h1>
</div>
<div class="company-info">
    <strong>{{$setting->company_name}}</strong><br>
    {{$setting->company_address}}<br>
    {{$setting->company_phone}}
</div>

<div class="date-info">
    <div>Date:{{Carbon\Carbon::parse($purchaseReturn->date)->format('d/m/Y')}}</div>
    <div>Nomor :{{$purchaseReturn->number}}</div>
</div>

<table class="info-table">
    <tr>
        <td>
            <div class="info-header">Vendor</div>
            <div style="padding: 10px;">
                {{$purchaseReturn->vendor->name}}<br>
                {{$purchaseReturn->vendor->address}}<br>
                {{$purchaseReturn->vendor->phone}}
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
            <th class="text-end">@</th>
            <th class="text-end">Harga Beli</th>
            <th class="text-end">Discount</th>
            <th class="text-end">Total</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchaseReturn->items as $item)
        <tr>
            <td>
                @if($item->is_stock)
                {{$item->item?->code}}
                @else
                NON STOK
                @endif
            </td>
            <td>{{$item->item?->name ?? $item->item_name}}</td>
            <td class="text-end">{{number($item->quantity)}}</td>
            <td class="text-end">{{rupiah($item->price)}}</td>
            <td class="text-end">{{rupiah($item->subtotal)}}</td>
            <td class="text-end">{{rupiah($item->discount)}}</td>
            <td class="text-end">{{rupiah($item->total)}}</td>
            <td>{{$item->remarks}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="totals-table">
    <tr>
        <td>SUBTOTAL</td>
        <td>{{rupiah($purchaseReturn->subtotal)}}</td>
    </tr>
    <tr>
        <td>DISCOUNT</td>
        <td>{{rupiah($purchaseReturn->discount)}}</td>
    </tr>
    <tr>
        <td>TOTAL DISCOUNT</td>
        <td>{{rupiah($purchaseReturn->total_discount)}}</td>
    </tr>
    <tr>
        <td>TAX</td>
        <td>{{rupiah($purchaseReturn->tax)}}</td>
    </tr>
    <tr>
        <td>TOTAL</td>
        <td>{{rupiah($purchaseReturn->total)}}</td>
    </tr>
</table>
@endsection
