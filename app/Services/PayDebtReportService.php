<?php

namespace App\Services;

use App\Models\PayDebt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PayDebtReportService
{
    public function applyFilters(Request $request): Builder
    {
        $query = PayDebt::with(['debt.vendor', 'payDebtHeader', 'debt.purchase']);

        // Filter by vendor
        $query->when($request->vendor_id, function ($query) use ($request) {
            $query->whereHas('debt.vendor', function ($query) use ($request) {
                $query->where('id', $request->vendor_id);
            });
        });

        // Filter by division
        $query->when($request->divisions, function ($query) use ($request) {
            $query->whereHas('payDebtHeader', function ($query) use ($request) {
                $query->whereIn('division_id', collect($request->divisions)->pluck('id'));
            });
        });

        // Filter by date type
        $query->when($request->dateType, function ($query) use ($request) {
            switch ($request->dateType) {
                case 'year':
                    if ($request->year) {
                        $query->whereHas('payDebtHeader', function ($query) use ($request) {
                            $query->whereYear('date', $request->year);
                        });
                    }
                    break;
                case 'month':
                    if ($request->monthFrom && $request->monthTo) {
                        $firstDay = date('Y-m-01', strtotime($request->monthFrom));
                        $lastDay = date('Y-m-t', strtotime($request->monthTo));
                        $query->whereHas('payDebtHeader', function ($query) use ($firstDay, $lastDay) {
                            $query->whereBetween('date', [$firstDay, $lastDay]);
                        });
                    }
                    break;
                case 'date':
                    if ($request->date) {
                        $query->whereHas('payDebtHeader', function ($query) use ($request) {
                            $query->whereDate('date', $request->date);
                        });
                    }
                    break;
                case 'range':
                    if ($request->dateFrom && $request->dateTo) {
                        $query->whereHas('payDebtHeader', function ($query) use ($request) {
                            $query->whereBetween('date', [$request->dateFrom, $request->dateTo]);
                        });
                    }
                    break;
                case 'until':
                    if ($request->untilDate) {
                        $query->whereHas('payDebtHeader', function ($query) use ($request) {
                            $query->whereDate('date', '<=', $request->untilDate);
                        });
                    }
                    break;
            }
        });

        return $query->when($request->sort, function ($query) use ($request) {
            if($request->sort == "VENDOR_NAME"){
                $query->sort('payDebtHeader.vendor.name', 'asc')
                    ->sort('payDebtHeader.date', 'asc');
            } else {
                $query->sort('payDebtHeader.date', 'asc')
                    ->sort('payDebtHeader.date', 'asc');
            }   
        });
    }

    public function getFilteredPayDebts(Request $request)
    {
        return $this->applyFilters($request)->get();
    }

    public function getPaginatedPayDebts(Request $request, int $perPage = 50)
    {
        return $this->applyFilters($request)->paginate($perPage);
    }
}