<?php

namespace App\Services;

use App\Models\Chart_ofAccount;
use App\Models\Division;
use App\Models\JournalTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IncomeStatementService
{
    public $amounts;

    public function __construct()
    {
        $this->amounts = collect([]);
    }

    /**
     * Normalize division IDs parameter
     */
    private function normalizeDivisionIds($divisionsId): array
    {
        if ($divisionsId === null || $divisionsId === '') {
            return [0];
        } elseif (is_array($divisionsId)) {
            return array_unique(array_map('intval', $divisionsId));
        } else {
            return [intval($divisionsId)];
        }
    }

    /**
     * Get account name for display (handle special inventory cases)
     */
    private function getDisplayAccountName(string $group, string $originalName): string
    {
        if ($group === "beginningInventories") {
            return "Persediaan Awal";
        } elseif ($group === "endingInventories") {
            return "Persediaan Akhir";
        }
        
        return $originalName;
    }

    /**
     * Build journal transaction query with common conditions
     */
    private function buildJournalQuery(int $accountId, ?string $startDate, ?string $endDate, int $divisionId, bool $isDebit): \Illuminate\Database\Eloquent\Builder
    {
        return JournalTransaction::query()
            ->where('chart_of_account_id', $accountId)
            ->join('journals', 'journal_transactions.journal_id', '=', 'journals.id')
            ->when($startDate !== null && $endDate !== null, 
                fn($q) => $q->whereBetween('journals.date', [$startDate, $endDate]))
            ->when($startDate !== null && $endDate === null, 
                fn($q) => $q->whereDate('journals.date', '<', $startDate))
            ->when($startDate === null && $endDate !== null, 
                fn($q) => $q->whereDate('journals.date', '<=', $endDate))
            ->when($isDebit, 
                fn($q) => $q->selectRaw('SUM(journal_transactions.debit) - SUM(journal_transactions.credit) as amount'),
                fn($q) => $q->selectRaw('SUM(journal_transactions.credit) - SUM(journal_transactions.debit) as amount'))
            ->when($divisionId !== 0, fn($q) => $q->where('journals.division_id', $divisionId))
            ->where('journals.deleted_at', null)
            ->where('journal_transactions.deleted_at', null);
    }

    /**
     * Find existing account in amounts collection
     */
    private function findExistingAccount(string $accountName): ?array
    {
        return $this->amounts->firstWhere('account_name', $accountName);
    }

    /**
     * Update existing division amount in amounts collection
     */
    private function updateExistingDivision(string $accountName, int $divisionId, float $newAmount): void
    {
        $this->amounts = $this->amounts->map(function ($item) use ($accountName, $divisionId, $newAmount) {
            if ($item['account_name'] === $accountName) {
                $item['divisions'] = collect($item['divisions'])->map(function ($division) use ($divisionId, $newAmount) {
                    if ($division['division_id'] === $divisionId) {
                        $division['amount'] = $newAmount;
                    }
                    return $division;
                })->toArray();
            }
            return $item;
        });
    }

    /**
     * Add new division to existing account in amounts collection
     */
    private function addDivisionToExistingAccount(string $accountName, int $divisionId, ?float $amount): void
    {
        $this->amounts = $this->amounts->map(function ($item) use ($accountName, $divisionId, $amount) {
            if ($item['account_name'] === $accountName) {
                $item['divisions'][] = [
                    'division_id' => $divisionId,
                    'amount' => $amount
                ];
            }
            return $item;
        });
    }

    /**
     * Add new account to amounts collection
     */
    private function addNewAccount(string $group, string $accountName, int $divisionId, ?float $amount): void
    {
        $this->amounts->push([
            'group' => $group,
            'account_name' => $accountName,
            'divisions' => [
                [
                    'division_id' => $divisionId,
                    'amount' => $amount
                ]
            ]
        ]);
    }

    /**
     * Update amounts collection with new account/division data
     */
    private function updateAmountsCollection(string $group, string $accountName, int $divisionId, ?float $amount): void
    {
        $existingAccount = $this->findExistingAccount($accountName);
        
        if ($existingAccount) {
            $existingDivisions = collect($existingAccount['divisions']);
            $existingDivisionIndex = $existingDivisions->search(function ($division) use ($divisionId) {
                return $division['division_id'] == $divisionId;
            });
            
            if ($existingDivisionIndex !== false) {
                // Division exists, update the amount (sum it)
                $existingDivision = $existingDivisions[$existingDivisionIndex];
                $newAmount = ($existingDivision['amount'] ?? 0) + ($amount ?? 0);
                $this->updateExistingDivision($accountName, $divisionId, $newAmount);
            } else {
                // Division doesn't exist, add it
                $this->addDivisionToExistingAccount($accountName, $divisionId, $amount);
            }
        } else {
            // Account doesn't exist, create new entry
            $this->addNewAccount($group, $accountName, $divisionId, $amount);
        }
    }

    public function getAmount($group, $accounts, $startDate, $endDate, $divisionId, $isDebit = true)
    {
        $amounts = [];
        
        foreach ($accounts as $account) {
            $accountName = $this->getDisplayAccountName($group, $account->name);
            
            $periodAmount = $this->buildJournalQuery($account->id, $startDate, $endDate, $divisionId, $isDebit)
                ->value('amount');
            
            // Skip if no amount and not inventory account
            if ($periodAmount === null && !in_array($accountName, ["Persediaan Awal", "Persediaan Akhir"])) {
                continue;
            }
            
            // Update amounts collection
            $this->updateAmountsCollection($group, $accountName, $divisionId, $periodAmount);
            
            // Add to return array
            $amounts[] = [
                'division_id' => $divisionId,
                'account' => $account->name,
                'amount' => ($periodAmount ?? 0),
            ];
        }
        
        return $amounts;
    }

    /**
     * Get revenue accounts
     */
    private function getRevenueAccounts()
    {
        return Chart_ofAccount::where('type', 'revenue')->whereDoesntHave('children')->get();
    }

    /**
     * Get inventory accounts
     */
    private function getInventoryAccounts()
    {
        return Chart_ofAccount::where('name', 'Persediaan')->orderBy('code')->get();
    }

    /**
     * Get purchase accounts
     */
    private function getPurchaseAccounts()
    {
        return Chart_ofAccount::where('name', 'Pembelian')->orderBy('code')->get();
    }

    /**
     * Get purchase accounts
     */
    private function getPurchaseDiscountAccounts()
    {
        return Chart_ofAccount::where('name', 'Potongan Pembelian')->orderBy('code')->get();
    }

    /**
     * Get shipping fee accounts
     */
    private function getShippingFeeAccounts()
    {
        return Chart_ofAccount::where('name', 'Biaya Angkut Pembelian')->orderBy('code')->get();
    }

    /**
     * Get purchase return accounts
     */
    private function getPurchaseReturnAccounts()
    {
        return Chart_ofAccount::where('name', 'Retur Pembelian')->orderBy('code')->get();
    }

    /**
     * Get stock correction accounts
     */
    private function getStockCorrectionAccounts()
    {
        return Chart_ofAccount::query()->where('name', 'LIKE', '%Koreksi%')->get();
    }

    /**
     * Get expense accounts
     */
    private function getExpenseAccounts()
    {
        return Chart_ofAccount::whereHas('parent', fn($q) => $q->where('name', 'BIAYA/BEBAN'))
            ->whereDoesntHave('children')->get();
    }

    /**
     * Get other expense accounts
     */
    private function getOtherExpenseAccounts()
    {
        return Chart_ofAccount::whereHas('parent', fn($q) => $q->where('name', 'BIAYA/BEBAN LAINNYA'))
            ->whereDoesntHave('children')->get();
    }

    /**
     * Get other revenue accounts
     */
    private function getOtherRevenueAccounts()
    {
        return Chart_ofAccount::whereHas('parent', fn($q) => $q->where('name', 'PENDAPATAN LAINNYA'))
            ->whereDoesntHave('children')->get();
    }

    /**
     * Process accounts for a single division
     */
    private function processAccountsForDivision(int $divisionId, string $startDate, string $endDate): void
    {   
        $this->getAmount('sales', $this->getRevenueAccounts(), $startDate, $endDate, $divisionId, false);
        $this->getAmount('beginningInventories', $this->getInventoryAccounts(), $startDate, null, $divisionId, true);
        $this->getAmount('purchases', $this->getPurchaseAccounts(), $startDate, $endDate, $divisionId, true);
        $this->getAmount('purchaseDiscounts', $this->getPurchaseDiscountAccounts(), $startDate, $endDate, $divisionId, false);
        $this->getAmount('shippingFees', $this->getShippingFeeAccounts(), $startDate, $endDate, $divisionId, true);
        $this->getAmount('purchaseReturns', $this->getPurchaseReturnAccounts(), $startDate, $endDate, $divisionId, false);
        $this->getAmount('stockCorrections', $this->getStockCorrectionAccounts(), $startDate, $endDate, $divisionId, true);
        $this->getAmount('endingInventories', $this->getInventoryAccounts(), null, $endDate, $divisionId, true);
        $this->getAmount('expenses', $this->getExpenseAccounts(), $startDate, $endDate, $divisionId, true);
        $this->getAmount('otherExpenses', $this->getOtherExpenseAccounts(), $startDate, $endDate, $divisionId, true);
        $this->getAmount('otherRevenues', $this->getOtherRevenueAccounts(), $startDate, $endDate, $divisionId, false);
    }

    /**
     * Calculate and log income statement totals for debugging
     */
    private function calculateAndLogTotals(): float
    {
        $amounts = $this->amounts;
        
        $totalSales = $amounts->where('group', 'sales')->sum(function ($item) {
            return collect($item['divisions'])->sum('amount');
        });
        
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
        
        $incomeStatementResult = $totalSales - $totalHPP - $totalExpenses + $totalOtherRevenues - $totalOtherExpenses;
        
        Log::info('IncomeStatementService calculation results', [
            'amounts_count' => $amounts->count(),
            'totalSales' => $totalSales,
            'totalCOGSComponents' => $totalCOGSComponents,
            'totalEndingInventory' => $totalEndingInventory,
            'totalHPP' => $totalHPP,
            'totalExpenses' => $totalExpenses,
            'totalOtherRevenues' => $totalOtherRevenues,
            'totalOtherExpenses' => $totalOtherExpenses,
            'incomeStatementResult' => $incomeStatementResult
        ]);
        
        return $incomeStatementResult;
    }

    /**
     * Calculate default start date based on end date
     * Default behavior: if endDate is provided, use the beginning of that month as startDate
     * 
     * @param string $endDate The end date
     * @param string $defaultPeriod The default period type ('month', 'year', 'quarter')
     * @return string The calculated start date
     */
    private function calculateDefaultStartDate(string $endDate, string $defaultPeriod = 'month'): string
    {
       return Carbon::parse($endDate)->startOf('year')->format('Y-m-d');
    }

    public function generate($startDate, $endDate, $divisionsId)
    {

        // Reset amounts collection to avoid stale data
        $this->amounts = collect([]);
        
        // Handle case when only endDate is provided
        if (empty($startDate) && !empty($endDate)) {
            $startDate = $this->calculateDefaultStartDate($endDate);
        }

        
        // Validate that we have both dates
        if (empty($startDate) || empty($endDate)) {
            throw new \InvalidArgumentException('Both startDate and endDate must be provided or endDate for default period calculation');
        }
        
        // Normalize divisions parameter
        $divisionsId = $this->normalizeDivisionIds($divisionsId);
        
        // Debug: Log parameters received
        Log::info('IncomeStatementService generate', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'divisionsId' => $divisionsId,
            'divisionsId_type' => gettype($divisionsId),
            'divisionsId_count' => count($divisionsId)
        ]);
        
        // Prepare divisions data
        $divisions = $this->prepareDivisionsData($divisionsId);
        
        // Process accounts for each division
        Log::info('IncomeStatementService before loop', [
            'divisionsId' => $divisionsId,
            'divisionsId_unique' => array_unique($divisionsId)
        ]);
        
        foreach ($divisionsId as $divisionId) {
            $this->processAccountsForDivision($divisionId, $startDate, $endDate);
        }
        
        // Calculate totals and log for debugging
        $this->calculateAndLogTotals();
        
        return [
            'amounts' => $this->amounts,
            'divisions' => $divisions,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }

    /**
     * Prepare divisions data for response
     */
    private function prepareDivisionsData(array $divisionsId): array
    {
        $divisions = Division::whereIn('id', $divisionsId)
            ->orderBy('order', 'asc')
            ->get()
            ->map(fn($d) => ['id' => $d->id, 'name' => $d->name])
            ->toArray();
        
        if (in_array(0, $divisionsId)) {
            $divisions = [['id' => 0, 'name' => '--'], ...$divisions];
        }
        
        return $divisions;
    }
}
