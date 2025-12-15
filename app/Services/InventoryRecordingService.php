<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\ItemIn;
use App\Models\ItemOut;
use App\Services\InventoryJournalService;
use Illuminate\Support\Facades\Log;

class InventoryRecordingService
{
    public function __construct(
        protected InventoryJournalService $inventoryService
    ) {}

    /**
     * Record inventory for a new purchase
     * Call this after purchase is created and stock is increased
     */
    public function recordPurchaseInventory(Purchase $purchase): bool
    {
        try {
            // Check if purchase has inventory items
            $hasInventoryItems = $purchase->items->contains(function ($item) {
                return $item->is_stock && $item->item_id;
            });

            if (!$hasInventoryItems) {
                Log::info("Purchase #{$purchase->no} has no inventory items, skipping inventory journal");
                return true;
            }

            $journal = $this->inventoryService->recordPurchaseInventory($purchase);

            Log::info("Inventory journal recorded for purchase #{$purchase->no}", [
                'purchase_id' => $purchase->id,
                'journal_id' => $journal->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to record inventory journal for purchase #{$purchase->no}", [
                'purchase_id' => $purchase->id,
                'error' => $e->getMessage()
            ]);

            // Don't throw exception to avoid breaking the main purchase flow
            return false;
        }
    }

    /**
     * Record inventory for a new sale
     * Call this after sale is created and stock is decreased
     */
    public function recordSaleInventory(Sale $sale): bool
    {
        try {
            // Check if sale has inventory items
            $hasInventoryItems = $sale->items->contains(function ($item) {
                return $item->is_stock && $item->item_id;
            });

            if (!$hasInventoryItems) {
                Log::info("Sale #{$sale->no} has no inventory items, skipping inventory journal");
                return true;
            }

            $journal = $this->inventoryService->recordSaleInventory($sale);

            Log::info("Inventory journal recorded for sale #{$sale->no}", [
                'sale_id' => $sale->id,
                'journal_id' => $journal->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to record inventory journal for sale #{$sale->no}", [
                'sale_id' => $sale->id,
                'error' => $e->getMessage()
            ]);

            // Don't throw exception to avoid breaking the main sale flow
            return false;
        }
    }

    /**
     * Record inventory adjustment
     */
    public function recordInventoryAdjustment(array $adjustments, int $divisionId, string $date, ?string $notes = null): bool
    {
        try {
            $journal = $this->inventoryService->recordInventoryAdjustment($adjustments, $divisionId, $date, $notes);

            Log::info("Inventory adjustment journal recorded", [
                'journal_id' => $journal->id,
                'adjustments_count' => count($adjustments)
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to record inventory adjustment", [
                'error' => $e->getMessage(),
                'adjustments' => $adjustments
            ]);

            return false;
        }
    }

    /**
     * Get inventory balance report
     */
    public function getInventoryBalance(int $divisionId, ?string $date ): array
    {
        try {
            return $this->inventoryService->getInventoryBalance($divisionId, $date);
        } catch (\Exception $e) {
            Log::error("Failed to get inventory balance", [
                'division_id' => $divisionId,
                'date' => $date,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Sync all missing inventory journals
     */
    public function syncAllInventoryJournals(): array
    {
        $results = [
            'purchases' => $this->inventoryService->syncPurchaseInventoryJournals(),
            'sales' => $this->inventoryService->syncSaleInventoryJournals()
        ];

        Log::info("Inventory journal sync completed", [
            'purchase_results' => count($results['purchases']),
            'sale_results' => count($results['sales'])
        ]);

        return $results;
    }

    /**
     * Delete inventory journal for a purchase
     * Call this when deleting a purchase
     */
    public function deletePurchaseInventory(Purchase $purchase): bool
    {
        try {
            $result = $this->inventoryService->deletePurchaseInventory($purchase);

            if ($result) {
                Log::info("Inventory journal deleted for purchase #{$purchase->no}", [
                    'purchase_id' => $purchase->id
                ]);
            }

            return $result;

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
     * Delete inventory journal for a sale
     * Call this when deleting a sale
     */
    public function deleteSaleInventory(Sale $sale): bool
    {
        try {
            $result = $this->inventoryService->deleteSaleInventory($sale);

            if ($result) {
                Log::info("Inventory journal deleted for sale #{$sale->no}", [
                    'sale_id' => $sale->id
                ]);
            }

            return $result;

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
     * Record inventory for stock movement
     * Call this after stock movement is created and stock is moved
     */
    public function recordStockMovementInventory(StockMovement $stockMovement): bool
    {
        try {
            $journal = $this->inventoryService->recordStockMovementInventory($stockMovement);

            Log::info("Inventory journal recorded for stock movement #{$stockMovement->number}", [
                'stock_movement_id' => $stockMovement->id,
                'journal_id' => $journal->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to record inventory journal for stock movement #{$stockMovement->number}", [
                'stock_movement_id' => $stockMovement->id,
                'error' => $e->getMessage()
            ]);

            // Don't throw exception to avoid breaking the main stock movement flow
            return false;
        }
    }

    /**
     * Record inventory for item in
     * Call this after item in is created and stock is increased
     */
    public function recordItemInInventory(ItemIn $itemIn): bool
    {
        try {
            $journal = $this->inventoryService->recordItemInInventory($itemIn);

            Log::info("Inventory journal recorded for item in #{$itemIn->number}", [
                'item_in_id' => $itemIn->id,
                'journal_id' => $journal->id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("Failed to record inventory journal for item in #{$itemIn->number}", [
                'item_in_id' => $itemIn->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Record inventory for item out
     * Call this after item out is created and stock is decreased
     */
    public function recordItemOutInventory(ItemOut $itemOut): bool
    {
        try {
            $journal = $this->inventoryService->recordItemOutInventory($itemOut);

            Log::info("Inventory journal recorded for item out #{$itemOut->number}", [
                'item_out_id' => $itemOut->id,
                'journal_id' => $journal->id
            ]);

            return true;

        } catch (\Exception $e) {
            dd($e);
            Log::error("Failed to record inventory journal for item out #{$itemOut->number}", [
                'item_out_id' => $itemOut->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Delete inventory journal for stock movement
     * Call this when deleting a stock movement
     */
    public function deleteStockMovementInventory(StockMovement $stockMovement): bool
    {
        try {
            $result = $this->inventoryService->deleteStockMovementInventory($stockMovement);

            if ($result) {
                Log::info("Inventory journal deleted for stock movement #{$stockMovement->number}", [
                    'stock_movement_id' => $stockMovement->id
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for stock movement #{$stockMovement->number}", [
                'stock_movement_id' => $stockMovement->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Delete inventory journal for item in
     * Call this when deleting an item in
     */
    public function deleteItemInInventory(ItemIn $itemIn): bool
    {
        try {
            $result = $this->inventoryService->deleteItemInInventory($itemIn);

            if ($result) {
                Log::info("Inventory journal deleted for item in #{$itemIn->number}", [
                    'item_in_id' => $itemIn->id
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for item in #{$itemIn->number}", [
                'item_in_id' => $itemIn->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Delete inventory journal for item out
     * Call this when deleting an item out
     */
    public function deleteItemOutInventory(ItemOut $itemOut): bool
    {
        try {
            $result = $this->inventoryService->deleteItemOutInventory($itemOut);

            if ($result) {
                Log::info("Inventory journal deleted for item out #{$itemOut->number}", [
                    'item_out_id' => $itemOut->id
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Failed to delete inventory journal for item out #{$itemOut->number}", [
                'item_out_id' => $itemOut->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
