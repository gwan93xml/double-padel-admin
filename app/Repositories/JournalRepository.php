<?php

namespace App\Repositories;

use App\Models\Journal;
use App\Models\User;

class JournalRepository
{
    public static function  store(
        $date,
        $divisionId,
        $transactionId,
        $transactionType,
        $notes,
        $debit,
        $credit,
        $transactions = []
    ) {
        $journal = Journal::create([
            'number' => Journal::generateNumber($date),
            'division_id' => $divisionId,
            'transaction_id' => $transactionId,
            'transaction_type' => $transactionType,
            'date' => $date,
            'notes' => $notes,
            'debit' => $debit,
            'credit' => $credit,
            'entry_by' => auth()->user()->name,
            'entry_at' => now(),
        ]);
        foreach ($transactions as $transaction) {
            $journal->transactions()->create([
                'chart_of_account_id' => $transaction['chart_of_account_id'],
                'debit' => $transaction['debit'] ?? 0,
                'credit' => $transaction['credit'] ?? 0,
                'transaction_ref' => $transaction['transaction_ref'] ?? null,
                'notes' => $transaction['notes'] ?? null,
                'amount' => $transaction['amount'] ?? 0,
            ]);
        }
    }

    public static function  update(
        $id,
        $date,
        $divisionId,
        $transactionId,
        $transactionType,
        $notes,
        $debit,
        $credit,
        $transactions = []
    ) {
        $journal = Journal::findOrFail($id);
        $journal->update([
            'date' => $date,
            'division_id' => $divisionId,
            'transaction_id' => $transactionId,
            'transaction_type' => $transactionType,
            'notes' => $notes,
            'debit' => $debit,
            'credit' => $credit,
            'last_edit_by' => auth()->user()->name,
            'last_edit_at' => now(),
        ]);

        // Clear existing transactions
        $journal->transactions()->delete();

        foreach ($transactions as $transaction) {
            $journal->transactions()->create([
                'chart_of_account_id' => $transaction['chart_of_account_id'],
                'debit' => $transaction['debit'] ?? 0,
                'credit' => $transaction['credit'] ?? 0,
                'transaction_ref' => $transaction['transaction_ref'] ?? null,
                'notes' => $transaction['notes'] ?? null,
                'amount' => $transaction['amount'] ?? 0,
            ]);
        }
    }
}
