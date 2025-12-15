<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kas {{ $filters['fromDate'] }}—{{ $filters['toDate'] }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
        }

        .header .period {
            font-size: 12px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 4px 6px;
        }

        th {
            background: #eee;
        }

        tfoot td {
            font-weight: bold;
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Kas</h1>
        <div class="period">
            Periode: {{ \Carbon\Carbon::parse($filters['fromDate'])->format('d/m/Y') }}
            &mdash;
            {{ \Carbon\Carbon::parse($filters['toDate'])->format('d/m/Y') }}
            @if(!empty($filters['division']))
            | Divisi: {{ $filters['division']->name }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:3%">No</th>
                <th style="width:10%">Tanggal</th>
                <th style="width:12%">Nomor</th>
                <th style="width:15%">Divisi</th>
                <th style="width:20%">Akun</th>
                <th style="width:20%">Keterangan</th>
                <th style="width:10%" class="text-right">Debit</th>
                <th style="width:10%" class="text-right">Credit</th>
                <th style="width:10%">Jenis</th>
            </tr>
        </thead>
        <tbody>
            @php
            $totalDebit = 0;
            $totalCredit = 0;
            @endphp

            @foreach($items as $idx => $row)
            @php
            $totalDebit += $row->debit;
            $totalCredit += $row->credit;
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->trx_date)->format('d/m/Y') }}</td>
                <td>{{ $row->trx_number }}</td>
                <td>{{ $row->division_name ?? '-' }}</td>
                <td>{{ $row->account_name ?? '#' . $row->coa_id }}</td>
                <td>{{ $row->description }}</td>
                <td class="text-right">{{ number_format($row->debit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row->credit, 0, ',', '.') }}</td>
                <td class="text-center">
                    @switch($row->trx_type)
                    @case('cash_in') Masuk @break
                    @case('cash_out') Keluar @break
                    @case('cash_transfer_in') Transfer Masuk @break
                    @case('cash_transfer_out') Transfer Keluar @break
                    @default {{ $row->trx_type }}
                    @endswitch
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Grand Total</td>
                <td class="text-right">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totalCredit, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div style="font-size:10px; text-align:center; margin-top:20px;">
        Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
