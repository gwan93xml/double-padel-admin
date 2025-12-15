<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .header-left img {
            max-height: 60px;
        }

        .header-center {
            text-align: center;
        }

        .header-center h2 {
            margin: 0;
            font-size: 18px;
        }

        .header-left h2 {
            margin: 0;
            font-size: 18px;
        }

        .header-center p {
            margin: 2px 0;
        }

        .header-right {
            text-align: right;
        }

        .header-right .title {
            font-weight: bold;
            font-size: 16px;
        }

        .header-center .title {
            font-weight: bold;
            font-size: 16px;
        }

        .info-table,
        .items-table {
            margin-top: 10px;
        }

        .info-table td {
            padding: 2px 4px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
        }

        .items-table th {
            background: #f0f0f0;
        }

        .footer-notes {
            margin-top: 10px;
        }

        .footer-notes p {
            margin: 2px 0;
        }

        .summary {
            margin-top: 20px;
            width: 200px;
        }

        .summary td {
            padding: 2px 4px;
        }

        .signature {
            margin-top: 40px;
        }

        .signature .sig-left,
        .signature .sig-right {
            display: inline-block;
            width: 45%;
            text-align: center;
        }

    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td class="header-left" style="width: 70%;">
                {{-- <img src="{{ public_path('images/logo.png') }}" alt="Logo"> --}}
                <h2>CV. Tangier Raya Kemindo</h2>
            </td>
        </tr>
        <tr>
            <td class="header-center" style="width: 30%;" colspan="2">
                <div class="title">SURAT JALAN</div>
                <div>No. {{$salesOrder->number}}</div>
            </td>
        </tr>
    </table>

    {{-- Info --}}
    <table class="info-table">
        <tr>
            <td style="width:50%">
                <table class="info-table">
                    <tr>
                        <td style="width:50px">Tanggal</td>
                        <td>: {{ \Carbon\Carbon::parse($salesOrder->date)->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td>No. PO</td>
                        <td>: {{ $salesOrder->purchase_order_number }}</td>
                    </tr>
                    <tr>
                        <td>No. Telp</td>
                        <td>: {{ $salesOrder->customer->phone ?? '-' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width:50%">
                <table class="info-table">
                    <tr>
                        <td style="width:50px">Kepada</td>
                        <td style="width:1px">:</td>
                        <td>{{ $salesOrder->customer->name }}</td>
                    </tr>
                    <tr>
                        <td valign="top">Alamat</td>
                        <td valign="top">:</td>
                        <td>{!! nl2br(e($salesOrder->customer->address)) !!}</td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>

    {{-- Items --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:40%;">Nama Barang</th>
                {{-- <th style="width:10%;">Isi</th> --}}
                <th style="width:8%;">Qty</th>
                <th style="width:10%;">Satuan</th>
                {{-- <th style="width:10%;">Ukuran</th> --}}
                <th style="width:18%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesOrder->items as $i => $it)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $it->item_name }}</td>
                {{-- <td class="text-center">{{ $it->notes }}</td> --}}
                <td class="text-center">{{ $it['quantity'] }}</td>
                <td class="text-center">{{ $it['unit'] }}</td>
                {{-- <td class="text-center">{{ $it['size'] }}</td> --}}
                <td>{{ $it['notes'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer Notes --}}
    <div class="footer-notes">
        <p><strong>Perhatian :</strong></p>
        <p>* Harap memeriksa kondisi barang dan menghitung kuantitas sesuai dengan surat jalan</p>
        <p>* Komplain tidak dapat diterima apabila surat jalan sudah ditandatangani</p>
        <p>* Barang dengan kondisi baik tidak dapat dikembalikan atau ditukar, kecuali sudah ada perjanjian sebelumnya</p>
    </div>

    {{-- Summary Totals --}}
    {{-- <table class="summary">
        <tr>
            <td><strong>Total Qty</strong></td>
            <td class="text-right">{{ $salesOrder->items->sum('quantity') }}</td>
    </tr>
    <tr>
        <td><strong>Bruto</strong></td>
        <td class="text-right">{{ $totals['bruto'] }}</td>
    </tr>
    </table> --}}

    {{-- Signatures --}}
    <div class="signature">
        <div class="sig-left">
            <p>Pengirim,</p>
            <br><br><br>
            <p>____________________</p>
        </div>
        <div class="sig-right">
            <p>Penerima,</p>
            <br><br><br>
            <p>____________________</p>
        </div>
    </div>

</body>
</html>
