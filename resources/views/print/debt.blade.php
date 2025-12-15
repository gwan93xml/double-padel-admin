@extends('print.layout')
@section('content')
<div class="header">
    <h1>Daftar Utang</h1>
    @if(isset($request) && $request->vendor_id)
    <p>Vendor: {{ $debts->first()?->vendor?->name ?? 'All Vendors' }}</p>
    @endif
</div>

<div class="detail-section">
    <table class="items-table">
        <thead>
            <tr>
                <th>Divisi</th>
                <th>Nomor Invoice</th>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Jatuh Tempo</th>
                <th>Total Utang</th>
                <th>Dibayar</th>
                <th>Sisa Utang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debts as $item)
            @php
            $isPaid = $item->status === 'paid' || $item->remaining_amount_filtered == 0;
            $isOverdue = !$isPaid && \Carbon\Carbon::parse($item->due_date)->isPast();

            // Check if vendor name is same as previous item
            $previousItem = $loop->index > 0 ? $debts[$loop->index - 1] : null;
            $vendorName = $item->vendor?->name;
            $showVendorName = !$previousItem || $previousItem->vendor?->name !== $vendorName;
            @endphp
            <tr class="{{ $isOverdue ? 'overdue-row' : '' }}">
                <td>{{$item->division?->name ?? '-' }}</td>
                <td>{{$item->invoice_number ?? '-' }}</td>
                <td>{{ $showVendorName ? $vendorName : '' }}</td>
                <td>{{Carbon\Carbon::parse($item->date)->format('d/m/Y')}}</td>
                <td>{{$item->due_date ? Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : '-'}}</td>
                <td class="text-end">{{rupiah($item->amount)}}</td>
                <td class="text-end">{{rupiah($item->paid_amount_filtered)}}</td>
                <td class="text-end">{{rupiah($item->remaining_amount_filtered)}}</td>
                <td class="text-center">
                    @if($isPaid)
                    <span style="color: blue; font-weight: bold;">LUNAS</span>
                    @elseif($isOverdue)
                    <span style="color: red; font-weight: bold;">OVERDUE</span>
                    @else
                    <span style="color: green;">AKTIF</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>Total</strong></td>
                <td class="text-end"><strong>{{rupiah($debts->sum('amount'))}}</strong></td>
                <td class="text-end"><strong>{{rupiah($debts->sum('paid_amount'))}}</strong></td>
                <td class="text-end"><strong>{{rupiah($debts->sum('remaining_amount_filtered'))}}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<style>
    .overdue-row {
        background-color: #ffebee;
    }

    .summary-table td,
    .aging-table td,
    .aging-table th {
        padding: 5px 10px;
        border: 1px solid #ddd;
    }

    .summary-section,
    .aging-section,
    .detail-section {
        page-break-inside: avoid;
    }

</style>
@endsection
