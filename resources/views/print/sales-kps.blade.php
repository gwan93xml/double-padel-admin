@extends('print.layout')
@section('content')
{{-- <div class="container"> --}}
<table class="mb-1">
    <tr>
        <td class="border-0 valign-top" style="width:50%">
            @if($company->logo == null)
            <h1 style="font-size:40pt">{{$company->name}}</h1>
            @else
            <center><img src="{{public_path("/storage/".$company->logo)}}" alt="" style="height:108px;" /></center>
            <h1 class="header">{{$company->name}}</h1>
            @endif
        </td>
        <td class=" border-0 valign-top p-0">
            <div class="border p-2" style="padding-bottom: 30px;">
                <strong style="font-size : 12pt">Kepada Yth</strong>
                <div style="font-size: 10pt">{{$sale->customer->name}}</div>
            </div>
            <table style="font-size:9pt !important" class="mb-0 pt-1">
                <tbody>
                    <tr>
                        <td class="border-0 p-0 m-0" style="width:25%">
                            Faktur No.
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{$sale->no}}/{{$sale->cr_db}}/{{$sale->company_sale_code}}/{{$sale->month}}/{{$sale->year}}
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
    </tr>
</table>
<table class="items-table" style="font-size:9pt">
    <thead class="">
        <tr class="" style="font-size:9pt">
            <th class="border-0 border-y p-0 m-0 text-center pb-1 pt-1" style="width:5px">NO</th>
            <th class="border-0 border-y p-0 m-0 text-center" style="width:50px">UNIT</th>
            <th class="border-0 border-y p-0 m-0 text-left" style="width:260px">URAIAN</th>
            <th class="border-0 border-y p-0 m-0 text-end" style="width:80px">SATUAN(Rp)</th>
            <th class="border-0 border-y p-0 m-0 text-end" style="width:60px">HARGA</th>
            <th class="border-0 border-y p-0 m-0 text-end" style="width:80px">DISCOUNT</th>
            <th class="border-0 border-y p-0 m-0 text-end" style="width:150px" colspan="2">JUMLAH</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->items as $key => $item)
        <tr style="font-size:8pt">
            <td class="border-0  p-0 m-0  pb-1">{{$key+1}}</td>
            <td class="border-0  p-0 m-0  pb-1 text-end pr-2">{{$item->quantity}} {{ $item->unit}}</td>
            <td class="border-0  p-0 m-0  pb-1">{{$item->item?->name ?? $item->item_name}}</td>
            <td class="border-0  p-0 m-0  pb-1   text-end">{{number($item->price)}}</td>
            <td class="border-0  p-0 m-0  pb-1   text-end">{{number($item->subtotal)}}</td>
            <td class="border-0  p-0 m-0  pb-1   text-end">{{number($item->discount)}}</td>
            <td class="border-0  p-0 m-0  pb-1   text-end" colspan="2">{{number($item->total)}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="border-t pt-2">
        <tr>
            <td colspan="5" class="border-0 p-0 m-0" valign="top">
                <table class="border-0 p-0 m-0">
                    <tr>
                        <td class="border-0 p-0 m-0" style="width:80px">
                            Jatuh Tempo
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{Carbon\Carbon::parse($sale->due_date)->format('d F Y')}}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0" style="width:80px" valign="top">
                            Terbilang
                        </td>
                        <td class="border-0 p-0 m-0" style="padding-right:5px" valign="top">
                            : {{ucwords(terbilang($sale->total_amount)." rupiah")}}
                        </td>
                    </tr>
                </table>
            </td>
            <td class="border-0 p-0 m-0" colspan="3">
                <table class="border-0 p-0 m-0">
                    <tr>
                        <td class="border-0 p-0 m-0" style="width:90px">
                            SUBTOTAL
                        </td>
                        <td class="border-0 p-0 m-0" style="width:35px">
                            : IDR
                        </td>
                        <td class="border-0 text-end p-0 m-0">
                            {{number($sale->subtotal)}}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            PPN
                        </td>
                        <td class="border-0 p-0 m-0">
                            : IDR
                        </td>
                        <td class="border-0 text-end p-0 m-0">
                            {{number($sale->tax)}}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0 border-t font-bold valign-top">
                            GRAND TOTAL
                        </td>
                        <td class="border-0 p-0 m-0 border-t font-bold valign-top">
                            : IDR
                        </td>
                        <td class="border-0 text-end p-0 m-0 border-t font-bold valign-top">
                            {{rupiah($sale->total_amount)}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="5" class="border-0 p-0 m-0">
                <table>
                    <tr>
                        <td class="border-0">
                            <center>Hormat Kami</center> <br>
                            <br>
                            <br>
                            <br>
                            <center>_____________</center>
                        </td>
                        <td class="border-0">
                            <div class="border p-2 font-semibold" style="margin-left:75px">
                                Bank: {{$company->bankAccount?->bank?->name}} <br>
                                AC : {{$company->bankAccount?->account_number}} <br>
                                A/N : {{$company->bankAccount?->account_name}}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="border-0 p-0 m-0  font-bold valign-top">
                
            </td>
            <td class="border-0 p-0 m-0  font-bold valign-top">
                
            </td>
            <td class="border-0 text-end p-0 m-0  font-bold valign-top">
            </td>
        </tr>
    </tfoot>
</table>
@endsection
