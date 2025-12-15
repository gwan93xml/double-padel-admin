<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemMutation;
use App\Models\ItemTransaction;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Http\Request;

class StockCardService
{
    public function getStockCalculation(Request $request): array
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $itemId = $request->item_id;
        $warehouseId = $request->warehouse_id;

        // Hitung saldo awal
        $startingBalance = $this->calculateStartingBalance($itemId, $warehouseId, $fromDate);

        // Ambil item dengan units
        $item = Item::with('units')->findOrFail($itemId);

        // Ambil transaksi dalam periode
        $itemTransactions = $this->getItemTransactions($itemId, $warehouseId, $fromDate, $toDate);

        // Load customer/vendor data efficiently
        $this->loadCustomerVendorData($itemTransactions);

        // Hitung totals dan format transaksi
        $result = $this->calculateTotalsAndFormatTransactions(
            $itemTransactions,
            $startingBalance,
            $item
        );

        return [
            'transactions' => $result['transactions'],
            'starting_balance' => $result['starting_balance_formatted'],
            'total_in' => $result['total_in_formatted'],
            'total_out' => $result['total_out_formatted'],
            'closing_balance' => $result['closing_balance_formatted'],
        ];
    }

    protected function calculateStartingBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $balanceIn = ItemMutation::where([
            ['item_id', $itemId],
            ['type', 'in'],
            ['date', '<', $fromDate],
        ])
            ->when($warehouseId, fn($query) => $query->where('warehouse_id', $warehouseId))
            ->sum('quantity');

        $balanceOut = ItemMutation::where([
            ['item_id', $itemId],
            ['type', 'OUT'],
            ['date', '<', $fromDate],
        ])
            ->when($warehouseId, fn($query) => $query->where('warehouse_id', $warehouseId))
            ->sum('quantity');

        return $balanceIn - $balanceOut;
    }

    protected function loadCustomerVendorData($itemTransactions)
    {
        // Get all sale IDs
        $saleIds = $itemTransactions->where('reference_type', 'Sale')
            ->pluck('reference_id')
            ->unique()
            ->filter();

        // Get all purchase IDs  
        $purchaseIds = $itemTransactions->where('reference_type', 'Purchase')
            ->pluck('reference_id')
            ->unique()
            ->filter();

        // Load sales with customers
        $sales = [];
        if ($saleIds->isNotEmpty()) {
            $sales = Sale::with('customer')->whereIn('id', $saleIds)->get()->keyBy('id');
        }

        // Load purchases with vendors
        $purchases = [];
        if ($purchaseIds->isNotEmpty()) {
            $purchases = Purchase::with('vendor')->whereIn('id', $purchaseIds)->get()->keyBy('id');
        }

        // Attach customer/vendor to transactions
        foreach ($itemTransactions as $transaction) {
            if ($transaction->reference_type === 'Sale' && isset($sales[$transaction->reference_id])) {
                $sale = $sales[$transaction->reference_id];
                if ($sale->customer) {
                    $transaction->customer = $sale->customer;
                }
            } elseif ($transaction->reference_type === 'Purchase' && isset($purchases[$transaction->reference_id])) {
                $purchase = $purchases[$transaction->reference_id];
                if ($purchase->vendor) {
                    $transaction->vendor = $purchase->vendor;
                }
            }
        }
    }

    protected function getItemTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate)
    {
        return ItemMutation::with(['item', 'warehouse', 'linkingItem','customer', 'vendor'])
            ->where('item_id', $itemId)
            ->when($warehouseId, fn($query) => $query->where('warehouse_id', $warehouseId))
            ->whereBetween('date', [$fromDate, $toDate])
            ->orderBy('date', 'asc')
            ->orderBy('reference_id', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    protected function calculateTotalsAndFormatTransactions($itemTransactions, float $startingBalance, Item $item): array
    {
        $balance = $startingBalance;
        $totalIn = 0;
        $totalOut = 0;

        $unit = $item->unit;
        $unitReport1 = $item->unit_report_1;
        $unitReport2 = $item->unit_report_2;

        foreach ($itemTransactions as $transaction) {
            // Update balance
            $balance += $transaction->type === 'in' ? $transaction->quantity : -$transaction->quantity;

            // Update totals
            if ($transaction->type === 'in') {
                $totalIn += $transaction->quantity;
            } else {
                $totalOut += $transaction->quantity;
            }

            // Format quantity dan balance untuk display
            $transaction->quantity = Item::formatQuantity(
                $transaction->quantity,
                $unit,
                $unitReport1,
                $unitReport2,
                $item->units
            );

            $transaction->balance = Item::formatQuantity(
                $balance,
                $unit,
                $unitReport1,
                $unitReport2,
                $item->units
            );
        }

        $closingBalance = $balance;

        return [
            'transactions' => $itemTransactions,
            'starting_balance_formatted' => Item::formatQuantity($startingBalance, $unit, $unitReport1, $unitReport2, $item->units),
            'total_in_formatted' => Item::formatQuantity($totalIn, $unit, $unitReport1, $unitReport2, $item->units),
            'total_out_formatted' => Item::formatQuantity($totalOut, $unit, $unitReport1, $unitReport2, $item->units),
            'closing_balance_formatted' => Item::formatQuantity($closingBalance, $unit, $unitReport1, $unitReport2, $item->units),
        ];
    }
}
