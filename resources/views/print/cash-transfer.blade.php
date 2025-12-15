<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Transfer Kas</title>
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

        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table tr td {
            border: none;
            padding: 2px 0;
        }

        .info-label {
            width: 30%;
            font-weight: bold;
        }

        .info-value {
            width: 70%;
        }

    </style>
</head>
<body>
    <div class="header">
        <h3 style="margin: 0;">BUKTI TRANSFER KAS</h3>
    </div>

    <table class="info-table no-border">
        <tr>
            <td class="info-label">Nomor</td>
            <td class="info-value">: {{ $cashTransfer->number }}</td>
            <td class="info-label">Tanggal</td>
            <td class="info-value">: {{ Carbon\Carbon::parse($cashTransfer->date)->format('d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Divisi</td>
            <td class="info-value">: {{ $cashTransfer->division->name }}</td>
            <td class="info-label"></td>
            <td class="info-value"></td>
        </tr>
    </table>

    <table class="info-table no-border" style="margin-top: 5px;">
        <tr>
            <td colspan="2">
                <strong>DARI (PENGIRIM)</strong><br>
                Akun: {{ $cashTransfer->fromChartOfAccount->name }}<br>
                Jumlah: {{ rupiah($cashTransfer->from_amount ?? 0) }}<br>
                @if($cashTransfer->from_notes)
                Catatan: {{ $cashTransfer->from_notes }}
                @endif
            </td>
            <td colspan="2">
                <strong>KE (PENERIMA)</strong><br>
                Akun: {{ $cashTransfer->toChartOfAccount->name }}<br>
                Jumlah: {{ rupiah($cashTransfer->to_amount ?? 0) }}<br>
                @if($cashTransfer->to_notes)
                Catatan: {{ $cashTransfer->to_notes }}
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">DETAIL TRANSAKSI</div>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">NO</th>
                <th style="width: 50%;">KETERANGAN</th>
                <th style="width: 15%;">MU</th>
                <th style="width: 25%;">JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td>Transfer dari {{ $cashTransfer->fromChartOfAccount->name }} ke {{ $cashTransfer->toChartOfAccount->name }}</td>
                <td>IDR</td>
                <td class="text-right">{{ rupiah($cashTransfer->amount) }}</td>
            </tr>
            @if($cashTransfer->note)
            <tr>
                <td colspan="4"><strong>Catatan Umum:</strong> {{ $cashTransfer->note }}</td>
            </tr>
            @endif
            @if($cashTransfer->expense_notes)
            <tr>
                <td colspan="4"><strong>Catatan Biaya:</strong> {{ $cashTransfer->expense_notes }}</td>
            </tr>
            @endif
            <tr class="total">
                <td colspan="3" style="text-align:center;">TOTAL</td>
                <td class="text-right">{{ rupiah($cashTransfer->amount) }}</td>
            </tr>
        </tbody>
    </table>

    <br>
    <table class="info-table">
        <tr>
            <td style="border: none; padding: 10px 0;">
                <strong>Printed by:</strong> {{ auth()->user()->name }} <br>
                <strong>Date:</strong> {{ now()->format('d F Y H:i:s') }}
            </td>
        </tr>
    </table>

</body>
</html>
