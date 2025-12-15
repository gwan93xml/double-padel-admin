<table class="header-table" style="width: 100%; margin-bottom: 20px;">
    <thead>
        <tr>
            <td colspan="7" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN STOK BARANG
            </td>
        </tr>
    </thead>
</table>
<table class="items-table" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #f5f5f5;">NO.</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #f5f5f5;">KODE</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #f5f5f5;">NAMA BARANG</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #f5f5f5;">STOK</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #f5f5f5;">HARGA RATA-RATA</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #f5f5f5;">TOTAL NILAI</th>
            <th style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; background-color: #f5f5f5;">HARGA TERAKHIR</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $key => $item)
        <tr>
            <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $key + 1 }}</td>
            <td style="border: 1px solid #000; padding: 6px; text-align: center;">{{ $item['item']['code'] }}</td>
            <td style="border: 1px solid #000; padding: 6px;">{{ $item['item']['name'] }}</td>
            <td style="border: 1px solid #000; padding: 6px; text-align: right;">{{ $item['jumlah'] }}</td>
            <td style="border: 1px solid #000; padding: 6px; text-align: right;">
                {{ rupiah($item['harga']) }} per {{ $item['satuan_harga'] }}
            </td>
            <td style="border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold;">{{ rupiah($item['total']) }}</td>
            <td style="border: 1px solid #000; padding: 6px; text-align: right;">
                @if($item['harga_terakhir'])
                    {{ rupiah($item['harga_terakhir']) }} per {{ $item['satuan_harga_terakhir'] }} ({{ $item['tanggal_harga_terakhir'] }})
                @else
                    -
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; background-color: #f5f5f5;">TOTAL KESELURUHAN:</td>
            <td style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; background-color: #f5f5f5;">
                {{ rupiah($items->sum('total')) }}
            </td>
            <td style="border: 1px solid #000; padding: 8px; background-color: #f5f5f5;"></td>
        </tr>
    </tfoot>
</table>
