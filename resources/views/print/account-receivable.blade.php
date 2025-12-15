@extends('print.layout')
@section('content')
    <div class="header">
        <h1>Daftar Penerimaan Piutang periode {{$month_query}}</h1>
    </div>
    <table class="items-table">
        <thead>
            <tr>
                <th>NO</th>
                <th>NO. PO</th>
                <th>TANGGAL</th>
                <th>PELANGGAN</th>
                <th>NOTA</th>
                <th>NO. INVOICE</th>
                <th>BAYAR</th>
                <th>DISC</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $previousCustomer = null;
            @endphp
            @foreach($accountReceivables as $key => $item)
            <tr>
                <td>{{$key + 1 }}</td>
                <td>{{ $item->receivable?->sale?->purchase_order_number }}</td>
                <td>{{ Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                <td>
                    @if($previousCustomer !== $item->receivable?->customer?->name)
                        {{ $item->receivable?->customer?->name }}
                        @php
                            $previousCustomer = $item->receivable?->customer?->name;
                        @endphp
                    @else
                        &nbsp;
                    @endif
                </td>
                <td>{{ $item->accountReceivableHeader?->number }}</td>
                <td>{{ $item->receivable?->sale?->no }}</td>
                <td class="text-end">{{rupiah($item->paid_amount)}}</td>
                <td class="text-end">{{rupiah($item->discount)}}</td>
                <td class="text-end">{{rupiah($item->paid_amount + $item->discount)}}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-end">Total</td>
                <td class="text-end">{{rupiah($accountReceivables->sum('paid_amount'))}}</td>
                <td class="text-end">{{rupiah($accountReceivables->sum('discount'))}}</td>
                <td class="text-end">{{rupiah($accountReceivables->sum('paid_amount') + $accountReceivables->sum('discount'))}}</td>
            </tr>
        </tfoot>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th colspan="2">KAS MASUK (GRAND TOTAL)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashInDetails as $item)
            <tr>
                <td>{{$item->chart_ofAccount?->name ?? '' }}</td>
                <td class="text-end">{{rupiah($item->total)}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection
