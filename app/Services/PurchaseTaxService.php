<?php

namespace App\Services;

use App\Models\TaxTransaction;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseTaxService
{
    public function getPurchaseTaxTransactions(
        string $monthFrom,
        string $monthTo,
        int $divisionId = null,
        int $perPage = 50,
        bool $paginate = true,
    ) {
        $startDate = $monthFrom . '-01';
        $endDate = Carbon::parse($monthTo)
            ->endOfMonth()
            ->format('Y-m-d');

        $query = TaxTransaction::query()
            ->where('tax_type', 'purchase')
            ->whereHas('purchase')
            ->when($divisionId, function ($q) use ($divisionId) {
                $q->whereHas('purchase', function ($q2) use ($divisionId) {
                    $q2->where('division_id', $divisionId);
                });
            })
            ->with(['purchase.division'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date');

        return $paginate ? $query->paginate($perPage) : $query->get();
    }
}
