<?php

namespace App\Services;

use App\Models\DefaultChartOfAccount;
use App\Models\Journal;
use App\Models\JournalTransaction;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\ItemIn;
use App\Models\ItemOut;
use App\Models\Chart_ofAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InventoryJournalService
{
    /**
     * Create journal without balance validation (for inventory only)
     */
    protected function createUnbalancedJournal(string $transactionType, int $transactionId, array $header, array $details): Journal
    {
        DB::beginTransaction();
        try {
            $debit = collect($details)->sum('debit');
            $credit = collect($details)->sum('credit');

            $journal = Journal::create([
                'number' => $header['number'] ?? $this->generateJournalNumber(),
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

    /**
     * Generate journal number
     */
    protected function generateJournalNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd');
        $last = Journal::whereDate('created_at', now()->toDateString())
            ->where('transaction_type', 'like', 'Purchase_Inventory%')
            ->count();
        return $prefix . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get Chart of Account ID by name
     */
    protected function coa(string $name): int
    {
        $defaultCoa = DefaultChartOfAccount::where('name', $name)->first();

        if (!$defaultCoa) {
            throw new \Exception("Chart of Account '{$name}' not found in defaults");
        }

        // Return the chart_of_account_id directly from default COA
        return $defaultCoa->chart_of_account_id;
    }

    /**
     * Record inventory journal for purchase transaction (bypass balance validation)
     */
    public function recordPurchaseInventory(Purchase $purchase): Journal
    {
        $details = $this->buildPurchaseInventoryDetails($purchase);

        if (empty($details)) {
            throw new \Exception('No inventory items found for this purchase');
        }

        return $this->createUnbalancedJournal(
            transactionType: 'Purchase_Inventory',
            transactionId: $purchase->id,
            header: [
                'division_id' => $purchase->division_id,
                'date' => $purchase->purchase_date,
                'notes' => "Pencatatan Persediaan Pembelian #{$purchase->no}",
            ],
            details: $details
        );
    }

    /**
     * Record inventory journal for sale transaction (bypass balance validation)
     */
    public function recordSaleInventory(Sale $sale): Journal
    {
        $details = $this->buildSaleInventoryDetails($sale);

        if (empty($details)) {
            throw new \Exception('No inventory items found for this sale');
        }

        return $this->createUnbalancedJournal(
            transactionType: 'Sale_Inventory',
            transactionId: $sale->id,
            header: [
                'division_id' => $sale->division_id,
                'date' => $sale->sale_date,
                'notes' => "Pencatatan Persediaan Penjualan #{$sale->no}",
            ],
            details: $details
        );
    }

    /**
     * Build journal details for purchase inventory
     */
    protected function buildPurchaseInventoryDetails(Purchase $purchase): array
    {
        $details = [];

        foreach ($purchase->items as $item) {
            if (!$item->is_stock || !$item->item_id) {
                continue;
            }

            $itemData = Item::find($item->item_id);
            if (!$itemData) {
                continue;
            }

            // Only debit inventory account (no credit to purchase/HPP)
            $details[] = [
                'chart_of_account_id' => $this->getInventoryAccountId($itemData),
                'transaction_type' => 'debit',
                'debit' => $item->subtotal - $item->discount,
                'credit' => 0,
                'notes' => "Persediaan - {$itemData->name} - Pembelian ID#{$purchase->id} - {$purchase->invoice_number}",
            ];
        }

        return $details;
    }

    /**
     * Build journal details for sale inventory
     */
    protected function buildSaleInventoryDetails(Sale $sale): array
    {
        $details = [];

        foreach ($sale->items as $item) {
            if (!$item->is_stock || !$item->item_id) {
                continue;
            }

            $itemData = Item::find($item->item_id);
            if (!$itemData) {
                continue;
            }

            // Only credit inventory account (no debit to HPP)
            $details[] = [
                'chart_of_account_id' => $this->getInventoryAccountId($itemData),
                'transaction_type' => 'credit',
                'debit' => 0,
                'credit' => $this->calculateCOGS($item, $itemData),
                'notes' => "Persediaan - {$itemData->name} - Penjualan ID#{$sale->id} - {$sale->invoice_number}",
            ];
        }

        return $details;
    }

    /**
     * Get inventory account ID for an item
     */
    protected function getInventoryAccountId(Item $item): int
    {
        // For now, just return the default inventory account
        // This can be enhanced later to support category-specific accounts
        return $this->coa("Persediaan");
    }

    /**
     * Calculate Cost of Goods Sold for an item
     */
    protected function calculateCOGS($saleItem, Item $item): float
    {
        $costPerUnit = $this->getCostPerUnit($item, $saleItem->sale->sale_date ?? now()->toDateString());
        $unit = $item->units()->where('name', $saleItem->unit)->first();
        return $costPerUnit * $saleItem->quantity * ($unit->conversion ?? 1);
    }

    /**
     * Get cost per unit for an item based on purchase history
     * Can be used for any transaction type by providing the transaction date
     * Public method for external use
     */
    public function getItemCostPerUnit(Item $item, string $transactionDate = null): float
    {
        return $this->getCostPerUnit($item, $transactionDate);
    }

    /**
     * Get cost per unit for an item based on purchase history
     * Can be used for any transaction type by providing the transaction date
     */
    protected function getCostPerUnit(Item $item, $transactionDate = null, $unit = null): float
    {
        // Use provided transaction date or current date
        $date = $transactionDate ?? now()->toDateString();

        // Get all purchase items for this item before the transaction date
        $purchaseItems = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchase_items.item_id', $item->id)
            ->where('purchase_items.is_stock', true)
            ->where('purchases.purchase_date', '<=', $date)
            ->whereNotNull('purchase_items.price')
            ->where('purchase_items.price', '>', 0)
            ->select([
                'purchase_items.price',
                'purchase_items.unit',
                'purchase_items.quantity',
                'purchases.purchase_date'
            ])
            ->where('purchase_items.deleted_at', null)
            ->orderBy('purchases.purchase_date', 'desc') // Latest purchases first for more accurate average
            ->limit(50) // Limit to prevent performance issues
            ->get();

        if ($purchaseItems->isEmpty()) {
            $items = Item::where('linked_item_id', $item->id)->get();
            if ($items->isNotEmpty()) {
                // If item has linked items, check their purchase history as well
                foreach ($items as $linkedItem) {
                    $linkedPurchaseItems = DB::table('purchase_items')
                        ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                        ->where('purchase_items.item_id', $linkedItem->id)
                        ->where('purchase_items.is_stock', true)
                        ->where('purchases.purchase_date', '<=', $date)
                        ->whereNotNull('purchase_items.price')
                        ->where('purchase_items.price', '>', 0)
                        ->select([
                            'purchase_items.price',
                            'purchase_items.unit',
                            'purchase_items.quantity',
                            'purchases.purchase_date'
                        ])
                        ->orderBy('purchases.purchase_date', 'desc')
                        ->limit(50)
                        ->get();

                    if ($linkedPurchaseItems->isNotEmpty()) {
                        $purchaseItems = $purchaseItems->merge($linkedPurchaseItems);
                    }
                }
            } else {
                $unitPrice = $item->units()->where('is_purchase_price', true)->first();
                return ($item->purchase_price / $unitPrice->conversion)  ?? 0;
            }
        }

        info("Calculating cost per unit for item {$item->id} as of {$date}, found {$purchaseItems->count()} purchase items");

        // Calculate weighted average cost with unit conversion
        $totalCost = 0;
        $totalWeightedQuantity = 0;
        foreach ($purchaseItems as $purchaseItem) {
            // Get unit conversion
            $conversion = 1; // Default conversion
            if ($purchaseItem->unit) {
                $unitData = $item->units()->where('name', $purchaseItem->unit)->first();
                if ($unitData) {
                    $conversion = $unitData->conversion ?? 1;
                }
            }

            // Convert price to base unit price
            $baseUnitPrice = $purchaseItem->price / $conversion;

            // Calculate weighted quantity (in base units)
            $baseQuantity = $purchaseItem->quantity * $conversion;

            $totalCost += $baseUnitPrice * $baseQuantity;
            $totalWeightedQuantity += $baseQuantity;
        }
        if ($totalWeightedQuantity == 0) {
            $unitPrice = $item->units()->where('is_purchase_price', true)->first();
            return ($item->purchase_price / $unitPrice->conversion)  ?? 0;
        }

        // Calculate average cost per base unit
        $averageCostPerUnit = $totalCost / $totalWeightedQuantity;

        Log::info("Cost per unit calculation for item {$item->id}", [
            'transaction_date' => $date,
            'purchase_items_count' => $purchaseItems->count(),
            'average_cost_per_unit' => $averageCostPerUnit
        ]);

        return $averageCostPerUnit;
    }

    /**
     * Record inventory adjustment journal
     */
    public function recordInventoryAdjustment(array $adjustments, int $divisionId, string $date, ?string $notes = null): Journal
    {
        
        $details = [];

        foreach ($adjustments as $adjustment) {
            $item = Item::find($adjustment['item_id']);
            if (!$item) continue;

            $amount = abs($adjustment['quantity'] * $adjustment['cost_price']);

            if ($adjustment['quantity'] > 0) {
                // Stock increase - Only debit inventory (no credit to adjustment account)
                $details[] = [
                    'chart_of_account_id' => $this->getInventoryAccountId($item),
                    'transaction_type' => 'debit',
                    'debit' => $amount,
                    'credit' => 0,
                    'notes' => "Penyesuaian Persediaan - {$item->name} (+{$adjustment['quantity']})",
                ];
            } else {
                // Stock decrease - Only credit inventory (no debit to adjustment account)
                $details[] = [
                    'chart_of_account_id' => $this->getInventoryAccountId($item),
                    'transaction_type' => 'credit',
                    'debit' => 0,
                    'credit' => $amount,
                    'notes' => "Penyesuaian Persediaan - {$item->name} ({$adjustment['quantity']})",
                ];
            }
        }

        if (empty($details)) {
            throw new \Exception('No valid adjustments found');
        }

        return $this->createUnbalancedJournal(
            transactionType: 'Inventory_Adjustment',
            transactionId: 0, // No specific transaction ID for adjustments
            header: [
                'division_id' => $divisionId,
                'date' => $date,
                'notes' => $notes ?? 'Penyesuaian Persediaan',
            ],
            details: $details
        );
    }

    /**
     * Get inventory balance for reporting
     */
    public function getInventoryBalance(int $divisionId, ?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');

        $query = JournalTransaction::join('journals', 'journal_transactions.journal_id', '=', 'journals.id')
            ->join('chart_of_accounts', 'journal_transactions.chart_of_account_id', '=', 'chart_of_accounts.id')
            ->where('journals.division_id', $divisionId)
            ->where('journals.date', '<=', $date)
            ->where('chart_of_accounts.name', 'like', '%persediaan%')
            ->selectRaw('
                chart_of_accounts.id,
                chart_of_accounts.name,
                SUM(journal_transactions.debit - journal_transactions.credit) as balance
            ')
            ->groupBy('chart_of_accounts.id', 'chart_of_accounts.name')
            ->get();

        return $query->toArray();
    }

    /**
     * Sync inventory journals for existing purchases
     */
    public function syncPurchaseInventoryJournals(): array
    {
        $purchases = Purchase::with('items')
            ->whereDoesntHave('inventoryJournals')
            ->whereHas('items', function ($query) {
                $query->where('is_stock', true);
            })
            ->get();

        $results = [];

        foreach ($purchases as $purchase) {
            try {
                $journal = $this->recordPurchaseInventory($purchase);
                $results[] = [
                    'purchase_id' => $purchase->id,
                    'journal_id' => $journal->id,
                    'status' => 'success'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
            }
        }

        return $results;
    }

    /**
     * Sync inventory journals for existing sales
     */
    public function syncSaleInventoryJournals(): array
    {
        $sales = Sale::with('items')
            ->whereDoesntHave('inventoryJournals')
            ->whereHas('items', function ($query) {
                $query->where('is_stock', true);
            })
            ->get();

        $results = [];

        foreach ($sales as $sale) {
            try {
                $journal = $this->recordSaleInventory($sale);
                $results[] = [
                    'sale_id' => $sale->id,
                    'journal_id' => $journal->id,
                    'status' => 'success'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                    'status' => 'failed'
                ];
            }
        }

        return $results;
    }

    /**
     * Sync inventory journal for a specific purchase (for updates)
     */
    public function syncPurchaseInventoryJournal(Purchase $purchase): ?Journal
    {
        try {
            // Delete existing inventory journal for this purchase
            Journal::where('transaction_type', 'Purchase_Inventory')
                ->where('transaction_id', $purchase->id)
                ->delete();

            // Check if purchase has inventory items
            $hasInventoryItems = $purchase->items->contains(function ($item) {
                return $item->is_stock && $item->item_id;
            });

            if (!$hasInventoryItems) {
                return null; // No inventory items, no journal needed
            }

            // Create new inventory journal
            return $this->recordPurchaseInventory($purchase);
        } catch (\Exception $e) {
            // Log error but don't throw exception
            Log::error("Failed to sync inventory journal for purchase #{$purchase->no}", [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Delete inventory journal for a purchase (for deletion)
     */
    public function deletePurchaseInventory(Purchase $purchase): bool
    {
        try {
            $deletedCount = Journal::where('transaction_type', 'Purchase_Inventory')
                ->where('transaction_id', $purchase->id)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Deleted {$deletedCount} inventory journal(s) for purchase #{$purchase->no}", [
                    'purchase_id' => $purchase->id
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for purchase #{$purchase->no}", [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage()
            ]);

            // Don't throw exception to avoid breaking the main delete flow
            return false;
        }
    }

    /**
     * Delete inventory journal for a sale (for deletion)
     */
    public function deleteSaleInventory(Sale $sale): bool
    {
        try {
            $deletedCount = Journal::where('transaction_type', 'Sale_Inventory')
                ->where('transaction_id', $sale->id)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Deleted {$deletedCount} inventory journal(s) for sale #{$sale->no}", [
                    'sale_id' => $sale->id
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for sale #{$sale->no}", [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);

            // Don't throw exception to avoid breaking the main delete flow
            return false;
        }
    }

    /**
     * Record inventory journal for stock movement
     */
    public function recordStockMovementInventory(StockMovement $stockMovement): Journal
    {
        $details = $this->buildStockMovementInventoryDetails($stockMovement);

        if (empty($details)) {
            throw new \Exception('No inventory items found for this stock movement');
        }

        return $this->createUnbalancedJournal(
            transactionType: 'StockMovement_Inventory',
            transactionId: $stockMovement->id,
            header: [
                'division_id' => $stockMovement->division_id,
                'date' => $stockMovement->date,
                'notes' => "Pencatatan Perpindahan Persediaan #{$stockMovement->number}",
            ],
            details: $details
        );
    }

    /**
     * Build journal details for stock movement inventory
     */
    protected function buildStockMovementInventoryDetails(StockMovement $stockMovement): array
    {
        $details = [];

        foreach ($stockMovement->items as $item) {
            if (!$item->item_id) {
                continue;
            }

            $itemData = Item::find($item->item_id);
            if (!$itemData) {
                continue;
            }

            $conversion = $itemData->units()->where('name', $item->unit)->first()->conversion ?? 1;
            $baseQuantity = $item->quantity * $conversion;
            $costPerUnit = $this->getCostPerUnit($itemData, $stockMovement->date);
            $totalCost = $costPerUnit * $baseQuantity;

            // For stock movement, we need to balance the entries:
            // CREDIT inventory (outgoing warehouse) and DEBIT inventory (incoming warehouse)
            // But since it's the same inventory account, we might need to use different approach

            // For now, we'll just record the movement without affecting inventory balance
            // since stock movement doesn't change total inventory quantity
            $details[] = [
                'chart_of_account_id' => $this->getInventoryAccountId($itemData),
                'transaction_type' => 'debit', // DEBIT for incoming
                'debit' => $totalCost,
                'credit' => 0,
                'notes' => "Perpindahan Persediaan - {$itemData->name} - Dari Gudang {$stockMovement->warehouse_out_id} Ke {$stockMovement->warehouse_in_id} - {$stockMovement->number}",
            ];

            $details[] = [
                'chart_of_account_id' => $this->getInventoryAccountId($itemData),
                'transaction_type' => 'credit', // CREDIT for outgoing
                'debit' => 0,
                'credit' => $totalCost,
                'notes' => "Perpindahan Persediaan - {$itemData->name} - Dari Gudang {$stockMovement->warehouse_out_id} Ke {$stockMovement->warehouse_in_id} - {$stockMovement->number}",
            ];
        }

        return $details;
    }

    /**
     * Record inventory journal for item in (stock addition)
     */
    public function recordItemInInventory(ItemIn $itemIn): Journal
    {
        $details = $this->buildItemInInventoryDetails($itemIn);

        if (empty($details)) {
            throw new \Exception('No inventory items found for this item in');
        }

        return $this->createUnbalancedJournal(
            transactionType: 'ItemIn_Inventory',
            transactionId: $itemIn->id,
            header: [
                'division_id' => $itemIn->division_id,
                'date' => $itemIn->date,
                'notes' => "Pencatatan Barang Masuk #{$itemIn->number}",
            ],
            details: $details
        );
    }

    /**
     * Build journal details for item in inventory
     */
    protected function buildItemInInventoryDetails(ItemIn $itemIn): array
    {
        $details = [];

        foreach ($itemIn->items as $item) {
            if (!$item->item_id) {
                continue;
            }

            $itemData = Item::find($item->item_id);
            if (!$itemData) {
                continue;
            }

            $conversion = $itemData->units()->where('name', $item->unit)->first()->conversion ?? 1;
            $baseQuantity = $item->quantity * $conversion;

            // Use price from ItemInItem if available, otherwise get cost per unit from purchase history
            // Price is already in the correct unit, no need to convert
            $costPerUnit = $item->price ?? $this->getCostPerUnit($itemData, $itemIn->date);
            $totalCost = $costPerUnit * $item->quantity; // Use original quantity, not base quantity

            if ($itemIn->chart_of_account_id) {
                // If chart of account is specified, use Koreksi Stok Masuk account
                $details[] = [
                    'chart_of_account_id' => $this->coa("Koreksi Stok Masuk"),
                    'transaction_type' => 'debit',
                    'debit' => $totalCost,
                    'credit' => 0,
                    'notes' => "Barang Masuk - {$itemData->name} - {$itemIn->number}",
                ];

                // CREDIT the specified chart of account
                $details[] = [
                    'chart_of_account_id' => $itemIn->chart_of_account_id,
                    'transaction_type' => 'credit',
                    'debit' => 0,
                    'credit' => $totalCost,
                    'notes' => "Barang Masuk - {$itemData->name} - {$itemIn->number}",
                ];
            } else {
                // Default behavior: DEBIT inventory account (stock increases)
                $details[] = [
                    'chart_of_account_id' => $this->getInventoryAccountId($itemData),
                    'transaction_type' => 'debit',
                    'debit' => $totalCost,
                    'credit' => 0,
                    'notes' => "Barang Masuk - {$itemData->name} - {$itemIn->number}",
                ];
            }
        }

        return $details;
    }

    /**
     * Record inventory journal for item out (stock reduction)
     */
    public function recordItemOutInventory(ItemOut $itemOut): Journal
    {
        $details = $this->buildItemOutInventoryDetails($itemOut);
        if (empty($details)) {
            throw new \Exception('No inventory items found for this item out');
        }

        return $this->createUnbalancedJournal(
            transactionType: 'ItemOut_Inventory',
            transactionId: $itemOut->id,
            header: [
                'division_id' => $itemOut->division_id,
                'date' => $itemOut->date,
                'notes' => "Pencatatan Barang Keluar #{$itemOut->number}",
            ],
            details: $details
        );
    }

    /**
     * Build journal details for item out inventory
     */
    protected function buildItemOutInventoryDetails(ItemOut $itemOut): array
    {
        $details = [];

        foreach ($itemOut->items as $item) {
            if (!$item->item_id) {
                continue;
            }

            $itemData = Item::find($item->item_id);
            if (!$itemData) {
                continue;
            }

            $conversion = $itemData->units()->where('name', $item->unit)->first()->conversion ?? 1;
            $baseQuantity = $item->quantity * $conversion;

            // Use price from ItemOutItem if available, otherwise get cost per unit from purchase history
            $costPerUnit = $this->getCostPerUnit($itemData, $itemOut->date, $item->unit) ?? $item->price;
            info("Cost per unit for item out item ID {$item->id} is {$costPerUnit}");
            $totalCost = $costPerUnit * $baseQuantity;
            if ($itemOut->chart_of_account_id) {
                // If chart of account is specified, use Koreksi Stok Keluar account
                $details[] = [
                    'chart_of_account_id' => $this->coa("Koreksi Stok Keluar"),
                    'transaction_type' => 'credit',
                    'debit' => 0,
                    'credit' => $totalCost,
                    'notes' => "Barang Keluar - {$itemData->name} - {$itemOut->number}",
                ];

                // DEBIT the specified chart of account
                $details[] = [
                    'chart_of_account_id' => $itemOut->chart_of_account_id,
                    'transaction_type' => 'debit',
                    'debit' => $totalCost,
                    'credit' => 0,
                    'notes' => "Barang Keluar - {$itemData->name} - {$itemOut->number}",
                ];
            } else {
                // Default behavior: CREDIT inventory account (stock decreases)
                $details[] = [
                    'chart_of_account_id' => $this->getInventoryAccountId($itemData),
                    'transaction_type' => 'credit',
                    'debit' => 0,
                    'credit' => $totalCost,
                    'notes' => "Barang Keluar - {$itemData->name} - {$itemOut->number}",
                ];
            }
        }

        return $details;
    }

    /**
     * Delete inventory journal for stock movement (for deletion)
     */
    public function deleteStockMovementInventory(StockMovement $stockMovement): bool
    {
        try {
            $deletedCount = Journal::where('transaction_type', 'StockMovement_Inventory')
                ->where('transaction_id', $stockMovement->id)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Deleted {$deletedCount} inventory journal(s) for stock movement #{$stockMovement->number}", [
                    'stock_movement_id' => $stockMovement->id
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for stock movement #{$stockMovement->number}", [
                'stock_movement_id' => $stockMovement->id,
                'error' => $e->getMessage()
            ]);

            // Don't throw exception to avoid breaking the main delete flow
            return false;
        }
    }

    /**
     * Delete inventory journal for item in (for deletion)
     */
    public function deleteItemInInventory(ItemIn $itemIn): bool
    {
        try {
            $deletedCount = Journal::where('transaction_type', 'ItemIn_Inventory')
                ->where('transaction_id', $itemIn->id)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Deleted {$deletedCount} inventory journal(s) for item in #{$itemIn->number}", [
                    'item_in_id' => $itemIn->id
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for item in #{$itemIn->number}", [
                'item_in_id' => $itemIn->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Delete inventory journal for item out (for deletion)
     */
    public function deleteItemOutInventory(ItemOut $itemOut): bool
    {
        try {
            $deletedCount = Journal::where('transaction_type', 'ItemOut_Inventory')
                ->where('transaction_id', $itemOut->id)
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Deleted {$deletedCount} inventory journal(s) for item out #{$itemOut->number}", [
                    'item_out_id' => $itemOut->id
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for item out #{$itemOut->number}", [
                'item_out_id' => $itemOut->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
