<?php

namespace App\Services;

use App\Models\Debt;
use App\Models\PayDebtHeader;
use App\Utilities\DateRangeFormatter;
use Illuminate\Http\Request;

class DebtReportService
{
    public function getDebts(Request $request, bool $paginate = true)
    {
        $date = null;
        if ($request->dateType) {
            $date = DateRangeFormatter::format($request->all());
        }

        $query = Debt::with(['vendor'])
            ->when($request->has('status'), function ($query) use ($request) {
                if ($request->status === 'paid') {
                    $query->where('status', Debt::STATUS_PAID);
                } elseif ($request->status === 'unpaid') {
                    $query->where('status', Debt::STATUS_UNPAID);
                }
            })
            ->when($request->has('is_paid'), function ($query) {
                $query->where('status', Debt::STATUS_PAID);
            },)
            ->when($request->pay_debt_header_id != null,  function ($query) use ($request) {
                $payDebtHeader = PayDebtHeader::find($request->pay_debt_header_id);
                if ($payDebtHeader) {
                    $debtIds = $payDebtHeader->payDebts->pluck('debt_id')->toArray();
                    $query->whereIn('id', $debtIds)
                        ->orWhere('status', Debt::STATUS_UNPAID)
                        ->whereNot('remaining_amount', 0);
                } else {
                    $query->where('status', Debt::STATUS_UNPAID)
                        ->whereNot('remaining_amount', 0);
                }
            })
            ->when($request->divisions, function ($query) use ($request) {
                $divisionIds = collect($request->divisions)->pluck('id')->toArray();
                $query->whereIn('division_id', $divisionIds);
            })
            ->when($request->vendor_id, function ($query, $vendorId) {
                $query->where('vendor_id', $vendorId);
            })
            ->with(['purchase', 'purchaseDeposit', 'division'])
            ->when($date, function ($query) use ($date) {
                $query->whereBetween('date', [$date['start_date'], $date['end_date']]);
            })
            ->when($request->sort, function ($query) use ($request) {
                if ($request->sort === 'VENDOR_NAME') {
                    $query->sort('vendor.name', 'asc')
                        ->sort('date', 'asc');
                } elseif ($request->sort === 'DATE') {
                    $query->sort('date', 'asc')
                        ->sort('vendor.name', 'asc');
                }
            });

        $debts = $paginate ? $query->paginate(50) : $query->get();

        if ($request->pay_debt_header_id) {
            return $debts->map(function ($debt) {
                $debt->paid_amount = 0;
                $debt->remaining_amount = $debt->amount;
                return $debt;
            });
        }

        // Jika ada filter tanggal, hitung ulang paid_amount berdasarkan pembayaran sampai tanggal akhir filter
        if ($date && isset($date['end_date']) && !$paginate) {
            $debts->each(function ($debt) use ($date) {
                $debt->paid_amount_filtered = $this->calculatePaidAmountUntilDate($debt->id, $date['end_date']);
                $debt->remaining_amount_filtered = $debt->amount - $debt->paid_amount_filtered;
            });
        } elseif ($date && isset($date['end_date']) && $paginate) {
            $debts->getCollection()->each(function ($debt) use ($date) {
                $debt->paid_amount_filtered = $this->calculatePaidAmountUntilDate($debt->id, $date['end_date']);
                $debt->remaining_amount_filtered = $debt->amount - $debt->paid_amount_filtered;
            });
        }

        return $debts;
    }

    /**
     * Menghitung jumlah yang sudah dibayar untuk hutang tertentu sampai tanggal tertentu
     */
    private function calculatePaidAmountUntilDate($debtId, $untilDate)
    {
        if (!$debtId || !$untilDate) {
            return 0;
        }

        return \App\Models\PayDebt::whereHas('payDebtHeader', function ($query) use ($untilDate) {
            $query->where('date', '<=', $untilDate);
        })
            ->where('debt_id', $debtId)
            ->sum('paid_amount');
    }

