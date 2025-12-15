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
            font-size: 10pt;
            letter-spacing: 0px;
            box-sizing: border-box;

        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 14pt;
        }

        .header {
            margin: 0;
            font-size: 12pt;
            font-weight: bolder;
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
            border: 0;
        }

        .border-t-0 {
            border-top: 0;
        }

        .border-b-0 {
            border-bottom: 0;
        }

        .border-l-0 {
            border-left: 0;
        }

        .border-r-0 {
            border-right: 0;
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

        .p-0 {
            padding: 0;
        }

        .m-0 {
            margin: 0;
        }

        .border-y {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        .bg-white {
            background-color: #fff;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .border {
            border: 1px solid #000;
        }

        .p-2 {
            padding: 8px;
        }

        .font-semibold {
            font-weight: 600;
        }

        .valign-top {
            vertical-align: top;
        }

        .m-0 {
            margin: 0;
        }

        .p-0 {
            padding: 0;
        }

        .underline {
            text-decoration: underline;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .text-3xl {
            font-size: 1.875rem;
        }

        .font-semibold {
            font-weight: 600;
        }
        .pt-1 {
            padding-top: 0.25rem;
        }

        .pb-1 {
            padding-bottom: 0.25rem;
        }
        .pb-2 {
            padding-bottom: 0.5rem;
        }
        .pb-3 {
            padding-bottom: 0.75rem;
        }
        .pb-4 {
            padding-bottom: 1rem;
        }
        .mb-0 {
            margin-bottom: 0;
        }
        .pr-2 {
            padding-right: 0.5rem;
        }
        .pl-2 {
            padding-left: 0.5rem;
        }
        .mb-1 {
            margin-bottom: 0.25rem;
        }
        .pt-half {
            padding-top: 0.125rem;
        }


    </style>
</head>
<body>
    @yield('content')
</body>
</html>
