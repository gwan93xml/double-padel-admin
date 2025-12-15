<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pay Debt Slip - {{ $header->number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .title {
            text-align: center;
            margin-bottom: 20px;
        }

    </style>
</head>
<body>
    <h2 class="title">Pay Debt Slip</h2>

    {{-- Header info --}}
    <table>
        <tr>
            <th>Number</th>
            <td>{{ $header->number }}</td>
            <th>Date</th>
            <td>{{ \Carbon\Carbon::parse($header->date)->format('d F Y') }}</td>
        </tr>
        <tr>
            <th>Division</th>
            <td>{{ $header->division->name ?? '-' }}</td>
            <th>Vendor</th>
            <td>{{ $header->vendor->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Remarks</th>
            <td colspan="3">{{ $header->remarks ?? '-' }}</td>
        </tr>
    </table>

    {{-- Debt details --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Debt Number</th>
                <th>Debt Date</th>
                <th>Original Amount</th>
                <th>Discount</th>
                <th>Paid Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($details as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->debt?->purchase?->number ?? '-' }}</td>
                <td>{{ optional($item->debt?->date)->format('d-m-Y') }}</td>
                <td class="text-right">{{ number_format($item->debt->total ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->discount, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->paid_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="font-weight:bold;">
                <td colspan="5" class="text-right">Total Payment</td>
                <td class="text-right">{{ number_format($header->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size:10px; text-align:center; margin-top:20px;">
        Printed at {{ now()->format('d F Y H:i') }}
    </p>
</body>
</html>
