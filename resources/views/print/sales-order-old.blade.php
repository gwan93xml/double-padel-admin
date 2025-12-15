@extends('print.layout')
@section('content')
{{-- <div class="container"> --}}
<table>
    <tr>
        <td class="border-0" style="width:50%" rowspan="2">
            @if($company->logo != null)
            <center><img src="{{public_path("/storage/".$company->logo)}}" alt="" style="height:100px" /></center>
            <h1 style="font-size:18pt">{{$company->name}}</h1>
            @else
            <h1 style="font-size:40pt">{{$company->name}}</h1>
            @endif
        </td>
        <td class=" valign-top">
            <strong>Kepada Yth</strong>
            <div>{{$salesOrder->customer->name}}</div>
        </td>
    </tr>
    <tr>
        <td class="border-0 p-0 m-0">
            <table>
                <tbody>
                    <tr>
                        <td class="border-0 p-0 m-0">
                            Faktur No.
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
                            : {{Carbon\Carbon::parse($salesOrder->sale_date)->format('d F Y')}}
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
                            No. PO Anda
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
<table class="items-table">
    <thead class="">
        <tr class="">
            <th class="border-0 border-y text-center" style="width:15px">NO</th>
            <th class="border-0 border-y text-center">UNIT</th>
            <th class="border-0 border-y text-left">URAIAN</th>
            <th class="border-0 border-y text-end">SATUAN(Rp)</th>
            <th class="border-0 border-y text-end">HARGA</th>
            <th class="border-0 border-y text-end" colspan="2">JUMLAH</th>
        </tr>
    </thead>
    <tbody>
        @foreach($salesOrder->items as $key => $item)
        <tr>
            <td class="border-0 border-y">{{$key+1}}</td>
            <td class="border-0 border-y">{{$item->quantity}} {{$item->item?->unit ??  $item->unit}}</td>
            <td class="border-0 border-y">{{$item->item?->name ?? $item->item_name}}</td>
            <td class="border-0 border-y text-end">{{rupiah($item->price)}}</td>
            <td class="border-0 border-y text-end">{{rupiah($item->subtotal)}}</td>
            <td class="border-0 border-y text-end" colspan="2">{{rupiah($item->total)}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="border-0 p-0 m-0">
                JATUH TEMPO : {{Carbon\Carbon::parse($salesOrder->due_date)->format('d F Y')}}
            </td>
            <td class="border-0 p-0 m-0">
                TOTAL
            </td>
            <td class="border-0 p-0 m-0">
                : IDR
            </td>
            <td class="border-0 text-end p-0 m-0">
                {{rupiah($salesOrder->subtotal)}}
            </td>
        </tr>
        <tr>
            <td colspan="4" class="border-0 p-0 m-0">
                Terbilang : {{ucwords(terbilang($salesOrder->total_amount))}}
            </td>
            <td class="border-0 p-0 m-0">
                PPN
            </td>
            <td class="border-0 p-0 m-0">
                : IDR
            </td>
            <td class="border-0 text-end p-0 m-0">
                {{rupiah($salesOrder->tax)}}
            </td>
        </tr>
        <tr>
            <td colspan="4" class="border-0 p-0 m-0">
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
                            <div class="border p-2 font-semibold">
                                Bank: {{$setting->bankAccount->bank->name}} <br>
                                AC : {{$setting->bankAccount->account_number}} <br>
                                A/N : {{$setting->bankAccount->account_name}}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="border-0 p-0 m-0 border-t font-bold valign-top">
                GRAND TOTAL
            </td>
            <td class="border-0 p-0 m-0 border-t font-bold valign-top">
                : IDR
            </td>
            <td class="border-0 text-end p-0 m-0 border-t font-bold valign-top">
                {{rupiah($salesOrder->total_amount)}}
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
