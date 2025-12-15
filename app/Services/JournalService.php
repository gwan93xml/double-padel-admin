<?php

namespace App\Services;

use App\Models\DefaultChartOfAccount;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;

class JournalService
{
    public function create(string $transactionType, int $transactionId, array $header, array $details): Journal
    {
        DB::beginTransaction();
        try {
            $this->validateBalanced($details);

            $debit = collect($details)->sum('debit');
            $credit = collect($details)->sum('credit');

            $journal = Journal::create([
                'number' => $header['number'] ?? $this->generateNumber(),
                'division_id' => $header['division_id'],
                'transaction_type' => $transactionType,
                'transaction_id' => $transactionId,
                'date' => $header['date'],
                'notes' => $header['notes'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
            ]);

            foreach ($details as $tx) {
                $journal->transactions()->create([
                    'chart_of_account_id' => $tx['chart_of_account_id'],
                    'transaction_type' => $tx['transaction_type'] ?? null,
                    'debit' => $tx['debit'],
                    'credit' => $tx['credit'],
                    'amount' => max($tx['debit'], $tx['credit']),
                    'notes' => $tx['notes'] ?? null,
                ]);
            }

            DB::commit();
            return $journal;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function sync(string $transactionType, int $transactionId, array $header, array $details): void
    {
        DB::beginTransaction();
        try {
            $journal = Journal::where('transaction_type', $transactionType)
                ->where('transaction_id', $transactionId)
                ->with('transactions')
                ->first();

            if (!$journal) {
                $this->create($transactionType, $transactionId, $header, $details);
                DB::commit();
                return;
            }

            $hasChanged = $this->hasChanged($journal, $header, $details);

            if (!$hasChanged) {
                DB::commit();
                return;
            }

            $this->validateBalanced($details);

            $debit = collect($details)->sum('debit');
            $credit = collect($details)->sum('credit');

            $journal->update([
                'number' => $header['number'] ?? $journal->number,
                'division_id' => $header['division_id'],
                'date' => $header['date'],
                'notes' => $header['notes'] ?? null,
                'debit' => $debit,
                'credit' => $credit,
            ]);

            $journal->transactions()->delete();

            foreach ($details as $tx) {
                $journal->transactions()->create([
                    'chart_of_account_id' => $tx['chart_of_account_id'],
                    'transaction_type' => $tx['transaction_type'] ?? null,
                    'debit' => $tx['debit'],
                    'credit' => $tx['credit'],
                    'amount' => max($tx['debit'], $tx['credit']),
                    'notes' => $tx['notes'] ?? null,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $transactionType, int $transactionId): void
    {
        $journal = Journal::where('transaction_type', $transactionType)
            ->where('transaction_id', $transactionId)
            ->first();

        if ($journal) {
            $journal->transactions()->delete();
            $journal->delete();
        }
    }

    public function forceDelete(string $transactionType, int $transactionId): void
    {
        $journal = Journal::where('transaction_type', $transactionType)
            ->withTrashed()
            ->where('transaction_id', $transactionId)
            ->first();

        if ($journal) {
            $journal->transactions()->forceDelete();
            $journal->forceDelete();
        }
    }

    protected function hasChanged(Journal $journal, array $header, array $details): bool
    {
        if (
            $journal->division_id !== $header['division_id'] ||
            $journal->date !== $header['date'] ||
            $journal->notes !== ($header['notes'] ?? null)
        ) {
            return true;
        }

        $existing = $journal->transactions->map(fn($t) => [
            'chart_of_account_id' => $t->chart_of_account_id,
            'debit' => (float) $t->debit,
            'credit' => (float) $t->credit,
        ])->toArray();

        $incoming = collect($details)->map(fn($t) => [
            'chart_of_account_id' => $t['chart_of_account_id'],
            'debit' => (float) $t['debit'],
            'credit' => (float) $t['credit'],
        ])->toArray();

        return $existing !== $incoming;
    }

    protected function validateBalanced(array $details): void
    {
        $debit = collect($details)->sum('debit');
        $credit = collect($details)->sum('credit');

        $diff = abs($debit - $credit);
        if ($diff > 1) {
            if (round($debit, 2) !== round($credit, 2)) {
                throw new \Exception("Jurnal tidak seimbang: debit = $debit, credit = $credit");
            }
            throw new \Exception("Jurnal tidak seimbang: debit = $debit, credit = $credit, selisih = $diff");
        }
    }

    protected function generateNumber(): string
    {
        // Contoh: JRN-20250627-XXXX
        $prefix = 'JRN-' . now()->format('Ymd');
        $last = Journal::whereDate('created_at', now()->toDateString())
            ->count();
        return $prefix . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    public function coa(string $name): int
    {
        $record = DefaultChartOfAccount::where('name', $name)->first();

        if (!$record) {
            throw new \Exception("Default chart of account '{$name}' tidak ditemukan.");
        }

        return $record->chart_of_account_id;
    }

    public function exists(string $transactionType, int $transactionId): bool
    {
        return Journal::where('transaction_type', $transactionType)
            ->where('transaction_id', $transactionId)
            ->exists();
    }
}
