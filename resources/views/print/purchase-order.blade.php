@extends('print.layout')
@section('content')
<table>
    <tr>
        <td class="border-0" style="width:50%">
            <center><img src="{{public_path("/storage/".$company->logo)}}" alt="logo" style="height:100px" /></center>
            <h1 style="font-size:18pt" class="text-center m-0">{{$company->name}}</h1>
            <p class="m-0 text-center">
                {{$company->tagline}}
            </p>
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            <strong>{{$purchaseOrder->vendor->name}}</strong>
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0 text-end">
            <strong>{{$company->city}}, {{Carbon\Carbon::parse($purchaseOrder->date)->format('d F Y')}}</strong>
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            <strong>UP : {{$purchaseOrder->for_recipients}}</strong>
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            Hal : PURCHASE ORDER
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            No : {{$purchaseOrder->number}}
        </td>
    </tr>

    {{-- <tr>
        <td class="border-0 p-0 m-0">
            <table>
                <tbody>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            Faktur No.
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{$sale->number}}
    </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            Tanggal
        </td>
        <td class="border-0 p-0 m-0">
            : {{Carbon\Carbon::parse($sale->sale_date)->format('d F Y')}}
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            No. SP Kami
        </td>
        <td class="border-0 p-0 m-0">
            :
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            No. Po Anda
        </td>
        <td class="border-0 p-0 m-0">
            : {{$sale->purchase_order_number}}
        </td>
    </tr>
    </tbody>
</table>
</td>
</tr> --}}
</table>

<table>
    <thead>
        <tr>
            <th class="border-l-0 border-r-0" style="width:15px">NO</th>
            <th class="border-l-0 border-r-0 text-center">KETERANGAN</th>
            <th class="border-l-0 border-r-0 text-end">QTY</th>
            <th class="border-l-0 border-r-0 text-end">HARGA (Rp)</th>
            <th class="border-l-0 border-r-0 text-center" style="width:20%">JUMLAH</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchaseOrder->items as $key => $item)
        <tr>
            <td class="border-0 p-0 m-0 text-end">
                {{$key+1}}.
            </td>
            <td class="border-0 p-0 m-0 text-center">{{$item->item?->name ?? $item->item_name}}</td>
            <td class="border-0 p-0 m-0 text-end">{{number($item->quantity)}} {{$item->unit}}</td>
            <td class="border-0 p-0 m-0 text-end">{{rupiah($item->price)}}</td>
            <td class="border-0 p-0 m-0 text-end">{{rupiah($item->subtotal)}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table class="font-bold m-0">
    <tr>
        <td class="text-end border-0 p-0 m-0">SUBTOTAL : IDR</td>
        <td class="text-end border-0 p-0 m-0" style="width:20%">{{rupiah($purchaseOrder->subtotal)}}</td>
    </tr>
    <tr>
        <td class="text-end border-0 p-0 m-0">DISCOUNT : IDR</td>
        <td class="text-end border-0 p-0 m-0">{{rupiah($purchaseOrder->discount)}}</td>
    </tr>
    <tr>
        <td class="text-end border-0 p-0 m-0">PPN : IDR</td>
        <td class="text-end border-0 p-0 m-0">{{rupiah($purchaseOrder->tax)}}</td>
    </tr>
    <tr>
        <td class="text-end border-0 p-0 m-0">TOTAL : IDR</td>
        <td class="text-end border-0 p-0 m-0">{{rupiah($purchaseOrder->total)}}</td>
    </tr>
</table>
<table class="font-bold">
    <tr>
        <td class="text-end border-0 p-0 m-0"></td>
        <td class="text-end border-0 p-0 m-0" style="width:40%">
            <hr class="m-0 p-0">
            <hr class="">
        </td>
    </tr>
    <tr>
        <td class="font-bold border-0" colspan="2">
            Hormat Kami, <br>
            {{$company->name}} <br>
            <img src="{{public_path("/storage/".$company->logo)}}" alt="signature" style="height:50px" />
            <br>
            <strong>Manager</strong>
        </td>
    </tr>
</table>
@endsection
