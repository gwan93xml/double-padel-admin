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
    $received_from = $item->journal->cashIn->received_from ?? '';
    $description = $item->journal->cashIn->description ?? '';
    return $description . ' | Diterima Dari ' . $received_from;
} else if ($item->journal->transaction_type === "CashOut") {
    $paid_to = $item->journal->cashOut->paid_to ?? '';
    $description = $item->journal->cashOut->description ?? '';
    return $description . ' | Dibayar Kepada ' . $paid_to;
} else if ($item->journal->transaction_type === "CashTransfer") {
return $item->journal?->cashTransfer?->note ?? '';
}
return $item->journal->notes ?? "-";
}


function getAdditionalInfo($item) {
if ($item->journal->transaction_type == "Purchase" || $item->journal->transaction_type == "Purchase_Inventory") {
return "|  {$item->journal->purchase->vendor->name}";
} else if ($item->journal->transaction_type == "Sale" || $item->journal->transaction_type == "Sale_Inventory") {
return "|  {$item->journal->sale->customer->name}";
}
return '';
}
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Buku Besar</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1 {
            font-size: 18px;
            margin: 0 0 4px;
        }

        .subtitle {
            font-size: 12px;
            margin-bottom: 14px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 6px 8px;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .muted {
            color: #777;
        }

    </style>
</head>
<body>
    <h1>Laporan Buku Besar</h1>
    <div class="subtitle">
        Periode:
        {{ $fmtDate($dateFrom ?? null) ?: '-' }}
        s/d
        {{ $fmtDate($dateTo ?? null) ?: '-' }}
    </div>

    @foreach(($ledgers ?? []) as $gIndex => $group)
    <p><strong>AKUN</strong>: {{ data_get($group, 'account.name') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width:30px;">No</th>
                <th style="width:80px;">Tanggal</th>
                <th>Divisi</th>
                <th>No. Transaksi</th>
                <th>Keterangan</th>
                <th>Catatan</th>
                <th style="width:90px;" class="text-right">Debet</th>
                <th style="width:90px;" class="text-right">Kredit</th>
                <th style="width:100px;" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            {{-- Row Saldo Awal --}}
            <tr>
                <td>1</td>
                <td>{{ $fmtDate($searchQuery['dateFrom'] ?? null) }}</td>
                <td>Saldo Awal</td>
                <td class="muted text-center">-</td>
                <td class="muted text-center">-</td>
                <td class="muted text-center">-</td>
                <td class="muted text-right">-</td>
                <td class="muted text-right">-</td>
                @php $sb = (float) data_get($group, 'start_balance', 0); @endphp
                <td class="text-right">{{ $formatRp($sb) }}</td>
            </tr>

            {{-- Ledger Entries --}}
            @foreach((data_get($group, 'ledgers', []) ?? []) as $idx => $item)
            @php
            $no = $idx + 2;
            $debit = (float) ($item['debit'] ?? 0);
            $credit = (float) ($item['credit'] ?? 0);
            $balance = (float) ($item['balance'] ?? 0);
            $refDesc = data_get($item, 'reference_description', '-');
            @endphp
            <tr>
                <td>{{ $no }}</td>
                <td>{{ $fmtDate($item->journal->date) }}</td>
                <td>{{ $item->journal->division->name }}</td>
                <td>
                    {{ $item->journal->notes }} {{getAdditionalInfo($item) }}
                </td>
                <td>{{ getReferenceDescription($item) }}</td>
                <td>{{ $item['notes'] ?? '-' }}</td>
                <td class="text-right">{{ $formatRp($debit) }}</td>
                <td class="text-right">{{ $formatRp($credit) }}</td>
                <td class="text-right">{{ $formatRp($balance) }}</td>
            </tr>
            @endforeach
        </tbody>

        {{-- Footer total --}}
        @php
        $sumDebit = collect(data_get($group, 'ledgers', []))->sum('debit');
        $sumCredit = collect(data_get($group, 'ledgers', []))->sum('credit');
        $net = $sumDebit - $sumCredit;
        @endphp
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>Grand Total</strong></td>
                <td class="text-right"><strong>{{ $formatRp($sumDebit) }}</strong></td>
                <td class="text-right"><strong>{{ $formatRp($sumCredit) }}</strong></td>
                <td class="text-right"><strong>{{ $formatRp($net) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @endforeach
</body>
</html>
