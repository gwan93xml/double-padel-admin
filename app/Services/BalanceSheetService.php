<?php

namespace App\Services;

use App\Models\Chart_ofAccount;
use App\Models\JournalTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BalanceSheetService
{
    private IncomeStatementService $incomeStatementService;

    /**
     * Configuration for balance sheet accounts
     */
    private const ACCOUNT_CONFIGS = [
        // Assets
        'kasDanBank' => ['name' => 'Kas Dan Bank', 'hasParent' => true, 'isDebit' => true],
        'piutangUsaha' => ['name' => 'Piutang Usaha', 'hasParent' => true, 'isDebit' => true],
        'piutangLainnya' => ['name' => 'Piutang Lainnya', 'hasParent' => true, 'isDebit' => true],
        'persediaan' => ['name' => 'Persediaan', 'hasParent' => false, 'isDebit' => true],
        'internetDibayarDimuka' => ['name' => 'Internet Dibayar Dimuka', 'hasParent' => false, 'isDebit' => true],
        'uangMukaPembelian' => ['name' => 'Uang Muka Pembelian', 'hasParent' => false, 'isDebit' => true],
        'sewaDibayarDimuka' => ['name' => 'Sewa Dibayar Dimuka', 'hasParent' => false, 'isDebit' => true],
        'asuransiDibayarDimuka' => ['name' => 'Asuransi Dibayar Dimuka', 'hasParent' => false, 'isDebit' => true],
        'ppnDibayarDimuka' => ['name' => 'PPn Dibayar Dimuka', 'hasParent' => false, 'isDebit' => true],
        'atPeralatanKantor' => ['name' => 'AT - PERALATAN KANTOR', 'hasParent' => true, 'isDebit' => true],
        'atKendaraan' => ['name' => ['AT -KENDARAAN', 'AT - KENDARAAN'], 'hasParent' => true, 'isDebit' => true],
        'atBangunan' => ['name' => 'AT - BANGUNAN', 'hasParent' => true, 'isDebit' => true],
        'royaltiTAP' => ['name' => 'Royalti TAP', 'hasParent' => false, 'isDebit' => true],
        'perkiraanUnassign' => ['name' => 'Perkiraan Unassign', 'hasParent' => false, 'isDebit' => true],
        'perkiraanPerantara' => ['name' => 'Perkiraan Perantara', 'hasParent' => false, 'isDebit' => true],
        'komisiDibayarDimuka' => ['name' => 'Komisi Dibayar Dimuka', 'hasParent' => false, 'isDebit' => true],
        
        // Liabilities
        'kewajibanLancar' => ['name' => 'Kewajiban Lancar', 'hasParent' => true, 'isDebit' => false],
        'hutangPPn' => ['name' => 'Hutang PPN', 'hasParent' => true, 'isDebit' => false],
        'pendapatanDiterimaDimuka' => ['name' => 'Pendapatan Diterima Dimuka', 'hasParent' => false, 'isDebit' => false],
        'kewajibanJangkaPanjang' => ['name' => 'Kewajiban Jangka Panjang', 'hasParent' => true, 'isDebit' => false],
        'kewajiban' => ['name' => 'Kewajiban', 'hasParent' => true, 'isDebit' => false],
        
        // Equity
        'ekuitas' => ['name' => 'Ekuitas', 'hasParent' => true, 'isDebit' => false],
    ];

    public function __construct()
    {
        $this->incomeStatementService = new IncomeStatementService();
    }

    /**
     * Normalize division IDs to ensure proper format
     */
    private function normalizeDivisionIds(array $divisionIds): array
    {
        if (empty($divisionIds)) {
            return [0];
        }
        
        return array_map('intval', $divisionIds);
    }

    /**
     * Build base journal transaction query
     */
    private function buildJournalQuery(int $accountId): \Illuminate\Database\Eloquent\Builder
    {
        return JournalTransaction::where('chart_of_account_id', $accountId)
            ->join('journals', 'journal_transactions.journal_id', '=', 'journals.id')
            ->where('journals.deleted_at', null)
            ->where('journal_transactions.deleted_at', null);
    }
    public function getBalance($accounts, $startDate, $endDate, $isDebit = true)
    {
        $balances = [];
        
        foreach ($accounts as $account) {
            $previousBalance = $this->buildJournalQuery($account->id)
                ->whereDate('journals.date', '<', $startDate)
                ->when($isDebit, 
                    fn($q) => $q->selectRaw('SUM(journal_transactions.debit) - SUM(journal_transactions.credit) as balance'),
                    fn($q) => $q->selectRaw('SUM(journal_transactions.credit) - SUM(journal_transactions.debit) as balance')
                )
                ->value('balance');

            $periodBalance = $this->buildJournalQuery($account->id)
                ->whereBetween('journals.date', [$startDate, $endDate])
                ->when($isDebit, 
                    fn($q) => $q->selectRaw('SUM(journal_transactions.debit) - SUM(journal_transactions.credit) as balance'),
                    fn($q) => $q->selectRaw('SUM(journal_transactions.credit) - SUM(journal_transactions.debit) as balance')
                )
                ->value('balance');

            if ($previousBalance == null && $periodBalance == null) {
                continue;
            }
            
            $balances[] = [
                'account' => $account->name,
                'balance' => ($previousBalance ?? 0) + ($periodBalance ?? 0),
            ];
        }
        
        return $balances;
    }

    /**
     * Calculate earnings from income statement data using standard formula
     * totalSales - totalHPP - totalExpenses + totalOtherRevenues - totalOtherExpenses
     */
    private function calculateEarningsFromAmounts(Collection $amounts): float
    {
        $totalSales = $amounts->where('group', 'sales')->sum(function ($item) {
            return collect($item['divisions'])->sum('amount');
        });
        
        // HPP = COGS components - ending inventory
        $totalCOGSComponents = $amounts->whereIn('group', ['beginningInventories', 'purchases', 'shippingFees', 'stockCorrections'])
            ->sum(function ($item) {
                return collect($item['divisions'])->sum('amount');
            });
        
        $totalEndingInventory = $amounts->where('group', 'endingInventories')->sum(function ($item) {
            return collect($item['divisions'])->sum('amount');
        });
        
        $totalHPP = $totalCOGSComponents - $totalEndingInventory;
        
        $totalExpenses = $amounts->where('group', 'expenses')->sum(function ($item) {
            return collect($item['divisions'])->sum('amount');
        });
        
        $totalOtherRevenues = $amounts->where('group', 'otherRevenues')->sum(function ($item) {
            return collect($item['divisions'])->sum('amount');
        });
        
        $totalOtherExpenses = $amounts->where('group', 'otherExpenses')->sum(function ($item) {
            return collect($item['divisions'])->sum('amount');
        });
        
        return $totalSales - $totalHPP - $totalExpenses + $totalOtherRevenues - $totalOtherExpenses;
    }

    /**
     * Get account data and balance for a specific account configuration
     */
    private function getAccountBalance(string $accountKey, string $startDate, string $endDate): array
    {
        $config = self::ACCOUNT_CONFIGS[$accountKey];
        $accountName = $config['name'];
        $hasParent = $config['hasParent'];
        $isDebit = $config['isDebit'];

        if ($hasParent) {
            if (is_array($accountName)) {
                // Handle multiple possible names (like AT Kendaraan)
                $parentAccount = Chart_ofAccount::where(function($query) use ($accountName) {
                    foreach ($accountName as $name) {
                        $query->orWhere('name', $name);
                    }
                })->first();
            } else {
                $parentAccount = Chart_ofAccount::where('name', $accountName)->first();
            }
            
            if (!$parentAccount) {
                return [];
            }
            
            $accounts = Chart_ofAccount::where('parent_id', $parentAccount->id)->orderBy('code')->get();
        } else {
            $accounts = Chart_ofAccount::where('name', $accountName)->orderBy('code')->get();
        }

        return $this->getBalance($accounts, $startDate, $endDate, $isDebit);
    }

    /**
     * Menghitung laba rugi periode berjalan dari income statement
     * Menggunakan formula yang sama dengan PDF template
     */
    public function calculateCurrentPeriodEarnings($startDate, $endDate, array $divisionIds = [0]): float
    {
        $divisionIds = $this->normalizeDivisionIds($divisionIds);
        
        // Debug: Log divisions being used
        Log::info('BalanceSheet calculateCurrentPeriodEarnings', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'divisionIds' => $divisionIds
        ]);
        
        // Generate income statement untuk periode berjalan (current period)
        $incomeStatement = $this->incomeStatementService->generate($startDate, $endDate, $divisionIds);
        $amounts = $incomeStatement['amounts'];
        
        // Debug: Log raw amounts data
        Log::info('BalanceSheet raw amounts data', [
            'amounts_count' => $amounts->count(),
            'sample_amounts' => $amounts->take(5)->toArray()
        ]);
        
        // Use consolidated calculation method
        $result = $this->calculateEarningsFromAmounts($amounts);
        
        // Debug: Log calculation result
        Log::info('BalanceSheet calculateCurrentPeriodEarnings result', ['result' => $result]);
        
        return $result;
    }

    /**
     * Menghitung laba rugi periode sebelumnya 
     * Menggunakan formula yang sama dengan PDF template
     */
    public function calculatePreviousPeriodEarnings($startDate, $endDate, array $divisionIds = [0]): float
    {
        $divisionIds = $this->normalizeDivisionIds($divisionIds);
        
        $currentStart = Carbon::parse($startDate);
        $currentEnd = Carbon::parse($endDate);
        
        // Cek apakah periode saat ini adalah bulan penuh
        $isFullMonth = $currentStart->day === 1 && $currentEnd->day === $currentEnd->daysInMonth;
        
        if ($isFullMonth) {
            // Jika bulan penuh, ambil bulan sebelumnya
            $previousStart = $currentStart->copy()->subMonth()->startOfMonth();
            $previousEnd = $currentStart->copy()->subMonth()->endOfMonth();
        } else {
            // Jika bukan bulan penuh, ambil periode dengan rentang yang sama
            $periodDays = $currentStart->diffInDays($currentEnd) + 1;
            $previousEnd = $currentStart->copy()->subDay();
            $previousStart = $previousEnd->copy()->subDays($periodDays - 1);
        }
        
        $previousStartDate = $previousStart->format('Y-m-d');
        $previousEndDate = $previousEnd->format('Y-m-d');
        if($previousEnd->lt('2025-01-01')) {
            return 0;
        }
        
        // Generate income statement untuk periode sebelumnya menggunakan method yang sama
        $incomeStatement = $this->incomeStatementService->generate(null, $previousEndDate, $divisionIds);
        $amounts = $incomeStatement['amounts'];
        
        // Use consolidated calculation method
        return $this->calculateEarningsFromAmounts($amounts);
    }

    public function generate(string $startDate, string $endDate, bool $expandChildren = false, array $divisionIds = [0]): array
    {
        // Validasi tanggal
        $this->validateDates($startDate, $endDate);

        // Get all account balances using configuration
        $balances = $this->getAllAccountBalances($startDate, $endDate);
        
        // Calculate earnings
        $previousPeriodEarnings = $this->calculatePreviousPeriodEarnings($startDate, $endDate, $divisionIds);
        $currentPeriodEarnings = $this->calculateCurrentPeriodEarnings($startDate, $endDate, $divisionIds);
        
        // Add earnings to equity if they exist
        if ($previousPeriodEarnings != 0) {
            $balances['balanceEkuitas'][] = [
                'account' => 'Laba (Rugi) Periode Sebelumnya',
                'balance' => $previousPeriodEarnings,
            ];
        }
        
        if ($currentPeriodEarnings != 0) {
            $balances['balanceEkuitas'][] = [
                'account' => 'Laba (Rugi) Periode Berjalan',
                'balance' => $currentPeriodEarnings,
            ];
        }

        // Add earnings to the return array
        $balances['previousPeriodEarnings'] = $previousPeriodEarnings;
        $balances['currentPeriodEarnings'] = $currentPeriodEarnings;

        return $balances;
    }

    /**
     * Get all account balances using the account configuration
     */
    private function getAllAccountBalances(string $startDate, string $endDate): array
    {
        $balances = [];
        
        // Map configuration keys to expected return array keys
        $keyMappings = [
            'kasDanBank' => 'balanceKasDanBanks',
            'piutangUsaha' => 'balancePiutangUsahas',
            'piutangLainnya' => 'balancePiutangLainnyas',
            'persediaan' => 'balancePersediaans',
            'uangMukaPembelian' => 'balanceUangMukaPembelians',
            'internetDibayarDimuka' => 'balanceInternetDibayarDimukas',
            'sewaDibayarDimuka' => 'balanceSewaDibayarDimukas',
            'asuransiDibayarDimuka' => 'balanceAsuransiDibayarDimukas',
            'komisiDibayarDimuka' => 'balanceKomisiDibayarDimukas',
            'ppnDibayarDimuka' => 'balancePPnDibayarDimukas',
            'atPeralatanKantor' => 'balanceATPeralatanKantors',
            'atKendaraan' => 'balanceATKendaraans',
            'atBangunan' => 'balanceATBangunans',
            'royaltiTAP' => 'balanceRoyaltiTAPs',
            'perkiraanUnassign' => 'balancePerkiraanUnassigns',
            'perkiraanPerantara' => 'balancePerkiraanPerantaras',
            'kewajibanLancar' => 'balanceKewajibanLancars',
            'hutangPPn' => 'balanceHutangPPns',
            'pendapatanDiterimaDimuka' => 'balancePendapatanDiterimaDimukas',
            'kewajibanJangkaPanjang' => 'balanceKewajibanJangkaPanjangs',
            'kewajiban' => 'balanceKewajibans',
            'ekuitas' => 'balanceEkuitas',
        ];

        foreach ($keyMappings as $configKey => $returnKey) {
            $balances[$returnKey] = $this->getAccountBalance($configKey, $startDate, $endDate);
        }

        return $balances;
    }



    /**
     * Validasi format tanggal
     */
    private function validateDates(string $startDate, string $endDate): void
    {
        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            if ($start->gt($end)) {
                throw new \InvalidArgumentException('Start date must be before or equal to end date');
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format. Please use YYYY-MM-DD format.');
        }
    }
}
