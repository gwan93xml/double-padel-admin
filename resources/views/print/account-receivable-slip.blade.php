<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receivable Slip - {{ $header->number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .title { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2 class="title">Receivable Slip</h2>

    {{-- Header --}}
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
            <th>Customer</th>
            <td>{{ $header->customer->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Remarks</th>
            <td colspan="3">{{ $header->remarks ?? '-' }}</td>
        </tr>
    </table>

    {{-- Details --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Receivable Number</th>
                <th>Receivable Date</th>
                <th>Original Amount</th>
                <th>Discount</th>
                <th>Paid Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($details as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->receivable?->sale?->number ?? '-' }}</td>
                    <td>{{ optional($item->receivable->date)->format('d-m-Y') }}</td>
                    <td class="text-right">{{ number_format($item->receivable->total ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->discount, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->paid_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr style="font-weight:bold;">
                <td colspan="5" class="text-right">Total Received</td>
                <td class="text-right">{{ number_format($header->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size:10px; text-align:center; margin-top:20px;">
        Printed at {{ now()->format('d F Y H:i') }}
    </p>
</body>
</html>