    public function getDebtSummary(Request $request)
    {
        $debts = $this->getDebts($request, false);

        // Filter overdue: belum dibayar DAN sudah lewat jatuh tempo
        $overdueDebts = $debts->filter(function ($debt) {
            $remainingAmount = isset($debt->remaining_amount_filtered) ? $debt->remaining_amount_filtered : $debt->remaining_amount;
            $isPaid = $debt->status === 'paid' || $remainingAmount == 0;
            $isPastDue = \Carbon\Carbon::parse($debt->due_date)->isPast();
            return !$isPaid && $isPastDue;
        });

        return [
            'total_debts' => $debts->count(),
            'total_amount' => $debts->sum('amount'),
            'total_paid_amount' => $debts->sum(function ($debt) {
                return isset($debt->paid_amount_filtered) ? $debt->paid_amount_filtered : $debt->paid_amount;
            }),
            'total_remaining_amount' => $debts->sum(function ($debt) {
                return isset($debt->remaining_amount_filtered) ? $debt->remaining_amount_filtered : $debt->remaining_amount;
            }),
            'overdue_count' => $overdueDebts->count(),
            'overdue_amount' => $overdueDebts->sum(function ($debt) {
                return isset($debt->remaining_amount_filtered) ? $debt->remaining_amount_filtered : $debt->remaining_amount;
            }),
        ];
    }

    public function getDebtsByVendor(Request $request)
    {
        $debts = $this->getDebts($request, false);

        return $debts->groupBy('vendor_id')->map(function ($vendorDebts) {
            $vendor = $vendorDebts->first()->vendor;

            // Filter overdue untuk vendor ini
            $overdueDebts = $vendorDebts->filter(function ($debt) {
                $remainingAmount = isset($debt->remaining_amount_filtered) ? $debt->remaining_amount_filtered : $debt->remaining_amount;
                $isPaid = $debt->status === 'paid' || $remainingAmount == 0;
                $isPastDue = \Carbon\Carbon::parse($debt->due_date)->isPast();
                return !$isPaid && $isPastDue;
            });

            return [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->name,
                'total_debts' => $vendorDebts->count(),
                'total_amount' => $vendorDebts->sum('amount'),
                'total_paid_amount' => $vendorDebts->sum(function ($debt) {
                    return isset($debt->paid_amount_filtered) ? $debt->paid_amount_filtered : $debt->paid_amount;
                }),
                'total_remaining_amount' => $vendorDebts->sum(function ($debt) {
                    return isset($debt->remaining_amount_filtered) ? $debt->remaining_amount_filtered : $debt->remaining_amount;
                }),
                'overdue_count' => $overdueDebts->count(),
                'overdue_amount' => $overdueDebts->sum(function ($debt) {
                    return isset($debt->remaining_amount_filtered) ? $debt->remaining_amount_filtered : $debt->remaining_amount;
                }),
                'debts' => $vendorDebts->values()
            ];
        })->values();
    }

    public function getAgingReport(Request $request)
    {
        $debts = $this->getDebts($request, false);
        $today = now();

        $aging = [
            'current' => 0,      // Not due yet
            '1_30_days' => 0,    // 1-30 days overdue
            '31_60_days' => 0,   // 31-60 days overdue
            '61_90_days' => 0,   // 61-90 days overdue
            'over_90_days' => 0, // Over 90 days overdue
        ];

        foreach ($debts as $debt) {
            $dueDate = \Carbon\Carbon::parse($debt->due_date);
            $daysOverdue = $today->diffInDays($dueDate, false); // false means if due_date is past, result is negative
            $remainingAmount = isset($debt->remaining_amount_filtered) ? $debt->remaining_amount_filtered : $debt->remaining_amount;

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

    public function getTopVendors(Request $request, int $limit = 10)
    {
        $vendorDebts = $this->getDebtsByVendor($request);

        return $vendorDebts
            ->sortByDesc('total_remaining_amount')
            ->take($limit)
            ->values();
    }
}
