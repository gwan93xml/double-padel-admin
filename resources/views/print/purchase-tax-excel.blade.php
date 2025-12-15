<table class="header-table">
    <thead>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN PAJAK PEMBELIAN
            </td>
        </tr>
    </thead>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th>TANGGAL</th>
            <th>DIVISI</th>
            <th>TRANSAKSI</th>
            <th>NOMOR INVOICE</th>
            <th>CATATAN</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach($taxTransactions as $key => $item)
        <tr>
            <td class="text-center">{{ $key + 1 }}</td>
            <td class="text-center">{{ Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y') }}</td>
            <td>{{ $item->purchase->divisi->name }}</td>
            <td>{{ $item->purchase->no ?? '-' }}</td>
            <td class="text-center">
                {{ $item->purchase->invoice_number ?? '-'   }}
            </td>
            <td>{{ $item->purchase->note ?? '-' }}</td>
            <td class="text-end">{{ $item->amount }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-end">TOTAL</td>
            <td class="text-end">{{ $taxTransactions->sum('amount') }}</td>
        </tr>
    </tfoot>
</table>
