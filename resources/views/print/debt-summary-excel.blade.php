<table>
    <tr>
        <td><strong>Ringkasan Utang</strong></td>
    </tr>
    <tr>
        <td>Total Utang</td>
        <td>{{ $summary['total_debts'] }}</td>
    </tr>
    <tr>
        <td>Total Jumlah</td>
        <td>{{ rupiah($summary['total_amount']) }}</td>
    </tr>
    <tr>
        <td>Total Dibayar</td>
        <td>{{ rupiah($summary['total_paid_amount']) }}</td>
    </tr>
    <tr>
        <td>Total Sisa</td>
        <td>{{ rupiah($summary['total_remaining_amount']) }}</td>
    </tr>
    <tr>
        <td>Utang Jatuh Tempo</td>
        <td>{{ $summary['overdue_count'] }}</td>
    </tr>
    <tr>
        <td>Jumlah Jatuh Tempo</td>
        <td>{{ rupiah($summary['overdue_amount']) }}</td>
    </tr>
</table>
