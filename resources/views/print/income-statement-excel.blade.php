@php
    use Carbon\Carbon;

    $safeNumber = fn($v) => (is_numeric($v) && is_finite((float)$v)) ? (float)$v : (float)($v ?? 0);
    
    $sum = function ($arr) use ($safeNumber) {
        $t = 0; foreach ($arr as $v) $t += $safeNumber($v); return $t;
    };

    $COGS_IN_GROUPS   = ['beginningInventories','purchases','purchaseDiscounts','shippingFees','purchaseReturns','stockCorrections'];
    $ENDING_INV_GROUP = 'endingInventories';

    $sumByGroups = function (array $data, $groups, $divisionId = null) use ($sum, $safeNumber) {
        $groupSet = collect(is_array($groups) ? $groups : [$groups])->flip();
        $amounts = $data['amounts'] ?? [];

        $acc = 0;
        foreach ($amounts as $a) {
            if (!$groupSet->has($a['group'] ?? null)) continue;
            $divs = $a['divisions'] ?? [];

            if ($divisionId === null) {
                $acc += $sum(array_map(fn($d) => $safeNumber($d['amount'] ?? 0), $divs));
            } else {
                $found = null;
                foreach ($divs as $d) { if (($d['division_id'] ?? null) == $divisionId) { $found = $d; break; } }
                $acc += $safeNumber($found['amount'] ?? 0);
            }
        }
        return $acc;
    };

    // Function untuk get raw numeric value tanpa formatting (untuk Excel)
    $getRawAmount = function($divisions, $divisionId) use ($safeNumber) {
        $found = null;
        foreach ($divisions as $d) { 
            if (($d['division_id'] ?? null) == $divisionId) { 
                $found = $d; 
                break; 
            } 
        }
        $amount = $found['amount'] ?? 0;
        
        // Clean string input tapi return raw numeric value
        if (is_string($amount)) {
            $amount = preg_replace('/[^-0-9.]/', '', $amount);
        }
        
        return $safeNumber($amount);
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

<table>
    <tr>
        <th colspan="{{ 1 + count($divisions) + 1 }}">LAPORAN LABA RUGI PERIODE {{ $start }} s/d {{ $end }}</th>
    </tr>
    <tr>
        <th>&nbsp;</th>
        @foreach ($divisions as $division)
            <th>{{ getDivisionInitial($division['name']) ?? '' }}</th>
        @endforeach
        <th>TOTAL</th>
    </tr>

    {{-- Sales --}}
    @php $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'sales')); @endphp
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row['account_name'] ?? '' }}</td>
            @foreach ($divisions as $division)
                @php
                    $val = $getRawAmount($row['divisions'] ?? [], $division['id']);
                @endphp
                <td>{{ $val }}</td>
            @endforeach
            @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
            <td>{{ $rowTotal }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: bold; text-align: center;"><strong>TOTAL PENDAPATAN :</strong></td>
        @foreach ($divisions as $division)
            @php
                $perDiv = 0;
                foreach (($data['amounts'] ?? []) as $a) {
                    if (($a['group'] ?? null) === 'sales') {
                        $found = null;
                        foreach (($a['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                        $perDiv += (float)($found['amount'] ?? 0);
                    }
                }
            @endphp
            <td style="font-weight: bold;">{{ $perDiv }}</td>
        @endforeach
        @php
            $totalSalesAll = 0;
            foreach (($data['amounts'] ?? []) as $a) {
                if (($a['group'] ?? null) === 'sales') {
                    $totalSalesAll += $sum(array_map(fn($d)=>$d['amount'] ?? 0, $a['divisions'] ?? []));
                }
            }
        @endphp
        <td style="font-weight: bold;">{{ $totalSalesAll }}</td>
    </tr>
    <tr><td>&nbsp;</td></tr>

    {{-- COGS Components --}}
    @foreach (['beginningInventories'=>'Persediaan Awal','purchases'=>'Pembelian','purchaseDiscounts'=>'Potongan Pembelian','shippingFees'=>'Biaya Pengiriman','purchaseReturns' => 'Retur Pembelian','stockCorrections'=>'Koreksi Stok'] as $grpKey => $grpLabel)
        @php $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === $grpKey)); @endphp
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['account_name'] ?? $grpLabel }}</td>
                @foreach ($divisions as $division)
                    @php
                        $found = null;
                        foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                        $val = (float)($found['amount'] ?? 0);
                    @endphp
                    <td>{{ $val }}</td>
                @endforeach
                @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
                <td>{{ $rowTotal }}</td>
            </tr>
        @endforeach
    @endforeach

    {{-- Ending Inventories --}}
    @php $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'endingInventories')); @endphp
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row['account_name'] ?? 'Persediaan Akhir' }}</td>
            @foreach ($divisions as $division)
                @php
                    $found = null;
                    foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                    $val = (float)($found['amount'] ?? 0);
                @endphp
                <td>{{ $val }}</td>
            @endforeach
            @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
            <td>{{ $rowTotal }}</td>
        </tr>
    @endforeach

    {{-- TOTAL HPP --}}
    <tr>
        <td style="font-weight: bold; text-align: center;"><strong>TOTAL HARGA POKOK PENJUALAN :</strong></td>
        @foreach ($divisions as $division)
            @php
                $hppDiv = $sumByGroups($data, $COGS_IN_GROUPS, $division['id']) - $sumByGroups($data, $ENDING_INV_GROUP, $division['id']);
            @endphp
            <td style="font-weight: bold;">{{ $hppDiv }}</td>
        @endforeach
        @php
            $hppAll = $sumByGroups($data, $COGS_IN_GROUPS) - $sumByGroups($data, $ENDING_INV_GROUP);
        @endphp
        <td style="font-weight: bold;">{{ $hppAll }}</td>
    </tr>
    <tr><td>&nbsp;</td></tr>

    {{-- LABA (RUGI) KOTOR --}}
    <tr>
        <td style="font-weight: bold; text-align: center;"><strong>LABA (RUGI) KOTOR :</strong></td>
        @foreach ($divisions as $division)
            @php
                $salesDiv = $sumByGroups($data, 'sales', $division['id']);
                $hppDiv   = $sumByGroups($data, $COGS_IN_GROUPS, $division['id']) - $sumByGroups($data, $ENDING_INV_GROUP, $division['id']);
                $grossDiv = $salesDiv - $hppDiv;
            @endphp
            <td style="font-weight: bold; background-color: #f0f0f0;">{{ $grossDiv }}</td>
        @endforeach
        @php
            $salesAll = $sumByGroups($data, 'sales');
            $hppAll   = $sumByGroups($data, $COGS_IN_GROUPS) - $sumByGroups($data, $ENDING_INV_GROUP);
            $grossAll = $salesAll - $hppAll;
        @endphp
        <td style="font-weight: bold; background-color: #f0f0f0;">{{ $grossAll }}</td>
    </tr>
    <tr><td>&nbsp;</td></tr>

    {{-- EXPENSES --}}
    @php $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'expenses')); @endphp
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row['account_name'] ?? '' }}</td>
            @foreach ($divisions as $division)
                @php
                    $found = null;
                    foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                    $val = (float)($found['amount'] ?? 0);
                @endphp
                <td>{{ $val }}</td>
            @endforeach
            @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
            <td>{{ $rowTotal }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: bold; text-align: center;"><strong>TOTAL BIAYA/BEBAN :</strong></td>
        @foreach ($divisions as $division)
            @php $totalExpDiv = $sumByGroups($data, 'expenses', $division['id']); @endphp
            <td style="font-weight: bold;">{{ $totalExpDiv }}</td>
        @endforeach
        @php $totalExpAll = $sumByGroups($data, 'expenses'); @endphp
        <td style="font-weight: bold;">{{ $totalExpAll }}</td>
    </tr>
    <tr><td>&nbsp;</td></tr>

    {{-- OTHER REVENUES --}}
    @php $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'otherRevenues')); @endphp
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row['account_name'] ?? '' }}</td>
            @foreach ($divisions as $division)
                @php
                    $found = null;
                    foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                    $val = (float)($found['amount'] ?? 0);
                @endphp
                <td>{{ $val }}</td>
            @endforeach
            @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
            <td>{{ $rowTotal }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: bold; text-align: center;"><strong>TOTAL PENDAPATAN LAINNYA :</strong></td>
        @foreach ($divisions as $division)
            @php $totORDiv = $sumByGroups($data, 'otherRevenues', $division['id']); @endphp
            <td style="font-weight: bold;">{{ $totORDiv }}</td>
        @endforeach
        @php $totORAll = $sumByGroups($data, 'otherRevenues'); @endphp
        <td style="font-weight: bold;">{{ $totORAll }}</td>
    </tr>
    <tr><td>&nbsp;</td></tr>

    {{-- OTHER EXPENSES --}}
    @php $rows = array_values(array_filter($data['amounts'] ?? [], fn($a) => ($a['group'] ?? null) === 'otherExpenses')); @endphp
    @foreach ($rows as $row)
        <tr>
            <td>{{ $row['account_name'] ?? '' }}</td>
            @foreach ($divisions as $division)
                @php
                    $found = null;
                    foreach (($row['divisions'] ?? []) as $d) { if (($d['division_id'] ?? null) == $division['id']) { $found = $d; break; } }
                    $val = (float)($found['amount'] ?? 0);
                @endphp
                <td>{{ $val }}</td>
            @endforeach
            @php $rowTotal = $sum(array_map(fn($d)=>$d['amount'] ?? 0, $row['divisions'] ?? [])); @endphp
            <td>{{ $rowTotal }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: bold; text-align: center;"><strong>TOTAL BIAYA/BEBAN LAINNYA :</strong></td>
        @foreach ($divisions as $division)
            @php $totOEDiv = $sumByGroups($data, 'otherExpenses', $division['id']); @endphp
            <td style="font-weight: bold;">{{ $totOEDiv }}</td>
        @endforeach
        @php $totOEAll = $sumByGroups($data, 'otherExpenses'); @endphp
        <td style="font-weight: bold;">{{ $totOEAll }}</td>
    </tr>
    <tr><td>&nbsp;</td></tr>

    {{-- NET PROFIT / LOSS --}}
    <tr>
        <td style="font-weight: bold; text-align: center;"><strong>LABA (RUGI) PERIODE BERJALAN :</strong></td>
        @foreach ($divisions as $division)
            @php $labaDiv = $getLabaRugiPeriodeBerjalanPerDivisi($data, $division['id']); @endphp
            <td style="font-weight: bold; background-color: #e0e0e0; border: 2px solid #000;">{{ $labaDiv }}</td>
        @endforeach
        @php $labaAll = $getLabaRugiPeriodeBerjalanSemuaDivisi($data); @endphp
        <td style="font-weight: bold; background-color: #e0e0e0; border: 2px solid #000;">{{ $labaAll }}</td>
    </tr>
</table>
