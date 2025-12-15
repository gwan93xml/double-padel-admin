<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CashReportService
{
    /**
     * Generate laporan kas (cash in, cash out, transfer).
     *
     * @param  string|null  $fromDate    Format: YYYY-MM-DD
     * @param  string|null  $toDate      Format: YYYY-MM-DD
     * @param  int|null     $divisionId
     * @param  bool         $export      Jika true, return Collection; else LengthAwarePaginator
     * @param  int          $perPage
     */
    public function generate(
        ?string $fromDate,
        ?string $toDate,
        ?int $divisionId = null,
        ?bool $export = false,
        int $perPage = 50
    ): LengthAwarePaginator|Collection {
        // parse tanggal
        $from = $fromDate
            ? Carbon::parse($fromDate)->startOfDay()
            : null;
        $to = $toDate
            ? Carbon::parse($toDate)->endOfDay()
            : null;

        // cash_in
        $cashIn = DB::table('cash_ins as ci')
            ->join('cash_in_details as cid', 'cid.cash_in_id', 'ci.id')
            ->join('chart_of_accounts as coa', 'coa.id', 'cid.chart_of_account_id')
            ->join('divisions as d', 'd.id', 'ci.division_id')
            ->selectRaw(
                'ci.date              as trx_date,
                 ci.number            as trx_number,
                 ci.division_id       as division_id,
                 cid.chart_of_account_id as coa_id,
                 coa.name             as account_name,
                 d.name               as division_name,
                 cid.description      as description,
                 cid.debit            as debit,
                 cid.credit           as credit,
                 \'cash_in\'          as trx_type'
            )
            ->when($from && $to, fn($q) => $q->whereBetween('ci.date', [$from, $to]))
            ->when($divisionId, fn($q) => $q->where('ci.division_id', $divisionId));

        // cash_out
        $cashOut = DB::table('cash_outs as co')
            ->join('cash_out_details as cod', 'cod.cash_out_id', 'co.id')
            ->join('chart_of_accounts as coa', 'coa.id', 'cod.chart_of_account_id')
            ->join('divisions as d', 'd.id', 'co.division_id')
            ->selectRaw(
                'co.date              as trx_date,
                 co.number            as trx_number,
                 co.division_id       as division_id,
                 cod.chart_of_account_id as coa_id,
                 coa.name             as account_name,
                    d.name               as division_name,
                 cod.description      as description,
                 cod.debit            as debit,
                 cod.credit           as credit,
                 \'cash_out\'         as trx_type'
            )
            ->when($from && $to, fn($q) => $q->whereBetween('co.date', [$from, $to]))
            ->when($divisionId, fn($q) => $q->where('co.division_id', $divisionId))
            ->unionAll($cashIn);

        // cash_transfer out
        $transferOut = DB::table('cash_transfers as ct')
            ->join('chart_of_accounts as coa', 'coa.id', 'ct.from_chart_of_account_id')
            ->join('divisions as d', 'd.id', 'ct.division_id')
            ->selectRaw(
                'ct.date               as trx_date,
                 ct.number             as trx_number,
                 ct.division_id        as division_id,
                 ct.from_chart_of_account_id as coa_id,
                 coa.name             as account_name,
                    d.name               as division_name,
                 ct.note               as description,
                 0                     as debit,
                 ct.amount            as credit,
                 \'cash_transfer_out\' as trx_type'
            )
            ->when($from && $to, fn($q) => $q->whereBetween('ct.date', [$from, $to]))
            ->when($divisionId, fn($q) => $q->where('ct.division_id', $divisionId))
            ->unionAll($cashOut);

        // cash_transfer in
        $transferIn = DB::table('cash_transfers as ct')
            ->join('chart_of_accounts as coa', 'coa.id', 'ct.to_chart_of_account_id')
            ->join('divisions as d', 'd.id', 'ct.division_id')
            ->selectRaw(
                'ct.date               as trx_date,
                 ct.number             as trx_number,
                 ct.division_id        as division_id,
                 ct.to_chart_of_account_id   as coa_id,
                 coa.name              as account_name,
                    d.name                as division_name,
                 ct.note               as description,
                 ct.amount             as debit,
                 0                     as credit,
                 \'cash_transfer_in\'  as trx_type'
            )
            ->when($from && $to, fn($q) => $q->whereBetween('ct.date', [$from, $to]))
            ->when($divisionId, fn($q) => $q->where('ct.division_id', $divisionId))
            ->unionAll($transferOut);

        // wrap union subquery and order
        $fullQuery = DB::query()
            ->fromSub($transferIn, 't')
            ->orderBy('trx_date')
            ->orderBy('trx_number')
            ->orderBy('trx_type');

        if ($export) {
            return $fullQuery->get();
        }

        return $fullQuery->paginate($perPage);
    }
}
