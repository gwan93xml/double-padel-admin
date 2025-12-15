<table class="header-table">
    <thead>
        <tr>
            <td colspan="{{ 4 + (isset($warehouses) ? count($warehouses) : 0) }}" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN STOCK ITEM
            </td>
        </tr>
    </thead>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th>KODE ITEM</th>
            <th>NAMA ITEM</th>
            <th>STOCK TOTAL</th>
            @if(isset($warehouses))
                @foreach($warehouses as $warehouse)
                <th>{{ $warehouse->name }}</th>
                @endforeach
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($items as $key => $item)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $item->code }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->stock }}</td>
            @if(isset($warehouses))
                @foreach($warehouses as $warehouseIndex => $warehouse)
                <td>
                    @if($item->is_linked)
                        0
                    @else
                        {{ $item->warehouses[$warehouseIndex]->quantity ?? '0' }}
                    @endif
                </td>
                @endforeach
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
