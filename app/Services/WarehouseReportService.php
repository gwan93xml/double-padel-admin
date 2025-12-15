<?php

namespace App\Services;

use App\Models\ItemTransaction;
use Illuminate\Support\Facades\DB;

class WarehouseReportService
{
    public function getItemTransactions(?string $monthFrom = null, ?string $monthTo = null, ?string $search = null, int $perPage = 15, bool $paginate = false)
    {
        $query = ItemTransaction::query()
            ->with(['item.units', 'warehouse']);

        // Filter by date range if provided
        if ($monthFrom && $monthTo) {
            $query->whereBetween(DB::raw('DATE_FORMAT(date, "%Y-%m")'), [$monthFrom, $monthTo]);
        }

        // Filter by search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', '%' . $search . '%')
                             ->orWhere('code', 'like', '%' . $search . '%');
                })
                ->orWhereHas('warehouse', function ($warehouseQuery) use ($search) {
                    $warehouseQuery->where('name', 'like', '%' . $search . '%')
                                  ->orWhere('code', 'like', '%' . $search . '%');
                });
            });
        }

        // Order by created_at descending
        $query->orderBy('date', 'asc');

        if ($paginate) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function getTransactionSummary(?string $monthFrom = null, ?string $monthTo = null)
    {
        $query = ItemTransaction::query();

        if ($monthFrom && $monthTo) {
            $query->whereBetween(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), [$monthFrom, $monthTo]);
        }

        return [
            'total_transactions' => $query->count(),
            'total_in' => $query->where('type', 'in')->sum('quantity'),
            'total_out' => $query->where('type', 'out')->sum('quantity'),
        ];
    }
}
