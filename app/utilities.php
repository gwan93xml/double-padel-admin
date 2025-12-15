<?php
function rupiah($angka, $desimal = 0)
{
    $hasil_rupiah = "Rp " . number_format($angka, $desimal, ',', '.');
    return $hasil_rupiah;
}

function number($angka)
{
    $hasilNomor = number_format($angka, 0, ',', '.');
    return $hasilNomor;
}
function terbilang($angka)
{
    $angka = abs($angka);
    $baca = array(
        "",
        "satu",
        "dua",
        "tiga",
        "empat",
        "lima",
        "enam",
        "tujuh",
        "delapan",
        "sembilan",
        "sepuluh",
        "sebelas"
    );
    $terbilang = "";
    if ($angka < 12) {
        $terbilang = " " . $baca[$angka];
    } else if ($angka < 20) {
        $terbilang = terbilang($angka - 10) . " belas";
    } else if ($angka < 100) {
        $terbilang = terbilang($angka / 10) . " puluh" . terbilang($angka % 10);
    } else if ($angka < 200) {
        $terbilang = " seratus" . terbilang($angka - 100);
    } else if ($angka < 1000) {
        $terbilang = terbilang($angka / 100) . " ratus" . terbilang($angka % 100);
    } else if ($angka < 2000) {
        $terbilang = " seribu" . terbilang($angka - 1000);
    } else if ($angka < 1000000) {
        $terbilang = terbilang($angka / 1000) . " ribu" . terbilang($angka % 1000);
    } else if ($angka < 1000000000) {
        $terbilang = terbilang($angka / 1000000) . " juta" . terbilang($angka % 1000000);
    } else if ($angka < 1000000000000) {
        $terbilang = terbilang($angka / 1000000000) . " milyar" . terbilang($angka % 1000000000);
    } else if ($angka < 1000000000000000) {
        $terbilang = terbilang($angka / 1000000000000) . " trilyun"
            . terbilang($angka % 1000000000000);
    }
    return $terbilang ;
}

function romawi($angka)
{
    $romawi = array(
        '01' => 'I',
        '02' => 'II',
        '03' => 'III',
        '04' => 'IV',
        '05' => 'V',
        '06' => 'VI',
        '07' => 'VII',
        '08' => 'VIII',
        '09' => 'IX',
        '10' => 'X',
        '11' => 'XI',
        '12' => 'XII',
    );
    return $romawi[$angka];
}
