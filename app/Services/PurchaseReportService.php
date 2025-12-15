<?php

namespace App\Services;

use App\Models\Purchase;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PurchaseReportService
{
    /**
     * Generate laporan pembelian.
     *
     * @param  string|null  $monthFrom  Format: YYYY-MM
     * @param  string|null  $monthTo    Format: YYYY-MM
     * @param  int|null     $divisionId
     * @param  int|null     $vendorId
     * @param  int          $perPage
     * @return LengthAwarePaginator
     */
    public function generate(
        ?string $monthFrom,
        ?string $monthTo,
        ?array $divisions = null,
        ?int $vendorId = null,
        ?bool $export = false,
        ?bool $withDetail = false,
        int $perPage = 1000
    ): LengthAwarePaginator | Collection {
        $purchases = Purchase::query()
            ->when($monthFrom && $monthTo, function ($query) use ($monthFrom, $monthTo) {
                $startDate = $monthFrom . '-01';
                $endDate = Carbon::parse($monthTo)->endOfMonth();
                $query->whereBetween('purchase_date', [$startDate, $endDate]);
            })
            ->when($divisions, fn($query) => $query->whereIn('division_id', collect($divisions)->pluck('id')->toArray()))
            ->when($vendorId, fn($query) => $query->where('vendor_id', $vendorId))
            ->with(['vendor', 'division'])
            ->orderBy('purchase_date')
            ->orderBy('number');
        if($withDetail) {
            $purchases->with('items');
        }
        // Jika export, gunakan all() untuk mengambil semua data
        if ($export) {
            return $purchases->get();
        }
        // Jika tidak export, gunakan paginate untuk paginasi
        return $purchases
            ->paginate($perPage);
    }
}
