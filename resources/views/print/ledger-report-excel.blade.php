@php
$fmt = new \NumberFormatter('id_ID', \NumberFormatter::DECIMAL);
$formatRp = fn($v) => rupiah($v, 2);
$fmtDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '';

function getReferenceDescription ($item) {
    if ($item->journal->transaction_type === "Purchase") {
        return $item->journal?->purchase?->note ?? '';
    } else if ($item->journal->transaction_type === "Sale") {
        return $item->journal?->sale?->note ?? '';
    } else if ($item->journal->transaction_type === "CashIn") {
        return $item->journal?->cashIn?->description ?? '';
    } else if ($item->journal->transaction_type === "CashOut") {
        return $item->journal?->cashOut?->description ?? '';
    } else if ($item->journal->transaction_type === "CashTransfer") {
        return $item->journal?->cashTransfer?->note ?? '';
    }
    return $item->journal->notes ?: "-";
}

function getAdditionalInfo($item) {
if ($item->journal->transaction_type === "Purchase" || $item->journal->transaction_type === "Purchase_Inventory") {
return "|  {$item->journal->purchase->vendor->name}";
} else if ($item->journal->transaction_type === "Sale" || $item->journal->transaction_type === "Sale_Inventory") {
return "|  {$item->journal->sale->customer->name}";
}
return '';
}
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <tr>
            <td colspan="8"><strong>Laporan Buku Besar</strong></td>
        </tr>
        <tr>
            <td colspan="8">
                Periode: {{ $fmtDate($dateFrom ?? null) ?: '-' }} s/d {{ $fmtDate($dateTo ?? null) ?: '-' }}
            </td>
        </tr>
    </table>

    @foreach(($ledgers ?? []) as $gIndex => $group)
        <table>
            <tr>
                <td colspan="8"><strong>AKUN:</strong> {{ data_get($group, 'account.name') }}</td>
            </tr>
        </table>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No. Transaksi</th>
                    <th>Divisi</th>
                    <th>Keterangan</th>
                    <th>Catatan</th>
                    <th>Debet</th>
                    <th>Kredit</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                {{-- Saldo Awal --}}
                <tr>
                    <td>1</td>
                    <td>{{ $fmtDate($dateFrom ?? null) }}</td>
                    <td>Saldo Awal</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    <td>-</td>
                    @php $sb = (float) data_get($group, 'start_balance', 0); @endphp
                    <td>{{ $sb }}</td>
                </tr>

                {{-- Ledger Entries --}}
                @foreach((data_get($group, 'ledgers', []) ?? []) as $idx => $item)
                    @php
                        $no = $idx + 2;
                        $debit = (float) ($item['debit'] ?? 0);
                        $credit = (float) ($item['credit'] ?? 0);
                        $balance = (float) ($item['balance'] ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $fmtDate($item->journal->date) }}</td>
                        <td>{{ $item->journal->notes }} {{getAdditionalInfo($item) }}</td>
                        <td>{{ getReferenceDescription($item) }}</td>
                        <td>{{ $item['notes'] ?? '-' }}</td>
                        <td>{{ $debit }}</td>
                        <td>{{ $credit }}</td>
                        <td>{{ $balance }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $sumDebit = collect(data_get($group, 'ledgers', []))->sum('debit');
                    $sumCredit = collect(data_get($group, 'ledgers', []))->sum('credit');
                    $net = $sumDebit - $sumCredit;
                @endphp
                <tr>
                    <td colspan="6"><strong>Grand Total</strong></td>
                    <td><strong>{{ $sumDebit }}</strong></td>
                    <td><strong>{{ $sumCredit }}</strong></td>
                    <td><strong>{{ $net }}</strong></td>
                </tr>
            </tfoot>
        </table>

        {{-- Spacer antar akun --}}
        @if($gIndex < count($ledgers)-1)
            <table><tr><td colspan="8"></td></tr></table>
        @endif
    @endforeach
</body>
</html>
