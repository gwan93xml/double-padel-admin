@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print</title>
    <style>
        body {
            font-family: 'tahoma', sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .container {
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .company-info {
            margin-bottom: 20px;
        }

        .date-info {
            text-align: right;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .info-table {
            margin-bottom: 20px;
        }

        .info-table td {
            width: 50%;
            vertical-align: top;
        }

        .info-header {
            background-color: #fff;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }

        .items-table th {
            background-color: #f8f8f8;
        }

        .totals-table {
            width: 300px;
            margin-left: auto;
        }

        .totals-table td:first-child {
            font-weight: bold;
        }

        .totals-table td:last-child {
            text-align: right;
        }

        .text-end {
            text-align: right;
        }

        .border-0 {
            border: 0 ;
        }
        .border-top-0 {
            border-top: 0 ;
        }
        .border-bottom-0 {
            border-bottom: 0 ;
        }
        .border-left-0 {
            border-left: 0 ;
        }
        .border-right-0 {
            border-right: 0 ;
        }
        .border-l {
            border-left: 1px solid #000;
        }
        .border-r {
            border-right: 1px solid #000;
        }
        .border-t {
            border-top: 1px solid #000;
        }
        .border-b {
            border-bottom: 1px solid #000;
        }


    </style>
</head>
<body>
    @yield('content')
</body>
</html>
