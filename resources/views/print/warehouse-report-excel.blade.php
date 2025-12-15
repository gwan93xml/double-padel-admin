<table class="header-table">
    <thead>
        <tr>
            <td colspan="6" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN GUDANG
            </td>
        </tr>
    </thead>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>TANGGAL</th>
            <th>KETERANGAN</th>
            <th>DEBET</th>
            <th>KREDIT</th>
            <th>SALDO</th>
            <th>GUDANG</th>
        </tr>
    </thead>
    <tbody>
        @foreach($itemTransactions as $item)
        <tr>
            <td>{{Carbon\Carbon::parse($item->date)->format('d/m/Y')}}</td>
            <td>{{$item->description}}</td>
            @if($item->transaction_type == 'IN')
            <td class="text-end">{{rupiah($item->total)}}</td>
            <td class="text-end">-</td>
            @else
            <td class="text-end">-</td>
            <td class="text-end">{{rupiah($item->total)}}</td>
            @endif
            <td class="text-end">{{number($item->quantity)}} {{$item->item->units[0]->name}}</td>
            <td>{{$item->warehouse?->name}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
