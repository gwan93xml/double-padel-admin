<?php

namespace App\Services;

use App\Models\AccountReceivableHeader;
use App\Models\Receivable;
use App\Utilities\DateRangeFormatter;
use Illuminate\Http\Request;

class ReceivableReportService
{
    public function getReceivables(Request $request, bool $paginate = true)
    {
        $date = null;
        if ($request->dateType) {
            $date = DateRangeFormatter::format($request->all());
        }


        $query = Receivable::with(['customer'])
            ->when($request->has('status'), function ($query) use ($request) {
                if ($request->status === 'paid') {
                    $query->where('status', Receivable::STATUS_PAID);
                } elseif ($request->status === 'unpaid') {
                    $query->where('status', Receivable::STATUS_UNPAID);
                }
            })
            ->when($request->has('is_paid'), function ($query) {
                $query->where('status', Receivable::STATUS_PAID);
            })
            ->when($request->account_receivable_header_id != null, function ($query) use ($request) {
                $accountReceivableHeader = AccountReceivableHeader::find($request->account_receivable_header_id);
                if ($accountReceivableHeader) {
                    $receivableIds = $accountReceivableHeader->accountReceivables->pluck('receivable_id')->toArray();
                    $query->whereIn('id', $receivableIds)
                        ->orWhere('status', Receivable::STATUS_UNPAID)
                        ->whereNot('remaining_amount', 0);
                } else {
                    $query->where('status', Receivable::STATUS_UNPAID)
                        ->whereNot('remaining_amount', 0);
                }
            })
            ->when($request->divisions, function ($query) use ($request) {
                $divisionIds = collect($request->divisions)->pluck('id')->toArray();
                $query->whereIn('division_id', $divisionIds);
            })
            ->when($request->customer_id, function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->with(['sale', 'salesDeposit', 'division'])
            ->when($date, function ($query) use ($date) {
                $query->whereBetween('date', [$date['start_date'], $date['end_date']]);
            })
            ->when($request->sort, function ($query) use ($request) {
                if ($request->sort === 'CUSTOMER_NAME') {
                    $query->sort('customer.name', 'asc')
                        ->sort('date', 'asc');
                } elseif ($request->sort === 'DATE') {
                    $query->sort('date', 'asc')
                        ->sort('customer.name', 'asc');
                }
            });

        $receivables = $paginate ? $query->paginate(50) : $query->get();

        if ($request->account_receivable_header_id) {
            return $receivables->map(function ($receivable) {
                $receivable->paid_amount = 0;
                $receivable->remaining_amount = $receivable->amount;
                return $receivable;
            });
        }

        // Jika ada filter tanggal, hitung ulang paid_amount berdasarkan pembayaran sampai tanggal akhir filter
        if ($date && isset($date['end_date']) && !$paginate) {
            $receivables->each(function ($receivable) use ($date) {
                $receivable->paid_amount_filtered = $this->calculatePaidAmountUntilDate($receivable->id, $date['end_date']);
                $receivable->remaining_amount_filtered = $receivable->amount - $receivable->paid_amount_filtered;
            });
        } elseif ($date && isset($date['end_date']) && $paginate) {
            $receivables->getCollection()->each(function ($receivable) use ($date) {
                $receivable->paid_amount_filtered = $this->calculatePaidAmountUntilDate($receivable->id, $date['end_date']);
                $receivable->remaining_amount_filtered = $receivable->amount - $receivable->paid_amount_filtered;
            });
        }

        return $receivables;
    }

    /**
     * Menghitung jumlah yang sudah dibayar untuk piutang tertentu sampai tanggal tertentu
     */
    private function calculatePaidAmountUntilDate($receivableId, $untilDate)
    {
        if (!$receivableId || !$untilDate) {
            return 0;
        }

        return \App\Models\AccountReceivable::whereHas('accountReceivableHeader', function ($query) use ($untilDate) {
            $query->where('date', '<=', $untilDate);
        })
            ->where('receivable_id', $receivableId)
            ->sum('paid_amount');
    }

    public function getReceivableSummary(Request $request)
    {
        $receivables = $this->getReceivables($request, false);

        // Filter overdue: belum dibayar DAN sudah lewat jatuh tempo
        $overdueReceivables = $receivables->filter(function ($receivable) {
            $remainingAmount = isset($receivable->remaining_amount_filtered) ? $receivable->remaining_amount_filtered : $receivable->remaining_amount;
            $isPaid = $receivable->status === 'paid' || $remainingAmount == 0;
            $isPastDue = \Carbon\Carbon::parse($receivable->due_date)->isPast();
            return !$isPaid && $isPastDue;
        });

        return [
            'total_receivables' => $receivables->count(),
            'total_amount' => $receivables->sum('amount'),
            'total_paid_amount' => $receivables->sum(function ($receivable) {
                return isset($receivable->paid_amount_filtered) ? $receivable->paid_amount_filtered : $receivable->paid_amount;
            }),
            'total_remaining_amount' => $receivables->sum(function ($receivable) {
                return isset($receivable->remaining_amount_filtered) ? $receivable->remaining_amount_filtered : $receivable->remaining_amount;
            }),
            'overdue_count' => $overdueReceivables->count(),
            'overdue_amount' => $overdueReceivables->sum(function ($receivable) {
                return isset($receivable->remaining_amount_filtered) ? $receivable->remaining_amount_filtered : $receivable->remaining_amount;
            }),
        ];
    }

    public function getReceivablesByCustomer(Request $request)
    {
        $receivables = $this->getReceivables($request, false);

        return $receivables->groupBy('customer_id')->map(function ($customerReceivables) {
            $customer = $customerReceivables->first()->customer;

            // Filter overdue untuk customer ini
            $overdueReceivables = $customerReceivables->filter(function ($receivable) {
                $remainingAmount = isset($receivable->remaining_amount_filtered) ? $receivable->remaining_amount_filtered : $receivable->remaining_amount;
                $isPaid = $receivable->status === 'paid' || $remainingAmount == 0;
                $isPastDue = \Carbon\Carbon::parse($receivable->due_date)->isPast();
                return !$isPaid && $isPastDue;
            });

            return [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'total_receivables' => $customerReceivables->count(),
                'total_amount' => $customerReceivables->sum('amount'),
                'total_paid_amount' => $customerReceivables->sum(function ($receivable) {
                    return isset($receivable->paid_amount_filtered) ? $receivable->paid_amount_filtered : $receivable->paid_amount;
                }),
                'total_remaining_amount' => $customerReceivables->sum(function ($receivable) {
                    return isset($receivable->remaining_amount_filtered) ? $receivable->remaining_amount_filtered : $receivable->remaining_amount;
                }),
                'overdue_count' => $overdueReceivables->count(),
                'overdue_amount' => $overdueReceivables->sum(function ($receivable) {
                    return isset($receivable->remaining_amount_filtered) ? $receivable->remaining_amount_filtered : $receivable->remaining_amount;
                }),
                'receivables' => $customerReceivables->values()
            ];
        })->values();
    }

    public function getAgingReport(Request $request)
    {
        $receivables = $this->getReceivables($request, false);
        $today = now();

        $aging = [
            'current' => 0,      // Not due yet
            '1_30_days' => 0,    // 1-30 days overdue
            '31_60_days' => 0,   // 31-60 days overdue
            '61_90_days' => 0,   // 61-90 days overdue
            'over_90_days' => 0, // Over 90 days overdue
        ];

        foreach ($receivables as $receivable) {
            $dueDate = \Carbon\Carbon::parse($receivable->due_date);
            $daysOverdue = $today->diffInDays($dueDate, false); // false means if due_date is past, result is negative
            $remainingAmount = isset($receivable->remaining_amount_filtered) ? $receivable->remaining_amount_filtered : $receivable->remaining_amount;

            if ($daysOverdue < 0) {
                // Past due date - calculate absolute days overdue
                $absoluteDaysOverdue = abs($daysOverdue);

                if ($absoluteDaysOverdue <= 30) {
                    $aging['1_30_days'] += $remainingAmount;
                } elseif ($absoluteDaysOverdue <= 60) {
                    $aging['31_60_days'] += $remainingAmount;
                } elseif ($absoluteDaysOverdue <= 90) {
                    $aging['61_90_days'] += $remainingAmount;
                } else {
                    $aging['over_90_days'] += $remainingAmount;
                }
            } else {
                // Not due yet (current)
                $aging['current'] += $remainingAmount;
            }
        }

        return $aging;
    }

    public function getTopCustomers(Request $request, int $limit = 10)
    {
        $customerReceivables = $this->getReceivablesByCustomer($request);

        return $customerReceivables
            ->sortByDesc('total_remaining_amount')
            ->take($limit)
            ->values();
    }
}
