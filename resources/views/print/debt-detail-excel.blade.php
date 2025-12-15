<table>
    <tr>
        <td style="width: 150px;">
            Kode Vendor
        </td>
        <td>
            : {{$summary->vendor->code}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Nama Vendor
        </td>
        <td>
            : {{$summary->vendor->name}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Total Hutang
        </td>
        <td>
            : {{rupiah($summary->amount)}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Dibayar
        </td>
        <td>
            : {{rupiah($summary->paid_amount)}}
        </td>
    </tr>
    <tr>
        <td style="width: 150px;">
            Sisa Hutang
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
            <th>Total Hutang</th>
            <th>Dibayar</th>
            <th>Sisa Hutang</th>
        </tr>
    </thead>
    <tbody>
        @foreach($debts as $item)
        <tr>
            <td>{{ Carbon\Carbon::parse($item->due_date)->format('d/m/Y') }}</td>
            <td class="text-end">{{rupiah($item->amount)}}</td>
            <td class="text-end">{{rupiah($item->paid_amount)}}</td>
            <td class="text-end">{{rupiah($item->remaining_amount)}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
