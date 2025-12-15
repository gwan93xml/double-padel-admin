<table>
    <tr>
        <td style="width: 150px;">
            Kode Pelanggan
        </td>
        <td>
            : {{$summary->customer->code}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Nama Pelanggan
        </td>
        <td>
            : {{$summary->customer->name}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Total Piutang
        </td>
        <td>
            : {{rupiah($summary->amount)}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Diterima
        </td>
        <td>
            : {{rupiah($summary->paid_amount)}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Sisa Piutang
        </td>
        <td>
            : {{rupiah($summary->remaining_amount)}}
        </td>
    </tr>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>Jatuh Tempo</th>
            <th>Total Piutang</th>
            <th>Diterima</th>
            <th>Sisa Piutang</th>
        </tr>
    </thead>
    <tbody>
        @foreach($receivables as $item)
        <tr>
            <td>{{ Carbon\Carbon::parse($item->due_date)->format('d/m/Y') }}</td>
            <td class="text-end">{{rupiah($item->amount)}}</td>
            <td class="text-end">{{rupiah($item->paid_amount)}}</td>
            <td class="text-end">{{rupiah($item->remaining_amount)}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
