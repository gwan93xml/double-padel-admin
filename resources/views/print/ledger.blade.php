@extends('print.layout')
@section('content')
@php
function getReferenceDescription($item) {
if ($item->journal->transaction_type === "Purchase") {
return $item->journal->purchase->note ?? '';
} else if ($item->journal->transaction_type === "Sale") {
return $item->journal->sale->note ?? '';
} else if ($item->journal->transaction_type === "CashIn") {
    $received_from = $item->journal->cashIn->received_from ?? '';
    $description = $item->journal->cashIn->description ?? '';
    return $description . ' | Diterima Dari ' . $received_from;
} else if ($item->journal->transaction_type === "CashOut") {
    $paid_to = $item->journal->cashOut->paid_to ?? '';
    $description = $item->journal->cashOut->description ?? '';
    return $description . ' | Dibayar Kepada ' . $paid_to;
} else if ($item->journal->transaction_type === "CashTransfer") {
return $item->journal->cashOut->description ?? '';
} else if ($item->journal->transaction_type === "CashTransfer") {
return $item->journal->cashTransfer->note ?? '';
}
return $item->journal->notes ?? "-";
}
function getAdditionalInfo($item) {
if ($item->journal->transaction_type === "Purchase" || $item->journal->transaction_type === "Purchase_Inventory") {
return "|  {$item->journal->purchase->vendor->name}";
} else if ($item->journal->transaction_type === "Sale" || $item->journal->transaction_type === "Sale_Inventory") {
return "|  {$item->journal->sale->customer->name}";
}
return '';
}
@endphp
<div class="header">
    <h1>Buku Besar</h1>
</div>
<h2>{{ $chartOfAccount?->name ?? 'Silahkan Pilih Akun' }} Periode {{$dateFrom}} - {{$dateTo}}</h2>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th>TANGGAL</th>
            <th>DIVISI</th>
            <th>NO TRANSAKSI</th>
            <th>KETERANGAN</th>
            <th>CATATAN</th>
            <th class="text-end">DEBET</th>
            <th class="text-end">KREDIT</th>
            <th class="text-end">SALDO</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>{{Carbon\Carbon::parse($dateFrom)->format('d/m/Y')}}</td>
            <td>Saldo Awal</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td class="text-end">{{rupiah($startBalance)}}</td>
        </tr>
        @foreach($ledgers as $key => $item)
        <tr>
            <td>{{$key+2}}</td>
            <td>{{Carbon\Carbon::parse($item->journal->date)->format('d/m/Y') }}</td>
            <td>{{$item->journal->division->name }}</td>
            <td>{{$item->journal->notes }}  {{getAdditionalInfo($item)}}</td>
            <td>{{getReferenceDescription($item) }}</td>
            <td>{{$item->notes }}</td>
            <td class="text-end">{{rupiah($item->debit,2)}}</td>
            <td class="text-end">{{rupiah($item->credit,2)}}</td>
            <td class="text-end">{{rupiah($item->balance,2)}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6">TOTAL</td>
            <td class="text-end">{{rupiah($ledgers?->sum('debit') ?? 0, 2)}}</td>
            <td class="text-end">{{rupiah($ledgers?->sum('credit') ?? 0, 2)}}</td>
            <td class="text-end"></td>
        </tr>
    </tfoot>
</table>
@endsection
