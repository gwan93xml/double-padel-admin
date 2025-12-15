@extends('print.layout')
@section('content')
<table class="mb-0 p-0" padding="0" margin="0">
    <tr>
        <td class="border-0 valign-top" style="width:50%">
            @if($company->logo == null)
            <h1 style="font-size:40pt">{{$company->name}}</h1>
            @else
            <center><img src="{{public_path("/storage/".$company->logo)}}" alt="" style="height:108px;" /></center>
            <h1 class="header">{{$company->name}}</h1>
            @endif
        </td>
        <td class="border-0 valign-top p-0">
            <div class="border p-2" style="padding-bottom: 30px;">
                <strong style="font-size : 12pt">Kepada Yth</strong>
                <div style="font-size: 10pt">{{$salesOrder->customer->name}}</div>
            </div>
            <table class="mb-0" style="font-size:9pt !important">
                <tbody>
                    <tr>
                        <td class="border-0 p-0 m-0 font-bold pt-1" colspan="2" style="font-size: 12pt">
                            SURAT PENGANTAR
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0" style="width:25%">
                            NO
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{$salesOrder->number}}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            Tanggal
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{Carbon\Carbon::parse($salesOrder->date)->format('d F Y')}}
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0" colspan="2">
                            Barang - barang dibawah ini kami kirim sesuai dengan
                        </td>
                    </tr>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            No. Po Anda
                        </td>
                        <td class="border-0 p-0 m-0">
                            : {{$salesOrder->purchase_order_number}}
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
            <th class="border-0 border-y text-center p-0 m-0 pt-1 pb-1 mb-1" style="width:15px">NO</th>
            <th class="border-0 border-y text-center p-0 m-0 pt-1 pr-2 pb-1">UNIT</th>
            <th class="border-0 border-y text-center p-0 m-0 pt-1 pb-1">URAIAN</th>
            <th class="border-0 border-y text-center p-0 m-0 pt-1 pb-1">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
        @foreach($salesOrder->items as $key => $item)
        <tr style="font-size:8pt !important">
            <td class="border-0 p-0 m-0 pb-1 text-center">{{$key+1}}</td>
            <td class="border-0 p-0 m-0 pb-1 text-end pr-2">{{$item->quantity}} {{$item->unit}}</td>
            <td class="border-0 p-0 m-0 pb-1 pl-2 text-left" style="width:70%">{{$item->item?->name ?? $item->item_name}}</td>
            <td class="border-0 p-0 m-0 pb-1 text-center">{{$item->notes}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot class="border-t">
        <tr>
            <td colspan="4" class="border-0 p-0 m-0">
                Catatan : {{$salesOrder->notes}}
            </td>
        </tr>
        <tr>
            <td colspan="" class="border-0 p-0 m-0">

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
                <div class="font-bold">
                    Barang - barang tersebut diterima dengan baik/cukup
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="4" class="border-0 p-0 m-0">
                <table>
                    <tr>
                        <td class="border-0" style="width:20%">
                            <center>Tanda Tangan Penerima,</center> <br>
                            <div style="height: 70px;"></div>
                            <br>
                            ___________________________
                        </td>
                        <td style="width:30%" class="border-0">
                        </td>
                        <td class="border-0" style="width:20%">
                            <center><span>Hormat Kami,</span></center> <br>
                            <div style="height: 70px; text-align: center; vertical-align: middle;">
                                @if($company->logo == null)
                                <div style="font-size:18px; font-weight:bold; padding-top: 25px; line-height: 1.2;">
                                    {{$company->name}}
                                </div>
                                @else
                                <img src="{{public_path("/storage/".$company->logo)}}" alt="logo" style="height: 70px;" />
                                @endif
                            </div>
                            <br>
                            <div style="text-align:right">_________________________</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tfoot>
</table>

{{-- <table class="totals-table">
    <tr>
        <td>SUBTOTAL</td>
        <td>{{rupiah($salesOrder->subtotal)}}</td>
</tr>
<tr>
    <td>TAX</td>
    <td>{{rupiah($salesOrder->tax)}}</td>
</tr>
<tr>
    <td>TOTAL</td>
    <td>{{rupiah($salesOrder->total_amount)}}</td>
</tr>
</table> --}}
{{-- </div> --}}
@endsection
