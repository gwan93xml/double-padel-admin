<?php

namespace App\Services;

use App\Models\AccountReceivable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AccountReceivableReportService
{
    public function applyFilters(Request $request): Builder
    {
        $query = AccountReceivable::with(['receivable.customer', 'accountReceivableHeader', 'receivable.sale']);

        // Filter by customer
        $query->when($request->customer_id, function ($query) use ($request) {
            $query->whereHas('receivable.customer', function ($query) use ($request) {
                $query->where('id', $request->customer_id);
            });
        });

        // Filter by division
        $query->when($request->divisions, function ($query) use ($request) {
            $query->whereHas('accountReceivableHeader', function ($query) use ($request) {
                $query->whereIn('division_id', collect($request->divisions)->pluck('id'));
            });
        })
        ->sort('receivable.customer.name', 'asc')
        ->sort('accountReceivableHeader.date', 'asc');

        // Filter by date type
        $query->when($request->dateType, function ($query) use ($request) {
            switch ($request->dateType) {
                case 'year':
                    if ($request->year) {
                        $query->whereHas('accountReceivableHeader', function ($query) use ($request) {
                            $query->whereYear('date', $request->year);
                        });
                    }
                    break;
                case 'month':
                    if ($request->monthFrom && $request->monthTo) {
                        $firstDay = date('Y-m-01', strtotime($request->monthFrom));
                        $lastDay = date('Y-m-t', strtotime($request->monthTo));
                        $query->whereHas('accountReceivableHeader', function ($query) use ($firstDay, $lastDay) {
                            $query->whereBetween('date', [$firstDay, $lastDay]);
                        });
                    }
                    break;
                case 'date':
                    if ($request->date) {
                        $query->whereHas('accountReceivableHeader', function ($query) use ($request) {
                            $query->whereDate('date', $request->date);
                        });
                    }
                    break;
                case 'range':
                    if ($request->dateFrom && $request->dateTo) {
                        $query->whereHas('accountReceivableHeader', function ($query) use ($request) {
                            $query->whereBetween('date', [$request->dateFrom, $request->dateTo]);
                        });
                    }
                    break;
                case 'until':
                    if ($request->untilDate) {
                        $query->whereHas('accountReceivableHeader', function ($query) use ($request) {
                            $query->whereDate('date', '<=', $request->untilDate);
                        });
                    }
                    break;
            }
        });

        return $query->when($request->sort, function ($query) use ($request) {
            if($request->sort == "CUSTOMER_NAME"){
                $query->sort('accountReceivableHeader.customer.name', 'asc')
                    ->sort('accountReceivableHeader.date', 'asc');
            } else {
                $query->sort('accountReceivableHeader.date', 'asc')
                    ->sort('accountReceivableHeader.customer.name', 'asc');
            }   
        });
    }

    public function getFilteredAccountReceivables(Request $request)
    {
        return $this->applyFilters($request)->get();
    }

    public function getPaginatedAccountReceivables(Request $request, int $perPage = 50)
    {
        return $this->applyFilters($request)->paginate($perPage);
    }
}