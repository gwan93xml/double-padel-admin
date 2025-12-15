<table>
    <tr>
        <th>Ranking</th>
        <th>Nama Vendor</th>
        <th>Total Utang</th>
        <th>Total Sisa</th>
        <th>Jatuh Tempo</th>
        <th>Jumlah Jatuh Tempo</th>
    </tr>
    @foreach($topVendors as $index => $vendor)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $vendor['vendor_name'] }}</td>
        <td>{{ $vendor['total_debts'] }}</td>
        <td>{{ rupiah($vendor['total_remaining_amount']) }}</td>
        <td>{{ $vendor['overdue_count'] }}</td>
        <td>{{ rupiah($vendor['overdue_amount']) }}</td>
    </tr>
    @endforeach
</table>
