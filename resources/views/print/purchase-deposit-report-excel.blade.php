<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN DEPOSIT PEMBELIAN
            </th>
        </tr>
    </thead>
</table>

<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>NOMOR</th>
            <th>TANGGAL</th>
            <th>NO. PO</th>
            <th>VENDOR</th>
            <th>DIVISI</th>
            <th>JUMLAH</th>
            <th>CATATAN</th>
        </tr>
    </thead>
    <tbody>
        @foreach($deposits as $index => $deposit)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $deposit->number }}</td>
            <td>{{ Carbon\Carbon::parse($deposit->date)->format('d/m/Y') }}</td>
            <td>{{ $deposit->purchase_order_number ?? '-' }}</td>
            <td>{{ $deposit->vendor->name ?? '-' }}</td>
            <td>{{ $deposit->division->name ?? '-' }}</td>
            <td>{{ $deposit->amount }}</td>
            <td>{{ $deposit->notes ?? '-' }}</td>
        </tr>
        @endforeach

        @if($deposits->count() > 0)
        <tr>
            <td colspan="6"><strong>TOTAL</strong></td>
            <td><strong>{{ $total_amount }}</strong></td>
            <td></td>
        </tr>
        @endif
    </tbody>
</table>
