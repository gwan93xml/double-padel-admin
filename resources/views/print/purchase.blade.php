@extends('print.layout')
@section('content')
<div class="container">
    <div class="header">
        <h1>Slip Pembelian</h1>
    </div>

    <div class="date-info">
        <div>Date:{{Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y')}}</div>
        <div>Purchase Slip No.:{{$purchase->id}}</div>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <div class="info-header">Vendor</div>
                <div style="padding: 10px;">
                    {{$purchase->vendor->code}}<br>
                    {{$purchase->vendor->name}}<br>
                    {{$purchase->vendor->phone}}<br>
                    {{$purchase->vendor->address}}
                </div>
            </td>
            <td>
                <div class="info-header">Ship To</div>
                <div style="padding: 10px;">
                    {{$setting->company_name}}<br>
                    {{$setting->company_address}}<br>
                    {{$setting->company_phone}}
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
            @foreach($purchase->items as $item)
            <tr>
                <td>{{$item->item?->code ?? "-"}}</td>
                <td>{{$item->item?->name ?? $item->item_name}}</td>
                <td class="text-end">{{number($item->quantity)}} {{$item->unit}}</td>
                <td class="text-end">{{rupiah($item->price)}}</td>
                <td class="text-end">{{rupiah($item->subtotal)}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>SUBTOTAL</td>
            <td>{{rupiah($purchase->subtotal)}}</td>
        </tr>
        <tr>
            <td>DISCOUNT</td>
            <td>{{rupiah($purchase->discount)}}</td>
        </tr>
        <tr>
            <td>TAX</td>
            <td>{{rupiah($purchase->tax)}}</td>
        </tr>
        <tr>
            <td>ADJUSTMENT</td>
            <td>{{rupiah($purchase->adjustment)}}</td>
        </tr>
        <tr>
            <td>TOTAL</td>
            <td>{{rupiah($purchase->total_amount)}}</td>
        </tr>
    </table>
</div>
@endsection
