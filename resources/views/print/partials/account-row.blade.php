{{-- Recursive template for displaying account hierarchy --}}
<tr>
    <td>{{ $account['code'] }}</td>
    <td class="child-account child-account-level-{{ min($level, 4) }}">
        {{ $account['name'] }}
    </td>
    <td style="text-align:right">{{ number_format($account['total'],0,",",".") }}</td>
</tr>

{{-- Recursively display children if they exist --}}
@if(isset($account['children']) && count($account['children']) > 0)
    @foreach ($account['children'] as $child)
        @include('print.partials.account-row', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif
