@extends('print.layout')
@section('content')
<div class="container">
    <div class="header">
        <h1>Laporan Gudang Periode {{$month_from}} - {{$month_to}}</h1>
    </div>
    <table class="items-table">
        <thead>
            <tr>
                <th>TANGGAL</th>
                <th>KETERANGAN</th>
                <th>DEBET</th>
                <th>KREDIT</th>
                <th>SALDO</th>
                <th>GUDANG</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemTransactions as $item)
            <tr>
                <td>{{Carbon\Carbon::parse($item->date)->format('d/m/Y')}}</td>
                <td>{{$item->description}}</td>
                @if($item->transaction_type == 'IN')
                <td class="text-end">{{rupiah($item->total)}}</td>
                <td class="text-end">-</td>
                @else
                <td class="text-end">-</td>
                <td class="text-end">{{rupiah($item->total)}}</td>
                @endif
                <td class="text-end">{{number($item->quantity)}} {{$item->item->units[0]->name}}</td>
                <td>{{$item->warehouse?->name}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
