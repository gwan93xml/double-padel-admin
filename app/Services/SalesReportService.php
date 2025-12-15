<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * Generate laporan penjualan.
     *
     * @param  string|null  $monthFrom    Format: YYYY-MM
     * @param  string|null  $monthTo      Format: YYYY-MM
     * @param  int|null     $divisionId
     * @param  int|null     $customerId
     * @param  bool         $export       Jika true: ambil semua data (Collection)
     * @param  bool         $withDetail   Jika true: include relasi 'items'
     * @param  int          $perPage      Jumlah per halaman (paginate)
     * @param  string|null  $paymentStatus Filter by payment status
     * @return LengthAwarePaginator|Collection
     */
    public function generate(
        ?string $monthFrom,
        ?string $monthTo,
        ?array $divisions = null,
        ?int $customerId = null,
        bool $export = false,
        bool $withDetail = false,
        int $perPage = 1000,
        ?string $paymentStatus = null
    ): LengthAwarePaginator|Collection {
        
        // Validate date range
        if ($monthFrom && $monthTo && $monthFrom > $monthTo) {
            throw new \InvalidArgumentException('monthFrom cannot be greater than monthTo');
        }

        $query = Sale::query()
            // filter by periode
            ->when($monthFrom && $monthTo, function ($q) use ($monthFrom, $monthTo) {
                $start = $monthFrom . '-01';
                $end   = Carbon::parse($monthTo)->endOfMonth();
                $q->whereBetween('sale_date', [$start, $end]);
            })
            // filter by divisi
            ->when($divisions, fn($q) => $q->whereIn('division_id', collect($divisions)->pluck('id')->toArray()))
            // filter by customer
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId))
            // filter by payment status
            ->when($paymentStatus, fn($q) => $q->where('payment_status', $paymentStatus))
            // relasi dasar
            ->with(['customer', 'division']);

        // Optimize query for export
        if ($export) {
            $query->select([
                'id', 'customer_id', 'division_id', 'sale_date', 'number', 'no',
                'subtotal', 'discount', 'sales_discount', 'tax', 'total_amount', 
                'payment_status', 'due_date', 'purchase_order_number',
            ]);
        }

        // jika butuh detail items
        if ($withDetail) {
            $query->with('items.item');
        }

        $query->orderBy('sale_date')
              ->orderByRaw('CAST(SUBSTRING_INDEX(no, " ", 1) AS UNSIGNED)')
              ->orderBy('no'); // Secondary sort for same numbers with different suffixes

        // jika export: return semua data
        if ($export) {
            return $query->get();
        }

        // default: paginasi
        return $query->paginate($perPage);
    }

    /**
     * Get sales summary for reporting
     */
    public function getSalesSummary(
        ?string $monthFrom,
        ?string $monthTo,
        ?int $divisionId = null,
        ?int $customerId = null
    ): array {
        $query = Sale::query()
            ->when($monthFrom && $monthTo, function ($q) use ($monthFrom, $monthTo) {
                $start = $monthFrom . '-01';
                $end   = Carbon::parse($monthTo)->endOfMonth();
                $q->whereBetween('sale_date', [$start, $end]);
            })
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId));

        $summary = $query->selectRaw('
            COUNT(*) as total_transactions,
            SUM(subtotal) as total_subtotal,
            SUM(discount + sales_discount) as total_discount,
            SUM(tax) as total_tax,
            SUM(total_amount) as total_amount,
            AVG(total_amount) as average_transaction
        ')->first();

        return [
            'total_transactions' => $summary->total_transactions ?? 0,
            'total_subtotal' => $summary->total_subtotal ?? 0,
            'total_discount' => $summary->total_discount ?? 0,
            'total_tax' => $summary->total_tax ?? 0,
            'total_amount' => $summary->total_amount ?? 0,
            'average_transaction' => $summary->average_transaction ?? 0,
        ];
    }

    /**
     * Get sales by division breakdown
     */
    public function getSalesByDivision(
        ?string $monthFrom,
        ?string $monthTo,
        ?int $customerId = null
    ): Collection {
        $query = Sale::query()
            ->with('division')
            ->when($monthFrom && $monthTo, function ($q) use ($monthFrom, $monthTo) {
                $start = $monthFrom . '-01';
                $end   = Carbon::parse($monthTo)->endOfMonth();
                $q->whereBetween('sale_date', [$start, $end]);
            })
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId));

        return $query->selectRaw('
            division_id,
            COUNT(*) as total_transactions,
            SUM(subtotal) as total_subtotal,
            SUM(discount + sales_discount) as total_discount,
            SUM(tax) as total_tax,
            SUM(total_amount) as total_amount
        ')
        ->groupBy('division_id')
        ->orderBy('total_amount', 'desc')
        ->get();
    }

    /**
     * Get sales by customer breakdown
     */
    public function getSalesByCustomer(
        ?string $monthFrom,
        ?string $monthTo,
        ?int $divisionId = null,
        int $limit = 10
    ): Collection {
        $query = Sale::query()
            ->with('customer')
            ->when($monthFrom && $monthTo, function ($q) use ($monthFrom, $monthTo) {
                $start = $monthFrom . '-01';
                $end   = Carbon::parse($monthTo)->endOfMonth();
                $q->whereBetween('sale_date', [$start, $end]);
            })
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId));

        return $query->selectRaw('
            customer_id,
            COUNT(*) as total_transactions,
            SUM(subtotal) as total_subtotal,
            SUM(discount + sales_discount) as total_discount,
            SUM(tax) as total_tax,
            SUM(total_amount) as total_amount
        ')
        ->groupBy('customer_id')
        ->orderBy('total_amount', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Get monthly sales trend
     */
    public function getMonthlySalesTrend(
        ?string $yearFrom = null,
        ?string $yearTo = null,
        ?int $divisionId = null,
        ?int $customerId = null
    ): Collection {
        $yearFrom = $yearFrom ?? now()->subYear()->year;
        $yearTo = $yearTo ?? now()->year;

        $query = Sale::query()
            ->whereBetween(DB::raw('YEAR(sale_date)'), [$yearFrom, $yearTo])
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
            ->when($customerId, fn($q) => $q->where('customer_id', $customerId));

        return $query->selectRaw('
            YEAR(sale_date) as year,
            MONTH(sale_date) as month,
            COUNT(*) as total_transactions,
            SUM(total_amount) as total_amount
        ')
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
    }
}
