<?php

namespace App\Utilities;

use App\Models\Chart_ofAccount;
use App\Models\DefaultChartOfAccount;
use App\Models\Journal;
use App\Models\JournalTransaction;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class JournalUtility
{
    /**
     * Create journal entry with transactions (supports unbalanced entries for inventory)
     *
     * @param string $transactionType Transaction type identifier
     * @param int $transactionId Reference transaction ID
     * @param array $header Journal header data
     * @param array $transactions Array of transaction details
     * @param Model|null $reference Reference model (optional)
     * @return Journal|null Returns created journal or null on failure
     */

    public static function create(
        string $transactionType,
        int $transactionId,
        array $header,
        array $mutations
    ): ?Journal {
        $journal = Journal::where('transaction_type', $transactionType)
            ->where('transaction_id', $transactionId)
            ->first();

        if ($journal) {
            $journal->transactions()->forceDelete();
            $journal->forceDelete();
        }
        $journal = Journal::create([
            'transaction_type' => $transactionType,
            'transaction_id' => $transactionId,
            'number' => Journal::generateNumber($header['date'] ?? now()->toDateString()),
            'division_id' => $header['division_id'] ?? null,
            'date' => $header['date'] ?? null,
            'notes' => $header['notes'] ?? null,
        ]);
        foreach ($mutations as $mutation) {
            $amount = $mutation->price * $mutation->quantity;
            $journal->transactions()
                ->create([
                    'transaction_type' => $mutation->type == 'in' ? 'debit' : 'credit',
                    'chart_of_account_id' => self::coa("Persediaan") ?? null,
                    'debit' => $mutation->type == 'in' ? $amount : 0,
                    'credit' => $mutation->type == 'out' ? $amount : 0,
                    'notes' => $mutation->item->name ?? '',
                ]);
        }
        return $journal->load('transactions');
    }

    /**
     * Update existing journal entry (supports unbalanced entries for inventory)
     *
     * @param int $journalId Journal ID to update
     * @param array $header Updated journal header data
     * @param array $transactions Updated transaction details
     * @return Journal|null Returns updated journal or null on failure
     */
    public static function update(
        int $journalId,
        array $header,
        array $transactions
    ): ?Journal {
        try {
            return DB::transaction(function () use ($journalId, $header, $transactions) {
                $journal = Journal::find($journalId);
                if (!$journal) {
                    throw new Exception("Journal not found with ID: {$journalId}");
                }

                // Update journal header
                $journal->update($header);

                // Delete existing transactions
                JournalTransaction::where('journal_id', $journalId)->delete();

                // Create new transactions
                $transactionRecords = self::createJournalTransactions($journalId, $transactions);

                if (empty($transactionRecords)) {
                    throw new Exception("Failed to create journal transactions");
                }

                Log::info("Journal updated successfully", [
                    'journal_id' => $journalId,
                    'total_transactions' => count($transactionRecords)
                ]);

                return $journal->fresh()->load('transactions');
            });
        } catch (Exception $e) {
            Log::error("Failed to update journal entry", [
                'error' => $e->getMessage(),
                'journal_id' => $journalId
            ]);
            return null;
        }
    }

    /**
     * Delete journal entry and all its transactions
     *
     * @param int $journalId Journal ID to delete
     * @return bool Success status
     */
    public static function delete(int $journalId): bool
    {
        try {
            $result = DB::transaction(function () use ($journalId) {
                $journal = Journal::find($journalId);
                if (!$journal) {
                    Log::warning("Journal not found for deletion", ['journal_id' => $journalId]);
                    return false;
                }

                // Delete transactions first
                $transactionCount = JournalTransaction::where('journal_id', $journalId)->count();
                JournalTransaction::where('journal_id', $journalId)->delete();

                // Delete journal
                $journal->delete();

                Log::info("Journal deleted successfully", [
                    'journal_id' => $journalId,
                    'transactions_deleted' => $transactionCount
                ]);

                return true;
            });

            return $result ?? false;
        } catch (Exception $e) {
            Log::error("Failed to delete journal entry", [
                'error' => $e->getMessage(),
                'journal_id' => $journalId
            ]);
            return false;
        }
    }

    /**
     * Delete journal entries by reference
     *
     * @param string $transactionType Transaction type
     * @param int|null $transactionId Transaction ID (if null, deletes all for the type)
     * @return array Number of deleted records
     */
    public static function deleteByReference(string $transactionType, ?int $transactionId = null): array
    {
        try {
            $result = DB::transaction(function () use ($transactionType, $transactionId) {
                $journalQuery = Journal::where('transaction_type', $transactionType);
                if ($transactionId !== null) {
                    $journalQuery->where('transaction_id', $transactionId);
                }

                $journals = $journalQuery->get();
                $journalCount = $journals->count();
                $transactionCount = 0;

                foreach ($journals as $journal) {
                    $transactionCount += JournalTransaction::where('journal_id', $journal->id)->count();
                    JournalTransaction::where('journal_id', $journal->id)->delete();
                }

                $journalQuery->delete();

                Log::info("Journals deleted by reference", [
                    'transaction_type' => $transactionType,
                    'transaction_id' => $transactionId,
                    'journals_deleted' => $journalCount,
                    'transactions_deleted' => $transactionCount
                ]);

                return [
                    'journals_deleted' => $journalCount,
                    'transactions_deleted' => $transactionCount
                ];
            });

            return $result ?? [
                'journals_deleted' => 0,
                'transactions_deleted' => 0
            ];
        } catch (Exception $e) {
            Log::error("Failed to delete journals by reference", [
                'error' => $e->getMessage(),
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId
            ]);
            return [
                'journals_deleted' => 0,
                'transactions_deleted' => 0
            ];
        }
    }

    /**
     * Create journal header record
     *
     * @param string $transactionType Transaction type
     * @param int $transactionId Transaction ID
     * @param array $header Header data
     * @param Model|null $reference Reference model
     * @return Journal|null
     */
    protected static function createJournalHeader(
        string $transactionType,
        int $transactionId,
        array $header,
        ?Model $reference = null
    ): ?Journal {
        try {
            $journalData = array_merge([
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId,
                'number' => Journal::generateNumber($header['date'] ?? now()->toDateString()),
            ], $header);

            // Add reference data if provided
            if ($reference) {
                $journalData['reference_type'] = get_class($reference);
                $journalData['reference_id'] = $reference->id;
            }
            $journals = Journal::where('transaction_type', $transactionType)
                ->where('transaction_id', $transactionId)
                ->get();
            if ($journals->count() > 0) {
                foreach ($journals as $journal) {
                    $journal->transactions()->forceDelete();
                    $journal->forceDelete();
                }
            }
            return Journal::create($journalData);
        } catch (Exception $e) {
            Log::error("Failed to create journal header", [
                'error' => $e->getMessage(),
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId
            ]);
            return null;
        }
    }

    /**
     * Create journal transaction records
     *
     * @param int $journalId Journal ID
     * @param array $transactions Transaction details
     * @return array Created transaction records
     */
    protected static function createJournalTransactions(int $journalId, array $transactions): array
    {
        try {
            $createdTransactions = [];

            foreach ($transactions as $transaction) {

                $journalTransaction = JournalTransaction::create([
                    'journal_id' => $journalId,
                    'transaction_type' => $transaction['debit'] > 0 ? 'debit' : 'credit',
                    'chart_of_account_id' => $transaction['account_id'] ?? null,
                    'debit' => $transaction['debit'] ?? 0,
                ]);
                $createdTransactions[] = $journalTransaction;
            }

            return $createdTransactions;
        } catch (Exception $e) {
            Log::error("Failed to create journal transactions", [
                'error' => $e->getMessage(),
                'journal_id' => $journalId
            ]);
            return [];
        }
    }

    /**
     * Create simple journal entry (helper method for common patterns)
     *
     * @param string $transactionType Transaction type
     * @param int $transactionId Transaction ID  
     * @param int $divisionId Division ID
     * @param string $date Transaction date
     * @param string $notes Journal notes
     * @param array $entries Array of [chart_of_account_id, debit, credit, notes]
     * @param Model|null $reference Reference model
     * @return Journal|null
     */
    public static function createSimple(
        string $transactionType,
        int $transactionId,
        int $divisionId,
        string $date,
        string $notes,
        array $entries,
        ?Model $reference = null
    ): ?Journal {
        $header = [
            'division_id' => $divisionId,
            'date' => $date,
            'notes' => $notes,
        ];

        $transactions = [];
        foreach ($entries as $entry) {
            $transactions[] = [
                'chart_of_account_id' => $entry[0],
                'debit' => $entry[1] ?? 0,
                'credit' => $entry[2] ?? 0,
                'notes' => $entry[3] ?? '',
                'transaction_type' => ($entry[1] ?? 0) > 0 ? 'debit' : 'credit'
            ];
        }

        return self::create($transactionType, $transactionId, $header, $transactions, $reference);
    }

    public static function coa(string $name): ?int
    {
        $defaultChartOfAccount = DefaultChartOfAccount::where('name', $name)->first();
        return $defaultChartOfAccount ? $defaultChartOfAccount->chart_of_account_id : null;
    }
}
