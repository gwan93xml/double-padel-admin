<?php

namespace App\Services;

use App\Models\TaxTransaction;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesTaxService
{
    public function getSalesTaxTransactions(
        string $monthFrom,
        string $monthTo,
        ?int $divisionId = null,
        int $perPage = 50,
        bool $paginate = true,
    ) {
        $startDate = $monthFrom . '-01';
        $endDate = Carbon::parse($monthTo)
            ->endOfMonth()
            ->format('Y-m-d');

        $query = TaxTransaction::query()
            ->where('tax_type', 'sales')
            ->whereHas('sale')
            ->when($divisionId, function ($query) use ($divisionId) {
                $query->whereHas('sale', function ($query) use ($divisionId) {
                    $query->where('division_id', $divisionId);
                });
            })
            ->with(['sale.division'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date');

        return $paginate ? $query->paginate($perPage) : $query->get();
    }
}
