
<table>
    <tr>
        <td><strong>Kode Customer</strong></td>
        <td><strong>Nama Customer</strong></td>
        <td><strong>Jumlah Piutang</strong></td>
        <td><strong>Total Nilai</strong></td>
        <td><strong>Total Terbayar</strong></td>
        <td><strong>Total Sisa</strong></td>
        <td><strong>Overdue Count</strong></td>
        <td><strong>Overdue Amount</strong></td>
    </tr>
    @if(isset($customerData))
        @foreach($customerData as $customer)
        <tr>
            <td>{{ $customer['receivables'][0]['customer']['code'] ?? '-' }}</td>
            <td>{{ $customer['customer_name'] }}</td>
            <td>{{ $customer['total_receivables'] }}</td>
            <td>{{ number_format($customer['total_amount'], 0, ',', '.') }}</td>
            <td>{{ number_format($customer['total_paid_amount'], 0, ',', '.') }}</td>
            <td>{{ number_format($customer['total_remaining_amount'], 0, ',', '.') }}</td>
            <td>{{ $customer['overdue_count'] }}</td>
            <td>{{ number_format($customer['overdue_amount'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
        
        <tr>
            <td colspan="2"><strong>TOTAL</strong></td>
            <td><strong>{{ $customerData->sum('total_receivables') }}</strong></td>
            <td><strong>{{ number_format($customerData->sum('total_amount'), 0, ',', '.') }}</strong></td>
            <td><strong>{{ number_format($customerData->sum('total_paid_amount'), 0, ',', '.') }}</strong></td>
            <td><strong>{{ number_format($customerData->sum('total_remaining_amount'), 0, ',', '.') }}</strong></td>
            <td><strong>{{ $customerData->sum('overdue_count') }}</strong></td>
            <td><strong>{{ number_format($customerData->sum('overdue_amount'), 0, ',', '.') }}</strong></td>
        </tr>
    @endif
</table>
