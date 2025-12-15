<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Kas Masuk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 4px;
            text-align: left;
        }

        .no-border {
            border: none;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .total {
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

    </style>
</head>
<body>
    <div class="header">
    </div>
    <h3>
        @if($cashIn->accountReceivableHeader)
        BUKTI PENERIMAAN PIUTANG
        @else
        BUKTI KAS MASUK
        @endif
    </h3>
    <table>
        <tr>
            <td class="no-border">{{$cashIn->number}}</td>
            <td class="no-border" style="text-align:right;">
                User: {{$cashIn->entry_by}} {{Carbon\Carbon::parse($cashIn->entry_at)->format('d M Y H:i:s')}}<br>

                Tanggal: {{Carbon\Carbon::parse($cashIn->date)->format('d F Y')}}
            </td>
        </tr>
    </table>
    <br>
    <p>DITERIMA DARI : <b>{{$cashIn->received_from}}</b></p>
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>KETERANGAN</th>
                <th>MU</th>
                <th>DEBET</th>
                <th>KREDIT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashIn->details as $index => $detail)
            <tr>
                <td>{{$index+1}}.</td>
                <td>{{$detail->chart_ofAccount->name}}</td>
                <td>IDR</td>
                <td class="text-right">{{rupiah($detail->debit)}}</td>
                <td class="text-right">{{rupiah($detail->credit)}}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="3" style="text-align:center;">TOTAL</td>
                <td class="text-right">{{rupiah($cashIn->details->sum('debit'))}}</td>
                <td class="text-right">{{rupiah($cashIn->details->sum('credit'))}}</td>
            </tr>
        </tbody>
    </table>
    <p>Catatan: {{$cashIn->description}}</p>
    <p>Printed: {{auth()->user()->name}} - {{now()->format('H:i:s')}}, {{now()->format('D, d F Y')}}</p>
</body>
</html>
