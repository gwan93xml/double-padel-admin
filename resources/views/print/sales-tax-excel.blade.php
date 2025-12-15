<table class="header-table">
    <thead>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN PAJAK PENJUALAN
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
            <th>NOMOR PO.</th>
            <th>CATATAN</th>
            <th>JUMLAH</th>
        </tr>
    </thead>
    <tbody>
        @foreach($taxTransactions as $key => $item)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ Carbon\Carbon::parse($item->transaction_date)->format('d/m/Y') }}</td>
            <td>{{ $item->sale->division->name }}</td>
            <td>{{ $item->sale->no ?? '-' }}</td>
            <td>{{ $item->sale->purchase_order_number ?? '-' }}</td>
            <td>{{ $item->sale->note ?? '-' }}</td>
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
