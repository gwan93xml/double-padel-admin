@extends('print.layout')
@section('content')
<table>
    <tr>
        <td class="border-0" style="width:50%" colspan="2">
            @if($company->logo != null)
            <center><img src="{{public_path("/storage/".$company->logo)}}" alt="logo" style="height:100px" /></center>
            <h1 style="font-size:18pt" class="text-center">{{$company->name}}</h1>
            @else
            <h1 style="font-size:40pt" class="text-center">{{$company->name}}</h1>
            @endif
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            <table>
                <tbody>
                    <tr>
                        <td class="border-0 p-0 m-0 font-bold" colspan="2">
                            BARANG MASUK
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            NO
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{$itemIn->number}}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            Tanggal
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{Carbon\Carbon::parse($itemIn->date)->format('d F Y')}}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            Gudang
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{$itemIn->warehouse?->name}}
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
</table>
<table class="items-table">
    <thead class="">
        <tr class="">
            <th class="border-0 border-y text-center" style="width:15px">NO</th>
            <th class="border-0 border-y text-center">ITEM</th>
            <th class="border-0 border-y text-end">JUMLAH</th>
            <th class="border-0 border-y text-end">HARGA POKOK</th>
            <th class="border-0 border-y text-center">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
        @foreach($itemIn->items as $key => $item)
        <tr>
            <td class="border-0 border-y">{{$key+1}}</td>
            <td class="border-0 border-y text-end">{{$item->item->code}} {{$item->item->name}}</td>
            <td class="border-0 border-y text-end">{{$item->quantity}} {{$item->item?->unit ?? $item->unit}}</td>
            <td class="border-0 border-y text-end">{{rupiah($item->price)}}</td>
            <td class="border-0 border-y text-center">{{$item->remarks}}</td>
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="2" class="border-0 p-0 m-0">
                <strong>GRAND TOTAL</strong>
            </td>
            <td class="border-0 p-0 m-0 text-end">
            </td>
            <td class="border-0  text-end">
                <strong>{{rupiah($itemIn->price)}}</strong>
            </td>
            <td class="border-0 p-0 m-0">

            </td>
        </tr>
        <tr>
            <td colspan="4" class="border-0 p-0 m-0">
                Catatan : {{$itemIn->remarks}}
            </td>
            <td class="border-0 p-0 m-0">

            </td>
            <td class="border-0 p-0 m-0">

            </td>
            <td class="border-0 text-end p-0 m-0">

            </td>
        </tr>
        <tr>
            <td colspan="4" class="border-0 p-0 m-0">
                Printed By : {{auth()->user()->name}}
            </td>
            <td class="border-0 p-0 m-0">

            </td>
            <td class="border-0 p-0 m-0">

            </td>
            <td class="border-0 text-end p-0 m-0">

            </td>
        </tr>
    </tfoot>
</table>

{{-- <table class="totals-table">
    <tr>
        <td>SUBTOTAL</td>
        <td>{{rupiah($itemIn->subtotal)}}</td>
</tr>
<tr>
    <td>TAX</td>
    <td>{{rupiah($itemIn->tax)}}</td>
</tr>
<tr>
    <td>TOTAL</td>
    <td>{{rupiah($itemIn->total_amount)}}</td>
</tr>
</table> --}}
{{-- </div> --}}
@endsection
