@php
// Angka dikeluarkan sebagai NUMERIC agar Excel bisa format ribuan.
$num = fn($v) => (int) round($v ?? 0);
$dmy = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '';
$ph = ''; // placeholder untuk kolom kosong

$report = $data;

$sumBalances = function ($items) {
$t = 0;
foreach (($items ?? []) as $it) {
$t += (float) data_get($it, 'balance', 0);
}
return $t;
};

// AKTIVA LANCAR
$kasBank = $sumBalances($report['balanceKasDanBanks'] ?? []);
$piutangUsaha = $sumBalances($report['balancePiutangUsahas'] ?? []);
$piutangLain = $sumBalances($report['balancePiutangLainnyas'] ?? []);
$persediaan = $sumBalances($report['balancePersediaans'] ?? []);
$uangMukaPembelian = $sumBalances($report['balanceUangMukaPembelians'] ?? []);
$potonganPembelian = $sumBalances($report['balancePotonganPembelians'] ?? []);
$internetDibayarDimuka = $sumBalances($report['balanceInternetDibayarDimukas'] ?? []);
$sewaDimuka = $sumBalances($report['balanceSewaDibayarDimukas'] ?? []);
$asuransiDimuka = $sumBalances($report['balanceAsuransiDibayarDimukas']?? []);
$ppnDimuka = $sumBalances($report['balancePPnDibayarDimukas'] ?? []);
$komisiDibayarDimuka = $sumBalances($report['balanceKomisiDibayarDimukas'] ?? []); // tambahan komisi dibayar dimuka
$totalAktivaLancar = $kasBank + $piutangUsaha + $piutangLain + $persediaan + $uangMukaPembelian + $sewaDimuka + $asuransiDimuka + $ppnDimuka + $potonganPembelian + $internetDibayarDimuka + $komisiDibayarDimuka;

// AKTIVA TETAP
$atPeralatan = $sumBalances($report['balanceATPeralatanKantors'] ?? []);
$atKendaraan = $sumBalances($report['balanceATKendaraans'] ?? []);
$atBangunan = $sumBalances($report['balanceATBangunans'] ?? []);

// Lain-lain (kolom kanan)
$royaltiTAP = $sumBalances($report['balanceRoyaltiTAPs'] ?? []);
$unassign = $sumBalances($report['balancePerkiraanUnassigns'] ?? []);
$perantara = $sumBalances($report['balancePerkiraanPerantaras'] ?? []);

$totalAktivaTetap = $atPeralatan + $atKendaraan + $atBangunan;
$totalAktiva = $totalAktivaLancar + $totalAktivaTetap + $royaltiTAP + $unassign + $perantara;

// KEWAJIBAN
$kewajibanLancar = $sumBalances($report['balanceKewajibanLancars'] ?? []);
$hutangPPN = $sumBalances($report['balanceHutangPPns'] ?? []);
$kewajiban = $sumBalances($report['balanceKewajibans']?? []);
$kewajibanJPanjang = $sumBalances($report['balanceKewajibanJangkaPanjangs'] ?? []);
$totalKewajiban = $kewajibanLancar + $hutangPPN + $kewajiban + $kewajibanJPanjang;

// EKUITAS
$totalEkuitas = $sumBalances($report['balanceEkuitas'] ?? []);

$totalKewajibanEkuitas = $totalKewajiban + $totalEkuitas;

