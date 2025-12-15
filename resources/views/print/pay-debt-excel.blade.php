<table class="header-table">
    <thead>
        <tr>
            <td colspan="9" style="text-align: center; font-weight: bold; font-size: 16px;">
                LAPORAN PEMBAYARAN HUTANG
            </td>
        </tr>
    </thead>
</table>
<table class="items-table">
    <thead>
        <tr>
            <th>NO</th>
            <th>TANGGAL</th>
            <th>VENDOR</th>
            <th>NOTA</th>
            <th>INVOICE</th>
            <th>TANGGAL BELI</th>
            <th>BAYAR</th>
            <th>DISC</th>
            <th>TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @php
            $previousVendor = null;
        @endphp
        @foreach($payDebts as $item)
        <tr>
            <td>{{$item->id }}</td>
            <td>{{ Carbon\Carbon::parse($item->payDebtHeader->date)->format('d/m/Y') }}</td>
            <td>
                @if($previousVendor !== $item->debt->vendor?->name)
                    {{ $item->debt->vendor?->name }}
                    @php
                        $previousVendor = $item->debt->vendor?->name;
                    @endphp
                @else
                    &nbsp;
                @endif
            </td>
            <td>{{$item->payDebtHeader?->number}}</td>
            <td>{{$item->debt?->purchase?->invoice_number}}</td>
            <td>{{ $item->debt?->purchase?->purchase_date ? Carbon\Carbon::parse($item->debt->purchase->purchase_date)->format('d/m/Y') : '-' }}</td>
            <td class="text-end">{{$item->paid_amount}}</td>
            <td class="text-end">{{$item->discount}}</td>
            <td class="text-end">{{$item->paid_amount+$item->discount}}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" class="text-end">Total</td>
            <td class="text-end">{{$payDebts->sum('paid_amount')}}</td>
            <td class="text-end">{{$payDebts->sum('discount')}}</td>
            <td class="text-end">{{$payDebts->sum('paid_amount') + $payDebts->sum('discount')}}</td>
        </tr>
    </tfoot>
</table>

<table class="items-table">
    <thead>
        <tr>
            <th colspan="2">KAS KELUAR (GRAND TOTAL)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cashOutDetails as $item)
        <tr>
            <td>{{$item->chart_ofAccount?->name ?? '' }}</td>
            <td class="text-end">{{rupiah($item->total)}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
