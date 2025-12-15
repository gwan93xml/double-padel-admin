<table>
    <tr>
        <td><strong>Aging Report Utang</strong></td>
    </tr>
    <tr>
        <td>Belum Jatuh Tempo</td>
        <td>{{ rupiah($aging['current']) }}</td>
    </tr>
    <tr>
        <td>1-30 Hari</td>
        <td>{{ rupiah($aging['1_30_days']) }}</td>
    </tr>
    <tr>
        <td>31-60 Hari</td>
        <td>{{ rupiah($aging['31_60_days']) }}</td>
    </tr>
    <tr>
        <td>61-90 Hari</td>
        <td>{{ rupiah($aging['61_90_days']) }}</td>
    </tr>
    <tr>
        <td>Lebih dari 90 Hari</td>
        <td>{{ rupiah($aging['over_90_days']) }}</td>
    </tr>
</table>