$balanceDiff = $totalAktiva - $totalKewajibanEkuitas;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <tr>
            <td colspan="4"><strong>Laporan Neraca</strong></td>
        </tr>
        @if(!empty($dateFrom) || !empty($dateTo))
        <tr>
            <td colspan="4">Periode: {{ $dmy($dateFrom ?? null) ?: $ph }} s/d {{ $dmy($dateTo ?? null) ?: $ph }}</td>
        </tr>
        @endif
    </table>

    {{-- ===================== AKTIVA ===================== --}}
    <table>
        <tr>
            <td colspan="4" style="font-weight:bold; text-align:center;">AKTIVA</td>
        </tr>
        <tr>
            <td colspan="4" style="font-weight:bold;">AKTIVA LANCAR</td>
        </tr>

        {{-- KAS DAN BANK --}}
        <tr>
            <td style="font-weight:bold;">KAS DAN BANK</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @foreach(($report['balanceKasDanBanks'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($kasBank) }}</td>
            <td></td>
        </tr>

        {{-- PIUTANG USAHA --}}
        <tr>
            <td style="font-weight:bold;">PIUTANG USAHA</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @foreach(($report['balancePiutangUsahas'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($piutangUsaha) }}</td>
            <td></td>
        </tr>

        {{-- PIUTANG LAINNYA --}}
        <tr>
            <td style="font-weight:bold;">PIUTANG LAINNYA</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @foreach(($report['balancePiutangLainnyas'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($piutangLain) }}</td>
            <td></td>
        </tr>

        {{-- PERSEDIAAN --}}
        @foreach(($report['balancePersediaans'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- UANG MUKA PEMBELIAN --}}
        @foreach(($report['balanceUangMukaPembelians'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- POTONGAN PEMBELIAN --}}
        @foreach(($report['balancePotonganPembelians'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>

        @endforeach

        {{-- INTERNET DIBAYAR DIMUKA --}}

        @foreach(($report['balanceInternetDibayarDimukas'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- SEWA DIBAYAR DIMUKA --}}
        @foreach(($report['balanceSewaDibayarDimukas'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- ASURANSI DIBAYAR DIMUKA --}}
        @foreach(($report['balanceAsuransiDibayarDimukas'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- PPN DIBAYAR DIMUKA --}}
        @foreach(($report['balancePPnDibayarDimukas'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach


        {{-- KOMISI DIBAYAR DIMUKA --}}
        @foreach(($report['balanceKomisiDibayarDimukas'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- SUBTOTAL AKTIVA LANCAR (kolom ke-4) --}}
        <tr>
            <td></td>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($totalAktivaLancar) }}</td>
        </tr>

        {{-- AKTIVA TETAP --}}
        <tr>
            <td colspan="4" style="font-weight:bold;">AKTIVA TETAP</td>
        </tr>

        {{-- AT - PERALATAN KANTOR --}}
        <tr>
            <td style="font-weight:bold;">AT - PERALATAN KANTOR</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @foreach(($report['balanceATPeralatanKantors'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($atPeralatan) }}</td>
            <td></td>
        </tr>

        {{-- AT - KENDARAAN --}}
        <tr>
            <td style="font-weight:bold;">AT - KENDARAAN</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @foreach(($report['balanceATKendaraans'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($atKendaraan) }}</td>
            <td></td>
        </tr>

        {{-- AT - BANGUNAN --}}
        <tr>
            <td style="font-weight:bold;">AT - BANGUNAN</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @foreach(($report['balanceATBangunans'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($atBangunan) }}</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($totalAktivaTetap) }}</td>
        </tr>

        {{-- Royalti / Unassign / Perantara (kolom terakhir) --}}
        @foreach(($report['balanceRoyaltiTAPs'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
        </tr>
        @endforeach
        @foreach(($report['balancePerkiraanUnassigns'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
        </tr>
        @endforeach
        @foreach(($report['balancePerkiraanPerantaras'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
        </tr>
        @endforeach

        {{-- TOTAL AKTIVA --}}
        <tr>
            <td></td>
            <td style="font-weight:bold; text-align:right;">TOTAL AKTIVA :</td>
            <td></td>
            <td style="font-weight:bold; text-align:right;">{{ $num($totalAktiva) }}</td>
        </tr>
    </table>

    {{-- ===================== KEWAJIBAN ===================== --}}
    <table>
        <tr>
            <td colspan="4" style="font-weight:bold; text-align:center;">KEWAJIBAN</td>
        </tr>
        <tr>
            <td colspan="4" style="font-weight:bold;">KEWAJIBAN LANCAR</td>
        </tr>
        @foreach(($report['balanceKewajibanLancars'] ?? []) as $row)
        <tr>
            <td>{{ $row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- HUTANG PPN --}}
        <tr>
            <td style="font-weight:bold;">HUTANG PPN</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @foreach(($report['balanceHutangPPns'] ?? []) as $row)
        <tr>
            <td>{{ '  '.$row['account'] }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($hutangPPN) }}</td>
            <td></td>
        </tr>

        {{-- Pendapatan diterima dimuka --}}
        @foreach(($report['balanceKewajibans'] ?? []) as $row)
        <tr>
            <td>{{ $row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- Subtotal Kewajiban (posisi sesuai JSX) --}}
        <tr>
            <td></td>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($kewajibanLancar + $hutangPPN + $kewajiban) }}</td>
        </tr>

        {{-- Kewajiban Jangka Panjang --}}
        <tr>
            <td colspan="4" style="font-weight:bold;">KEWAJIBAN JANGKA PANJANG</td>
        </tr>
        @foreach(($report['balanceKewajibanJangkaPanjangs'] ?? []) as $row)
        <tr>
            <td>{{ $row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach
        <tr>
            <td></td>
            <td></td>
            <td class="text-right">>></td>
            <td class="text-right">{{ $num($kewajibanJPanjang) }}</td>
        </tr>

        {{-- TOTAL KEWAJIBAN --}}
        <tr>
            <td></td>
            <td style="font-weight:bold; text-align:right;">TOTAL KEWAJIBAN :</td>
            <td></td>
            <td style="font-weight:bold; text-align:right;">{{ $num($totalKewajiban) }}</td>
        </tr>
    </table>

    {{-- ===================== EKUITAS ===================== --}}
    <table>
        <tr>
            <td colspan="4" style="font-weight:bold; text-align:center;">EKUITAS</td>
        </tr>
        @foreach(($report['balanceEkuitas'] ?? []) as $row)
        <tr>
            <td>{{ $row['account'] }}</td>
            <td class="text-right">{{ $ph }}</td>
            <td class="text-right">{{ $num($row['balance'] ?? 0) }}</td>
            <td class="text-right">{{ $ph }}</td>
        </tr>
        @endforeach

        {{-- TOTAL EKUITAS --}}
        <tr>
            <td></td>
            <td style="font-weight:bold; text-align:right;">TOTAL EKUITAS :</td>
            <td></td>
            <td style="font-weight:bold; text-align:right;">{{ $num($totalEkuitas) }}</td>
        </tr>

        {{-- TOTAL KEWAJIBAN & EKUITAS --}}
        <tr>
            <td></td>
            <td style="font-weight:bold; text-align:right;">TOTAL KEWAJIBAN &amp; EKUITAS :</td>
            <td></td>
            <td style="font-weight:bold; text-align:right;">{{ $num($totalKewajibanEkuitas) }}</td>
        </tr>

        {{-- BALANCE (Aktiva - (Kewajiban + Ekuitas)) --}}
        <tr>
            <td></td>
            <td style="font-weight:bold; text-align:right;">Balance :</td>
            <td></td>
            <td style="font-weight:bold; text-align:right;">{{ $num($balanceDiff) }}</td>
        </tr>
    </table>
</body>
</html>
