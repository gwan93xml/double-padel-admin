<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>INVOICE {{ $sale->no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header,
        .info,
        .items,
        .totals,
        .footer {
            width: 100%;
        }

        .header td {
            vertical-align: top;
        }

        .header .logo {
            width: 15%;
        }

        .header .center {
            width: 55%;
            text-align: center;
        }

        .header .right {
            width: 30%;
            text-align: right;
        }

        .header .center .title {
            font-size: 16px;
            font-weight: bold;
        }

        .header .center .subtitle {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
        }

        .info td {
            padding: 2px 4px;
        }

        .items th,
        .items td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }

        .items th {
            background: #f0f0f0;
        }

        .items .text-center {
            text-align: center;
        }

        .totals td {
            padding: 4px 6px;
        }

        .totals .label {
            text-align: left;
            font-weight: bold;
        }

        .payment {
            margin-top: 24px;
        }

        .signature {}

        .signature .sig-left {
            display: inline-block;
            width: 40%;
            text-align: center;
        }

        .text-right {
            text-align: right !important;
        }

        .invoice-number {
            font-weight: bold;
            font-size: 12px;
        }

    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header">
        <tr>
            <td class="center">
                <strong class="title">CV. TANGIER RAYA KEMINDO</strong><br>
                {{-- Jl. Contoh No. 123, Kota Contoh<br>
        Telp. (0123) 456-7890 --}}
            </td>
        </tr>
        <tr>
            <td class="center">
                <div class="subtitle">INVOICE</div>
                <div class="invoice-number">No. {{ $sale->no }}-{{Carbon\Carbon::parse($sale->sale_date)->format('m-y')}}</div>
            </td>
        </tr>
    </table>

    {{-- Info --}}
    <table class="info" cellpadding="0" cellspacing="0" style="margin-top: 10px; margin-bottom: 10px;">
        <tr>
            <td style="width : 58%">
                <table class="info" cellpadding="0" cellspacing="0" style="margin-top: 10px; margin-bottom: 10px;">
                    <tr>
                        <td style="width : 10%">Nama</td>
                        <td style="width : 50%">: {{ $sale->customer->name }}</td>
                    </tr><tr>
                        <td style="width : 10%">Tanggal</td>
                        <td style="width : 50%">: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d F Y') }}</td>
                    </tr>
    </table>
    </td>
    <td style="width : 42%" valign="top">
        <table class="info" cellpadding="0" cellspacing="0" style="margin-top: 10px; margin-bottom: 10px;">
            <tr>
                <td style="width : 10%" valign="top">No. PO</td>
                <td style="width:1px" valign="top">:</td>
                <td style="width : 40%">{{ $sale->purchase_order_number }}</td>
            </tr><tr>
                <td style="width : 10%" valign="top">No. SJ</td>
                <td style="width:1px" valign="top">:</td>
                <td style="width : 40%">{{ $sale->no }}-{{Carbon\Carbon::parse($sale->sale_date)->format('m-Y')}}</td>
            </tr>
            <tr>
                <td valign="top">
                {{-- Alamat --}}
                </td>
                <td style="width:1px" valign="top">
                {{-- : --}}
                </td>
                <td valign="top">
                {{-- {{ $sale->customer['address'] }} --}}
                </td>
            </tr>
            {{-- <tr>
                        <td>No. PO</td>
                        <td>: {{ $sale->purchase_order_number }}
    </td>
    <td>Alamat</td>
    <td>: {!! nl2br(e($sale->customer['address'])) !!}</td>
    </tr>
    <tr>
        <td>No. SJ</td>
        <td>: {{ $sale->no }}-{{Carbon\Carbon::parse($sale->sale_date)->format('m-Y')}}</td>
        <td></td>
        <td></td>
    </tr> --}}
    </table>
    </td>
    </tr>
    {{-- <tr>
            <td style="width : 10%">Tanggal</td>
            <td style="width : 50%">: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d F Y') }}</td>
    <td style="width : 2%">Nama</td>
    <td style="width : 40%">: {{ $sale->customer['name'] }}</td>
    </tr>
    <tr>
        <td>No. PO</td>
        <td>: {{ $sale->purchase_order_number }}</td>
        <td>Alamat</td>
        <td>: {!! nl2br(e($sale->customer['address'])) !!}</td>
    </tr>
    <tr>
        <td>No. SJ</td>
        <td>: {{ $sale->no }}-{{Carbon\Carbon::parse($sale->sale_date)->format('m-Y')}}</td>
        <td></td>
        <td></td>
    </tr> --}}
    </table>

    {{-- Items --}}
    <table class="items" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th style="width:4%;" class="text-center">No</th>
                <th style="width:32%;" class="text-center">Nama</th>
                <th style="width:8%;" class="text-center">Qty</th>
                <th style="width:18%;" class="text-center">Keterangan</th>
                <th style="width:12%;" class="text-center">Harga</th>
                <th style="width:12%;" class="text-center">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $i => $it)
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td>{{ $it['item_name'] }}</td>
                <td >{{ $it['quantity'] }} {{ $it['unit'] }}</td>
                <td class="text-right">{{ $it['notes'] }}</td>
                <td class="text-right">{{ number_format($it['price'],0,',','.') }}</td>
                <td class="text-right">{{ number_format($it['subtotal'],0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <table class="totals">
        <tr>
            <td style="width:65%;">

            </td>
            <td class="label" style="width:13%;">Sub Total</td>
            <td class="text-right">{{ number_format($sale->items()->sum('subtotal'),0,',','.') }}</td>
        </tr>
        <tr>
            <td style="width:65%;">

            </td>
            <td class="label" style="width:13%;">PPN</td>
            <td class="text-right">{{ number_format($sale->tax,0,',','.') }}</td>
        </tr>
        <tr>
            <td style="width:65%;">

            </td>
            <td class="label" style="width:13%;">Total</td>
            <td class="text-right">{{ number_format($sale->total_amount,0,',','.') }}</td>
        </tr>
    </table>

    {{-- Totals --}}

    <table class="totals" style="width:100%; margin-top: 10px;">
        <tr>
            <td class="" style="width: 65%;">
                <div class="signature">
                    <div class="sig-left">
                        Hormat Kami,<br><br><br><br><br><br><br>
                        {{-- <img src="{{ public_path('images/stamp_and_signature.png') }}" style="max-height:60px;"><br> --}}
                        CV. Tangier Raya Kemindo
                    </div>
                </div>
            </td>
            <td style="width: 35%;" valign="bottom">
                <strong>Transfer Ke {{ $company->bankAccount->name ?? '' }}</strong><br>
                Account Number: {{ $company->bankAccount->account_number ?? '' }}<br>
                Atas Nama: {{ $company->bankAccount->account_name ?? '' }}
            </td>
        </tr>
    </table>


</body>
</html>
