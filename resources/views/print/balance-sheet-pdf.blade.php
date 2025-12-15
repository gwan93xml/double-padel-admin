@php
    // ===== Helpers =====
    $fmt = new \NumberFormatter('id_ID', \NumberFormatter::DECIMAL);
    $rp = fn($v) => (($n = (int) round($v ?? 0)) < 0)
    ? '(' . $fmt->format(abs($n)) . ')'
    : '' . $fmt->format($n);
    $dmy = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d/m/Y') : '';

    // Mengambil struktur data seperti di JSX: data.data.*
    // Terima $data (array/collection) dari controller


    $sumBalances = function ($items) {
        $total = 0;
        foreach (($items ?? []) as $it) {
            $total += (float) data_get($it, 'balance', 0);
        }
        return $total;
    };

    // ===== AKTIVA LANCAR =====
    $kasBank        = $sumBalances($report['balanceKasDanBanks']         ?? []);
    $piutangUsaha   = $sumBalances($report['balancePiutangUsahas']        ?? []);
    $piutangLain    = $sumBalances($report['balancePiutangLainnyas']      ?? []);
    $persediaan     = $sumBalances($report['balancePersediaans']          ?? []);
    $uangMukaPembelian = $sumBalances($report['balanceUangMukaPembelians'] ?? []);
    $potonganPembelian = $sumBalances($report['balancePotonganPembelians'] ?? []);
    $internetDibayarDimuka = $sumBalances($report['balanceInternetDibayarDimukas'] ?? []);
    $komisiDibayarDimuka = $sumBalances($report['balanceKomisiDibayarDimukas'] ?? []);
    $sewaDimuka     = $sumBalances($report['balanceSewaDibayarDimukas']   ?? []);
    $asuransiDimuka = $sumBalances($report['balanceAsuransiDibayarDimukas'] ?? []);
    $ppnDimuka      = $sumBalances($report['balancePPnDibayarDimukas']    ?? []);

    $totalAktivaLancar = $kasBank + $piutangUsaha + $piutangLain + $persediaan + $uangMukaPembelian + $sewaDimuka + $asuransiDimuka + $ppnDimuka + $potonganPembelian + $internetDibayarDimuka + $komisiDibayarDimuka;

    // ===== AKTIVA TETAP =====
    $atPeralatan = $sumBalances($report['balanceATPeralatanKantors'] ?? []);
    $atKendaraan = $sumBalances($report['balanceATKendaraans']       ?? []);
    $atBangunan  = $sumBalances($report['balanceATBangunans']        ?? []);

    // ===== Lain-lain (ditaruh di kolom kanan sesuai JSX) =====
    $royaltiTAP   = $sumBalances($report['balanceRoyaltiTAPs']        ?? []);
    $unassign     = $sumBalances($report['balancePerkiraanUnassigns'] ?? []);
    $perantara    = $sumBalances($report['balancePerkiraanPerantaras'] ?? []);

    $totalAktivaTetap = $atPeralatan + $atKendaraan + $atBangunan;

    // TOTAL AKTIVA (sesuai JSX: semua komponen aktiva)
    $totalAktiva = $totalAktivaLancar + $totalAktivaTetap + $royaltiTAP + $unassign + $perantara;

    // ===== KEWAJIBAN =====
    $kewajibanLancar   = $sumBalances($report['balanceKewajibanLancars']       ?? []);
    $hutangPPN         = $sumBalances($report['balanceHutangPPns']              ?? []);
    $kewajiban  = $sumBalances($report['balanceKewajibans'] ?? []);
    $hutangDireksi     = $sumBalances($report['balanceHutangDireksis'] ?? []);
    $kewajibanJPanjang = $sumBalances($report['balanceKewajibanJangkaPanjangs'] ?? []);

    $totalKewajiban = $kewajibanLancar + $hutangPPN + $kewajiban + $kewajibanJPanjang ;

    // ===== EKUITAS =====
    $totalEkuitas = $sumBalances($report['balanceEkuitas'] ?? []);

    // TOTAL KEWAJIBAN & EKUITAS (secara akuntansi seharusnya ini +)
    $totalKewajibanEkuitas = $totalKewajiban + $totalEkuitas;

    // Balance check (harusnya 0 jika seimbang)
    $balanceDiff = $totalAktiva - $totalKewajibanEkuitas;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Neraca</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color:#111; }
        h1 { font-size: 18px; margin:0 0 6px; }
        .subtitle { color:#333; margin:0 0 12px; }
        .section-title {
            background:#e5e7eb; text-align:center; font-weight:bold; font-size:16px; padding:6px 8px; margin:12px 0 6px;
        }
        table { width:100%; border-collapse: collapse; }
        td, th {  padding:6px 8px; vertical-align:top; }
        th { background:#f2f2f2; text-align:left; }
        .no-border td, .no-border th { border: none; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .bold { font-weight:bold; }
        .muted { color:#666; }
        .pl-12 { padding-left: 12px; }
        .pl-24 { padding-left: 24px; }
        .w55 { width:30%; } .w15 { width:20%; }
        .no-top-border { border-top: none !important; }
        .top-border { border-top: 1px solid #000 !important; }
    </style>
</head>
<body>
    <h1>Laporan Neraca</h1>
    @if(!empty($dateFrom) || !empty($dateTo))
        <div class="subtitle">Periode: {{ $dmy($dateFrom ?? null) ?: '-' }} s/d {{ $dmy($dateTo ?? null) ?: '-' }}</div>
    @endif

    {{-- ===================== AKTIVA ===================== --}}
    <div class="section-title">AKTIVA</div>
    <table>
        <tbody>
            {{-- AKTIVA LANCAR --}}
            <tr><td colspan="4" class="bold">AKTIVA LANCAR</td></tr>

            {{-- KAS DAN BANK --}}
            <tr>
                <td class="bold pl-12">KAS DAN BANK</td>
                <td></td><td></td><td></td>
            </tr>
            @foreach(($report['balanceKasDanBanks'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($kasBank) }}</td>
                <td></td>
            </tr>

            {{-- PIUTANG USAHA --}}
            <tr><td class="bold pl-12">PIUTANG USAHA</td><td></td><td></td><td></td></tr>
            @foreach(($report['balancePiutangUsahas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($piutangUsaha) }}</td>
                <td></td>
            </tr>

            {{-- PIUTANG LAINNYA --}}
            <tr><td class="bold pl-12">PIUTANG LAINNYA</td><td></td><td></td><td></td></tr>
            @foreach(($report['balancePiutangLainnyas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($piutangLain) }}</td>
                <td></td>
            </tr>

            {{-- PERSEDIAAN --}}
            @foreach(($report['balancePersediaans'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- UANG MUKA PEMBELIAN --}}
            @foreach(($report['balanceUangMukaPembelians'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- UANG POTONGAN PEMBELIAN --}}
            @foreach(($report['balancePotonganPembelians'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach


            {{-- UANG POTONGAN PEMBELIAN --}}
            @foreach(($report['balanceInternetDibayarDimukas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- SEWA DIBAYAR DIMUKA --}}
            @foreach(($report['balanceSewaDibayarDimukas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- ASURANSI DIBAYAR DIMUKA --}}
            @foreach(($report['balanceAsuransiDibayarDimukas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- PPN DIBAYAR DIMUKA --}}
            @foreach(($report['balancePPnDibayarDimukas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- KOMISI DIBAYAR DIMUKA --}}
            @foreach(($report['balanceKomisiDibayarDimukas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- SUBTOTAL AKTIVA LANCAR (kolom kanan ketiga sesuai JSX) --}}
            <tr>
                <td></td>
                <td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($totalAktivaLancar) }}</td>
            </tr>

            {{-- AKTIVA TETAP --}}
            <tr><td colspan="4" class="bold">AKTIVA TETAP</td></tr>

            {{-- AT - PERALATAN KANTOR --}}
            <tr><td class="bold pl-12">AT - PERALATAN KANTOR</td><td></td><td></td><td></td></tr>
            @foreach(($report['balanceATPeralatanKantors'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td><td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td><td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($atPeralatan) }}</td><td></td>
            </tr>

            {{-- AT - KENDARAAN --}}
            <tr><td class="bold pl-12">AT - KENDARAAN</td><td></td><td></td><td></td></tr>
            @foreach(($report['balanceATKendaraans'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td><td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td><td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($atKendaraan) }}</td><td></td>
            </tr>

            {{-- AT - BANGUNAN --}}
            <tr><td class="bold pl-12">AT - BANGUNAN</td><td></td><td></td><td></td></tr>
            @foreach(($report['balanceATBangunans'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td><td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td><td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($atBangunan) }}</td><td></td>
            </tr>

            <tr>
                <td></td>
                <td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($totalAktivaTetap) }}</td>
            </tr>

            {{-- ROYALTI TAP / UNASSIGN / PERANTARA (nilai di kolom paling kanan sesuai JSX) --}}
            @foreach(($report['balanceRoyaltiTAPs'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td><td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                </tr>
            @endforeach
            @foreach(($report['balancePerkiraanUnassigns'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td><td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                </tr>
            @endforeach
            @foreach(($report['balancePerkiraanPerantaras'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td><td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                </tr>
            @endforeach

            {{-- TOTAL AKTIVA --}}
            <tr>
                <td></td>
                <td class="bold text-right">TOTAL AKTIVA :</td>
                <td class="bold text-center">IDR</td>
                <td class="bold text-right">{{ $rp($totalAktiva) }}</td>
            </tr>

            {{-- garis pemisah --}}
            <tr class="no-border">
                <td colspan="4" class="no-top-border" style="padding:8px 0;"></td>
            </tr>
        </tbody>
    </table>

    {{-- ===================== KEWAJIBAN ===================== --}}
    <div class="section-title">KEWAJIBAN</div>
    <table>
        <tbody>
            {{-- KEWAJIBAN LANCAR --}}
            <tr><td colspan="4" class="bold">KEWAJIBAN LANCAR</td></tr>
            @foreach(($report['balanceKewajibanLancars'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-12">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- HUTANG PPN --}}
            <tr><td class="bold pl-12">HUTANG PPN</td><td></td><td></td><td></td></tr>
            @foreach(($report['balanceHutangPPns'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-24">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td><td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($hutangPPN) }}</td>
                <td></td>
            </tr>
            
            @foreach(($report['balanceKewajibans'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-12">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- SUBTOTAL KEWAJIBAN (sesuai JSX posisi) --}}
            <tr>
                <td></td><td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($kewajibanLancar + $hutangPPN + $kewajiban) }}</td>
            </tr>

            {{-- KEWAJIBAN JANGKA PANJANG --}}
            <tr><td colspan="4" class="bold">KEWAJIBAN JANGKA PANJANG</td></tr>
            @foreach(($report['balanceKewajibanJangkaPanjangs'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-12">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach
            <tr>
                <td></td><td></td>
                <td class="text-right top-border">&gt;&gt;</td>
                <td class="text-right">{{ $rp($kewajibanJPanjang) }}</td>
            </tr>

            {{-- TOTAL KEWAJIBAN --}}
            <tr>
                <td></td>
                <td class="bold text-right">TOTAL KEWAJIBAN :</td>
                <td class="bold text-center">IDR</td>
                <td class="bold text-right">{{ $rp($totalKewajiban) }}</td>
            </tr>

            <tr class="no-border"><td colspan="4" style="padding:8px 0;"></td></tr>
        </tbody>
    </table>

    {{-- ===================== EKUITAS ===================== --}}
    <div class="section-title">EKUITAS</div>
    <table>
        <tbody>
            @foreach(($report['balanceEkuitas'] ?? []) as $row)
                <tr>
                    <td class="w55 pl-12">{{ data_get($row,'account') }}</td>
                    <td class="w15 text-right"></td>
                    <td class="w15 text-right">{{ $rp(data_get($row,'balance',0)) }}</td>
                    <td class="w15 text-right"></td>
                </tr>
            @endforeach

            {{-- TOTAL EKUITAS --}}
            <tr>
                <td></td>
                <td class="bold text-right">TOTAL EKUITAS :</td>
                <td class="bold text-center">IDR</td>
                <td class="bold text-right">{{ $rp($totalEkuitas) }}</td>
            </tr>

            <tr class="no-border"><td colspan="4" style="padding:8px 0;"></td></tr>

            {{-- TOTAL KEWAJIBAN & EKUITAS --}}
            <tr>
                <td></td>
                <td class="bold text-right">TOTAL KEWAJIBAN &amp; EKUITAS :</td>
                <td class="bold text-center">IDR</td>
                <td class="bold text-right">{{ $rp($totalKewajibanEkuitas) }}</td>
            </tr>

            <tr class="no-border"><td colspan="4" style="padding:8px 0;"></td></tr>

            {{-- BALANCE (Aktiva - (Kewajiban + Ekuitas)) --}}
            <tr>
                <td></td>
                <td class="bold text-right">Balance :</td>
                <td class="bold text-center">IDR</td>
                <td class="bold text-right">{{ $rp($balanceDiff) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
