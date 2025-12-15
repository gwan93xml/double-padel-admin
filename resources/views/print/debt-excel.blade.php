 <table class="header-table">
     <thead>
         <tr>
             <td colspan="8" style="text-align: center; font-weight: bold; font-size: 16px;">
                 LAPORAN HUTANG
             </td>
         </tr>
     </thead>
 </table>
 <table class="items-table">
     <thead>
         <tr>
             <th>Divisi</th>
             <th>Nomor Invoice</th>
             <th>Nama</th>
             <th>Tanggal</th>
             <th>Jatuh Tempo</th>
             <th>Total Hutang</th>
             <th>Dibayar</th>
             <th>Sisa Hutang</th>
         </tr>
     </thead>
     <tbody>
         @foreach($debts as $item)
         @php
             // Check if vendor name is same as previous item
             $previousItem = $loop->index > 0 ? $debts[$loop->index - 1] : null;
             $vendorName = $item->vendor?->name;
             $showVendorName = !$previousItem || $previousItem->vendor?->name !== $vendorName;
         @endphp
         <tr>
             <td>{{$item->division?->name ?? '-' }}</td>
             <td>{{$item->invoice_number ?? '-' }}</td>
             <td>{{ $showVendorName ? $vendorName : '' }}</td>
             <td>{{Carbon\Carbon::parse($item->date)->format('d/m/Y')}}</td>
             <td>{{$item->due_date ? Carbon\Carbon::parse($item->due_date)->format('d/m/Y') : '-'}}</td>
             <td class="text-end">{{$item->amount}}</td>
             <td class="text-end">{{$item->paid_amount_filtered}}</td>
             <td class="text-end">{{$item->remaining_amount_filtered}}</td>
         </tr>
         @endforeach
     </tbody>
     <tfoot>
            @if($debts->count() > 0)
            <tr>
                <td colspan="5"><strong>TOTAL</strong></td>
                <td class="text-end"><strong>{{ $debts->sum('amount') }}</strong></td>
                <td class="text-end"><strong>{{ $debts->sum('paid_amount_filtered') }}</strong></td>
                <td class="text-end"><strong>{{ $debts->sum('remaining_amount_filtered') }}</strong></td>
            </tr>
            @endif
     </tfoot>
 </table>
