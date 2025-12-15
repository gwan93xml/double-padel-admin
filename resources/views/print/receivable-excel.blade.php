<table class="header-table">
    <thead>
        <tr>
            <td colspan="10" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN PIUTANG
            </td>
        </tr>
    </thead>
</table>
<table>
    <tr>
        <td><strong>Divisi</strong></td>
        <td><strong>Nomor PO.</strong></td>
        <td><strong>Nama</strong></td>
        <td><strong>Tanggal</strong></td>
        <td><strong>Jatuh Tempo</strong></td>
        <td><strong>Total Piutang</strong></td>
        <td><strong>Diterima</strong></td>
        <td><strong>Sisa Piutang</strong></td>
        <td><strong>Status</strong></td>
        <td><strong>Hari Overdue</strong></td>
    </tr>
    @foreach($receivables as $item)
    @php
    $dueDate = \Carbon\Carbon::parse($item->due_date);
    $isPaid = $item->status === 'paid' || $item->remaining_amount_filtered == 0;
    $isOverdue = !$isPaid && $dueDate->isPast();
    $daysOverdue = $isOverdue ? $dueDate->diffInDays(now()) : 0;

    // Check if customer name is same as previous item
    $previousItem = $loop->index > 0 ? $receivables[$loop->index - 1] : null;
    $customerName = $item->customer?->name;
    $showCustomerName = !$previousItem || $previousItem->customer?->name !== $customerName;
    @endphp
    <tr>
        <td>{{$item->division?->name ?? '-' }}</td>
        <td>{{$item->purchase_order_number ?? '-' }}</td>
        <td>{{ $showCustomerName ? $customerName : '' }}</td>
        <td>{{Carbon\Carbon::parse($item->date)->format('d/m/Y')}}</td>
        <td>{{$item->due_date ? $dueDate->format('d/m/Y') : '-'}}</td>
        <td>{{ $item->amount }}</td>
        <td>{{ $item->paid_amount_filtered }}</td>
        <td>{{ $item->remaining_amount_filtered }}</td>
        <td>
            @if($isPaid)
            LUNAS
            @elseif($isOverdue)
            OVERDUE
            @else
            AKTIF
            @endif
        </td>
        <td>
            {{ $isOverdue ? $daysOverdue . ' hari' : ($isPaid ? 'SELESAI' : '-') }}
        </td>
    </tr>
    @endforeach
    <tr>
        <td colspan="5"><strong>TOTAL</strong></td>
        <td><strong>{{ $receivables->sum('amount') }}</strong></td>
        <td><strong>{{ $receivables->sum('paid_amount_filtered') }}</strong></td>
        <td><strong>{{ $receivables->sum('remaining_amount_filtered') }}</strong></td>
        <td colspan="2"></td>
    </tr>
</table>
