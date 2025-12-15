<table>
    <tr>
        <td colspan="2"><strong>RINGKASAN PIUTANG</strong></td>
    </tr>
    @if(isset($summary))
    <tr>
        <td>Total Piutang</td>
        <td>{{ $summary['total_receivables'] }} transaksi</td>
    </tr>
    <tr>
        <td>Total Nilai</td>
        <td>{{ number_format($summary['total_amount'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Total Terbayar</td>
        <td>{{ number_format($summary['total_paid_amount'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Total Sisa</td>
        <td>{{ number_format($summary['total_remaining_amount'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Lewat Jatuh Tempo</td>
        <td>{{ $summary['overdue_count'] }} transaksi ({{ number_format($summary['overdue_amount'], 0, ',', '.') }})</td>
    </tr>
    @endif
    <tr><td colspan="2"></td></tr>
</table>

<table>
    <tr>
        <td colspan="2"><strong>ANALISIS UMUR PIUTANG</strong></td>
    </tr>
    <tr>
        <td>Kategori</td>
        <td>Jumlah</td>
    </tr>
    @if(isset($aging))
    <tr>
        <td>Belum Jatuh Tempo</td>
        <td>{{ number_format($aging['current'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>1-30 Hari</td>
        <td>{{ number_format($aging['1_30_days'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>31-60 Hari</td>
        <td>{{ number_format($aging['31_60_days'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>61-90 Hari</td>
        <td>{{ number_format($aging['61_90_days'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Lebih dari 90 Hari</td>
        <td>{{ number_format($aging['over_90_days'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td><strong>TOTAL</strong></td>
        <td><strong>{{ number_format($aging['current'] + $aging['1_30_days'] + $aging['31_60_days'] + $aging['61_90_days'] + $aging['over_90_days'], 0, ',', '.') }}</strong></td>
    </tr>
    @endif
</table>
