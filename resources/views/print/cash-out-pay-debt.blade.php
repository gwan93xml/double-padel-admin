<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Kas Keluar</title>
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
    <h3>BUKTI PEMBAYARAN HUTANG</h3>
    <table>
        <tr>
            <td class="no-border">{{$cashOut->number}}</td>
            <td class="no-border" style="text-align:right;">
                User: {{$cashOut->entry_by}} {{Carbon\Carbon::parse($cashOut->entry_at)->format('d M Y H:i:s')}}<br>

                Tanggal: {{Carbon\Carbon::parse($cashOut->date)->format('d F Y')}}
            </td>
        </tr>
    </table>
    <br>
    <p>DIBAYAR KEPADA : <b>{{$cashOut->paid_to}}</b></p>
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
            <tr>
                <td>1.</td>
                <td>Pembayaran hutang {{$cashOut->payDebtHeader->number}}</td>
                <td>IDR</td>
                <td class="text-right">{{rupiah($cashOut->payDebtHeader->total)}}</td>
                <td></td>
            </tr>
            @foreach($cashOut->details as $detail)
            <tr>
                <td>{{$loop->iteration + 1}}.</td>
                <td>{{$detail->chart_ofAccount->name}}</td>
                <td>IDR</td>
                <td></td>
                <td class="text-right">{{rupiah($detail->credit)}}</td>
            </tr>
            @endforeach
            <tr class="total">
                <td colspan="3" style="text-align:center;">TOTAL</td>
                <td class="text-right">{{rupiah($cashOut->payDebtHeader->total)}}</td>
                <td class="text-right">{{rupiah($cashOut->details->sum('credit'))}}</td>
            </tr>
        </tbody>
    </table>
    <p>Catatan: {{$cashOut->description}}</p>
    <p>Printed: {{auth()->user()->name}} - {{now()->format('H:i:s')}}, {{now()->format('D, d F Y')}}</p>
</body>
</html>
