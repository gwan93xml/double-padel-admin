@extends('print.layout')
@section('content')
<div class="container" style="font-family: Arial, sans-serif;">
    <div class="header" style="text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px;">
        <h1 style="margin: 0; font-size: 20px; font-weight: bold;">LAPORAN NILAI STOK</h1>
        <p style="margin: 5px 0 0 0; font-size: 12px;">
            Tanggal: {{ date('d/m/Y') }} | 
            Dicetak pada: {{ date('d/m/Y H:i:s') }}
        </p>
    </div>
    <table class="items-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="border-bottom: 2px solid #000;">
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">NO.</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">KODE</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">NAMA BARANG</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">STOK</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">HARGA RATA-RATA</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">TOTAL NILAI</th>
                <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold;">HARGA TERAKHIR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $key => $item)
            <tr style="{{ ($key % 2 == 0) ? 'background-color: #f9f9f9;' : '' }}">
                <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $key + 1 }}</td>
                <td style="border: 1px solid #000; padding: 6px; text-align: center; font-family: monospace;">{{ $item['item']['code'] }}</td>
                <td style="border: 1px solid #000; padding: 6px;">{{ $item['item']['name'] }}</td>
                <td style="border: 1px solid #000; padding: 6px; text-align: right;">{{ $item['jumlah'] }}</td>
                <td style="border: 1px solid #000; padding: 6px; text-align: right; font-family: monospace;">
                    {{ rupiah($item['harga']) }}<br>
                    <small style="font-size: 10px;">per {{ $item['satuan_harga'] }}</small>
                </td>
                <td style="border: 1px solid #000; padding: 6px; text-align: right; font-family: monospace; font-weight: bold;">{{ rupiah($item['total']) }}</td>
                <td style="border: 1px solid #000; padding: 6px; text-align: right; font-family: monospace;">
                    @if($item['harga_terakhir'])
                        {{ rupiah($item['harga_terakhir']) }}<br>
                        <small style="font-size: 10px;">per {{ $item['satuan_harga_terakhir'] }}</small><br>
                        <small style="font-size: 9px; font-style: italic;">{{ $item['tanggal_harga_terakhir'] }}</small>
                    @else
                        <span style="font-style: italic;">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="border-top: 2px solid #000; font-weight: bold;">
                <td colspan="5" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold;">TOTAL KESELURUHAN:</td>
                <td style="border: 1px solid #000; padding: 8px; text-align: right; font-family: monospace; font-weight: bold;">
                    {{ rupiah($items->sum('total')) }}
                </td>
                <td style="border: 1px solid #000; padding: 8px;"></td>
            </tr>
        </tfoot>
    </table>
    
    <div style="margin-top: 30px; font-size: 11px;">
        <p><strong>Keterangan:</strong></p>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li><strong>Harga Rata-rata:</strong> Berdasarkan perhitungan weighted average dari seluruh transaksi</li>
            <li><strong>Harga Terakhir:</strong> Harga dari transaksi pembelian terakhir untuk referensi</li>
            <li><strong>Total Nilai:</strong> Stok × Harga Rata-rata</li>
        </ul>
        <p style="margin-top: 15px; text-align: center; font-size: 10px; font-style: italic;">
            Laporan ini dihasilkan secara otomatis oleh sistem
        </p>
    </div>
</div>
@endsection
