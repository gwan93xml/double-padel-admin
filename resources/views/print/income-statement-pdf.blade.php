{{-- resources/views/pdf/income-statement.blade.php --}}
@php
    use Carbon\Carbon;

    /** ===================== Helpers ===================== */
    $safeNumber = fn($v) => (is_numeric($v) && is_finite((float)$v)) ? (float)$v : (float)($v ?? 0);
    $sum = function ($arr) use ($safeNumber) {
        $total = 0;
        foreach ($arr as $v) $total += $safeNumber($v);
        return $total;
    };

    // Amount groups
    $COGS_IN_GROUPS   = ['beginningInventories','purchases','purchaseDiscounts','shippingFees', 'purchaseReturns','stockCorrections'];
    $ENDING_INV_GROUP = 'endingInventories';

    // Format angka: tanpa "Rp", negatif menjadi (xxx)
    $formatRupiah = function ($amount) {
        $amount = (int) round((float)$amount ?? 0);
        $s = number_format(abs($amount), 0, ',', '.');
        return $amount < 0 ? "({$s})" : $s;
    };

    // Format angka dengan kurung untuk ending inventories
    $formatRupiahWithParentheses = function ($amount) {
        $amount = (int) round((float)$amount ?? 0);
        if ($amount == 0) return '-';
        $s = number_format(abs($amount), 0, ',', '.');
        return "({$s})";
    };

    // Sum by groups, optionally by division
    $sumByGroups = function (array $data, $groups, $divisionId = null) use ($sum, $safeNumber) {
        $groupSet = collect(is_array($groups) ? $groups : [$groups])->flip();
        $amounts = $data['amounts'] ?? [];

        $acc = 0;
        foreach ($amounts as $a) {
            if (!$groupSet->has($a['group'] ?? null)) continue;
            $divs = $a['divisions'] ?? [];

            if ($divisionId === null) {
                // semua divisi
                $acc += $sum(array_map(fn($d) => $safeNumber($d['amount'] ?? 0), $divs));
            } else {
                // per divisi
                $found = null;
                foreach ($divs as $d) {
                    if (($d['division_id'] ?? null) == $divisionId) { $found = $d; break; }
                }
                $acc += $safeNumber($found['amount'] ?? 0);
            }
        }
        return $acc;
    };

    $getLabaRugiPeriodeBerjalanPerDivisi = function (array $data, $divisionId) use ($sumByGroups, $COGS_IN_GROUPS, $ENDING_INV_GROUP) {
        $totalSales          = $sumByGroups($data, 'sales', $divisionId);
        $totalCOGSComponents = $sumByGroups($data, $COGS_IN_GROUPS, $divisionId);
        $totalEndingInv      = $sumByGroups($data, $ENDING_INV_GROUP, $divisionId);
        $totalHPP            = $totalCOGSComponents - $totalEndingInv;

        $totalExpenses       = $sumByGroups($data, 'expenses', $divisionId);
        $totalOtherRevenues  = $sumByGroups($data, 'otherRevenues', $divisionId);
        $totalOtherExpenses  = $sumByGroups($data, 'otherExpenses', $divisionId);

        return $totalSales - $totalHPP - $totalExpenses + $totalOtherRevenues - $totalOtherExpenses;
    };

    $getLabaRugiPeriodeBerjalanSemuaDivisi = function (array $data) use ($sumByGroups, $COGS_IN_GROUPS, $ENDING_INV_GROUP) {
        $totalSales          = $sumByGroups($data, 'sales');
        $totalCOGSComponents = $sumByGroups($data, $COGS_IN_GROUPS);
        $totalEndingInv      = $sumByGroups($data, $ENDING_INV_GROUP);
        $totalHPP            = $totalCOGSComponents - $totalEndingInv;

        $totalExpenses       = $sumByGroups($data, 'expenses');
        $totalOtherRevenues  = $sumByGroups($data, 'otherRevenues');
        $totalOtherExpenses  = $sumByGroups($data, 'otherExpenses');

        return $totalSales - $totalHPP - $totalExpenses + $totalOtherRevenues - $totalOtherExpenses;
    };

    $start = isset($data['startDate']) ? Carbon::parse($data['startDate'])->locale('id')->translatedFormat('d F Y') : '';
    $end   = isset($data['endDate'])   ? Carbon::parse($data['endDate'])->locale('id')->translatedFormat('d F Y')   : '';
    $divisions = $data['divisions'] ?? []; // [{id, name}]
    function getDivisionInitial($divisionName){
        if($divisionName == "KPS"){
            return "K";
        } else if($divisionName == "CV.SPN"){
            return "S";
        } else if($divisionName == "UDS"){
            return "UD";
        } else if($divisionName == "CV.TANGIER"){
            return "TR";
        } 
        return $divisionName;
    }
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 16px; margin: 0 0 10px 0; }
        .muted { color: #555; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; vertical-align: top; }
        th { border-bottom: 1px solid #000; text-align: center; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .border-t { border-top: 1px solid #000; }
        .border-b { border-bottom: 1px solid #000; }
        .border-tb { border-top: 1px solid #000; border-bottom: 1px solid #000; }
        .w-30 { width: 30%; }
        .w-15 { width: 15%; }
        .section-title { background: #e5e5e5; font-weight: bold; }
        .total-row { font-weight: bold; }
        .spacer-4 { height: 4px; }
        .spacer-8 { height: 8px; }
    </style>
</head>
<body>
    <h1>LAPORAN LABA RUGI PERIODE {{ $start }} s/d {{ $end }}</h1>

    <table>
        <thead>
            <tr>
                <th class="w-30">&nbsp;</th>
                @foreach ($divisions as $division)
                    <th class="w-15">{{ getDivisionInitial($division['name']) ?? '' }}</th>
                @endforeach
                <th class="w-15">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            {{-- PENDAPATAN (sales) --}}
            @php
                $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'sales'));
            @endphp
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['account_name'] ?? '' }}</td>
                    @foreach ($divisions as $division)
                        @php
                            $found = null;
                            foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                            $val = $found['amount'] ?? 0;
                        @endphp
                        <td class="text-right">{{ $val == 0 ? '-' : $formatRupiah($val) }}</td>
                    @endforeach
                    @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
                    <td class="text-right">{{ $formatRupiah($rowTotal) }}</td>
                </tr>
            @endforeach
            {{-- TOTAL PENDAPATAN --}}
            <tr>
                <td class="text-right total-row"><strong>TOTAL PENDAPATAN :</strong></td>
                @foreach ($divisions as $division)
                    @php
                        $perDiv = 0;
                        foreach (($data['amounts'] ?? []) as $a) {
                            if (($a['group'] ?? null) === 'sales') {
                                $found = null;
                                foreach (($a['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                                $perDiv += $found['amount'] ?? 0;
                            }
                        }
                    @endphp
                    <td class="text-right border-tb">{{ $perDiv == 0 ? '-' : $formatRupiah($perDiv) }}</td>
                @endforeach
                @php
                    $totalSalesAll = 0;
                    foreach (($data['amounts'] ?? []) as $a) {
                        if (($a['group'] ?? null) === 'sales') {
                            $totalSalesAll += $sum(array_map(fn($d)=>$d['amount'] ?? 0, $a['divisions'] ?? []));
                        }
                    }
                @endphp
                <td class="text-right border-tb">{{ $formatRupiah($totalSalesAll) }}</td>
            </tr>

            {{-- COGS components --}}
            @foreach (['beginningInventories'=>'Persediaan Awal','purchases'=>'Pembelian','purchaseDiscounts'=>'Potongan Pembelian','shippingFees'=>'Biaya Pengiriman','purchaseReturns'=>'Retur Pembelian','stockCorrections'=>'Koreksi Stok'] as $grpKey => $grpLabel)
                @php
                    $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === $grpKey));
                @endphp
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['account_name'] ?? $grpLabel }}</td>
                        @foreach ($divisions as $division)
                            @php
                                $found = null;
                                foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                                $val = $found['amount'] ?? 0;
                            @endphp
                            <td class="text-right">{{ $val == 0 ? '-' : $formatRupiah($val) }}</td>
                        @endforeach
                        @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
                        <td class="text-right">{{ $formatRupiah($rowTotal) }}</td>
                    </tr>
                @endforeach
            @endforeach

            {{-- Ending Inventories --}}
            @php
                $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'endingInventories'));
            @endphp
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['account_name'] ?? 'Persediaan Akhir' }}</td>
                    @foreach ($divisions as $division)
                        @php
                            $found = null;
                            foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                            $val = $found['amount'] ?? 0;
                        @endphp
                        <td class="text-right">{{ $val == 0 ? '-' : $formatRupiahWithParentheses($val) }}</td>
                    @endforeach
                    @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
                    <td class="text-right">{{ $formatRupiahWithParentheses($rowTotal) }}</td>
                </tr>
            @endforeach

            {{-- TOTAL HPP = (BI + Purch + Shipping + Corrections) - Ending --}}
            <tr>
                <td class="text-right total-row"><strong>TOTAL HARGA POKOK PENJUALAN :</strong></td>
                @foreach ($divisions as $division)
                    @php
                        $hppDiv = $sumByGroups($data, $COGS_IN_GROUPS, $division['id']) - $sumByGroups($data, $ENDING_INV_GROUP, $division['id']);
                    @endphp
                    <td class="text-right border-tb">{{ $formatRupiah($hppDiv) }}</td>
                @endforeach
                @php
                    $hppAll = $sumByGroups($data, $COGS_IN_GROUPS) - $sumByGroups($data, $ENDING_INV_GROUP);
                @endphp
                <td class="text-right border-tb">{{ $formatRupiah($hppAll) }}</td>
            </tr>

            {{-- LABA (RUGI) KOTOR --}}
            <tr>
                <td class="text-right total-row"><strong>LABA (RUGI) KOTOR :</strong></td>
                @foreach ($divisions as $division)
                    @php
                        $salesDiv = $sumByGroups($data, 'sales', $division['id']);
                        $hppDiv   = $sumByGroups($data, $COGS_IN_GROUPS, $division['id']) - $sumByGroups($data, $ENDING_INV_GROUP, $division['id']);
                        $grossDiv = $salesDiv - $hppDiv;
                    @endphp
                    <td class="text-right border-tb">{{ $formatRupiah($grossDiv) }}</td>
                @endforeach
                @php
                    $salesAll = $sumByGroups($data, 'sales');
                    $hppAll   = $sumByGroups($data, $COGS_IN_GROUPS) - $sumByGroups($data, $ENDING_INV_GROUP);
                    $grossAll = $salesAll - $hppAll;
                @endphp
                <td class="text-right border-tb">{{ $formatRupiah($grossAll) }}</td>
            </tr>

            {{-- EXPENSES --}}
            @php
                $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'expenses'));
            @endphp
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['account_name'] ?? '' }}</td>
                    @foreach ($divisions as $division)
                        @php
                            $found = null;
                            foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                            $val = $found['amount'] ?? 0;
                        @endphp
                        <td class="text-right">{{ $val == 0 ? '-' : $formatRupiah($val) }}</td>
                    @endforeach
                    @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
                    <td class="text-right">{{ $formatRupiah($rowTotal) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="text-right total-row"><strong>TOTAL BIAYA/BEBAN :</strong></td>
                @foreach ($divisions as $division)
                    @php $totalExpDiv = $sumByGroups($data, 'expenses', $division['id']); @endphp
                    <td class="text-right border-tb">{{ $totalExpDiv == 0 ? '-' : $formatRupiah($totalExpDiv) }}</td>
                @endforeach
                @php $totalExpAll = $sumByGroups($data, 'expenses'); @endphp
                <td class="text-right border-tb">{{ $formatRupiah($totalExpAll) }}</td>
            </tr>

            {{-- OTHER REVENUES --}}
            @php
                $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'otherRevenues'));
            @endphp
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['account_name'] ?? '' }}</td>
                    @foreach ($divisions as $division)
                        @php
                            $found = null;
                            foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                            $val = $found['amount'] ?? 0;
                        @endphp
                        <td class="text-right">{{ $val == 0 ? '-' : $formatRupiah($val) }}</td>
                    @endforeach
                    @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
                    <td class="text-right">{{ $formatRupiah($rowTotal) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="text-right total-row"><strong>TOTAL PENDAPATAN LAINNYA :</strong></td>
                @foreach ($divisions as $division)
                    @php $totORDiv = $sumByGroups($data, 'otherRevenues', $division['id']); @endphp
                    <td class="text-right border-tb">{{ $totORDiv == 0 ? '-' : $formatRupiah($totORDiv) }}</td>
                @endforeach
                @php $totORAll = $sumByGroups($data, 'otherRevenues'); @endphp
                <td class="text-right border-tb">{{ $formatRupiah($totORAll) }}</td>
            </tr>

            {{-- OTHER EXPENSES --}}
            @php
                $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'otherExpenses'));
            @endphp
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['account_name'] ?? '' }}</td>
                    @foreach ($divisions as $division)
                        @php
                            $found = null;
                            foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                            $val = $found['amount'] ?? 0;
                        @endphp
                        <td class="text-right">{{ $val == 0 ? '-' : $formatRupiah($val) }}</td>
                    @endforeach
                    @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
                    <td class="text-right">{{ $formatRupiah($rowTotal) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="text-right total-row"><strong>TOTAL BIAYA/BEBAN LAINNYA :</strong></td>
                @foreach ($divisions as $division)
                    @php $totOEDiv = $sumByGroups($data, 'otherExpenses', $division['id']); @endphp
                    <td class="text-right border-tb">{{ $totOEDiv == 0 ? '-' : $formatRupiah($totOEDiv) }}</td>
                @endforeach
                @php $totOEAll = $sumByGroups($data, 'otherExpenses'); @endphp
                <td class="text-right border-tb">{{ $formatRupiah($totOEAll) }}</td>
            </tr>

            {{-- NET PROFIT / LOSS --}}
            <tr>
                <td class="text-right total-row"><strong>LABA (RUGI) PERIODE BERJALAN :</strong></td>
                @foreach ($divisions as $division)
                    @php $labaDiv = $getLabaRugiPeriodeBerjalanPerDivisi($data, $division['id']); @endphp
                    <td class="text-right border-tb">{{ $formatRupiah($labaDiv) }}</td>
                @endforeach
                @php $labaAll = $getLabaRugiPeriodeBerjalanSemuaDivisi($data); @endphp
                <td class="text-right border-tb">{{ $formatRupiah($labaAll) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="spacer-8"></div>
    <div class="muted">
        Dicetak pada {{ Carbon::now()->locale('id')->translatedFormat('d F Y \p\u\k\u\l H:i') }}
    </div>
</body>
</html>
