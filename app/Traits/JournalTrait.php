<?php

namespace App\Traits;

use App\Models\Chart_ofAccount;
use App\Models\JournalTransaction;

trait JournalTrait
{
    public function post($accounts)
    {
        foreach ($accounts as $account) {
            JournalTransaction::create([
                'chart_of_account_id' => $account['chart_of_account_id'],
                'date' => $account['date'],
                'description' => $account['description'],
                'debit' => $account['debit'],
                'credit' => $account['credit'],
                'transaction_ref' => $account['transaction_ref'],
            ]);
        }
    }

    public function unpost($transaction_ref)
    {
        JournalTransaction::where('transaction_ref', $transaction_ref)->delete();
    }
}
