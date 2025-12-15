<?php

namespace App\Utilities;

use App\Models\Item;
use App\Models\ItemMutation;
use App\Models\Stock;
use App\Models\DefaultChartOfAccount;
use App\DTOs\BulkMutationResult;
use App\DTOs\BulkMutationSummary;
use App\DTOs\ItemMutationResult;
use App\Models\Chart_ofAccount;
use App\Models\Journal;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemMutationUtility
{

    protected static $referenceTypes = [
        'App\Models\Purchase' => 'Purchase_Inventory',
        'App\Models\Production' => 'Production_Inventory',
        'App\Models\Sale' => 'Sales_Inventory',
        'App\Models\ItemIn' => 'ItemIn_Inventory',
        'App\Models\ItemOut' => 'ItemOut_Inventory',
        'App\Models\StockMovement' => 'StockMovement_Inventory',
        'App\Models\PurchaseReturn' => 'PurchaseReturn_Inventory',
        'App\Models\SaleReturn' => 'SaleReturn_Inventory',
        'App\Models\StockMovementIn' => 'StockMovementIn_Inventory',
        'App\Models\StockMovementOut' => 'StockMovementOut_Inventory',
        'App\Models\ProductionIn' => 'ProductionIn_Inventory',
        'App\Models\ProductionOut' => 'ProductionOut_Inventory',
    ];


    /**
     * Increase item stock (IN mutation)
     *
     * @param int $itemId Item ID
     * @param float $quantity Quantity to increase
     * @param string $unit Unit name
     * @param float $price Price per unit
     * @param string $date Date of mutation
     * @param Model $reference Reference model (Purchase, Production, etc.)
     * @param int|null $warehouseId Warehouse ID
     * @param int|null $vendorId Vendor ID (optional)
     * @param string|null $notes Additional notes
     * @return ItemMutation|null
     */
    public static function increase(
        int $itemId,
        float $quantity,
        string $unit,
        float $price,
        string $date,
        Model $reference,
        ?int $warehouseId = null,
        ?int $vendorId = null,
        ?string $notes = null,
        ?int $sourceWarehouseId = null,
        ?int $actualItemId = null
    ): ?ItemMutation {

        $mutation = self::createMutation(
            itemId: $itemId,
            type: 'in',
            quantity: self::getBaseQuantity(Item::find($itemId), $quantity, $unit),
            unit: $unit,
            price: $price,
            date: $date,
            reference: $reference,
            warehouseId: $warehouseId,
            vendorId: $vendorId,
            notes: $notes,
            isIncrease: true,
            sourceWarehouseId: $sourceWarehouseId,
            actualItemId: $actualItemId
        );

        if (!$mutation) {
            return null;
        }

        // 2. Create Stock batch record
        $stock = self::createStockBatch(
            itemId: $mutation->item_id,
            quantity: $mutation->quantity,
            unit: $mutation->unit,
            price: $mutation->price,
            date: $date,
            reference: $reference,
            warehouseId: $warehouseId,
            vendorId: $vendorId,
            notes: $notes,
        );

        return $mutation;
    }

    /**
     * Decrease item stock (OUT mutation)
     *
     * @param int $itemId
     * @param float $quantity
     * @param string $unit
     * @param string $date
     * @param Model $reference
     * @param int|null $warehouseId
     * @param int|null $customerId
     * @param string|null $notes
     * @return array<ItemMutation>|null
     */
    public static function decrease(
        int $itemId,
        float $quantity,
        string $unit,
        string $date,
        Model $reference,
        ?int $warehouseId = null,
        ?int $customerId = null,
        ?string $notes = null,
        ?int $actualItemId = null
    ): ?array {
        // try {
        return DB::transaction(function () use (
            $itemId,
            $quantity,
            $unit,
            $date,
            $reference,
            $warehouseId,
            $customerId,
            $notes,
            $actualItemId
        ) {
            // 1. Consume stock using FIFO
            $consumption = self::consumeStockFifo($itemId, $warehouseId, $quantity, $unit);

            if (!$consumption || empty($consumption['batches_consumed'])) {
                Log::warning("No stock batches available for consumption", [
                    'item_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $quantity
                ]);
                throw new Exception("No stock batches available for consumption $itemId ");
            }

            // 2. Create separate ItemMutation for each consumed batch
            $mutations = [];
            foreach ($consumption['batches_consumed'] as $batchInfo) {
               

                $batchNotes = $notes ? $notes . " (Batch: {$batchInfo['batch_number']})" : "Batch: {$batchInfo['batch_number']}";

                $mutation = self::createMutation(
                    itemId: $itemId,
                    type: 'out',
                    quantity: $batchInfo['quantity_consumed'],
                    unit: $unit,
                    price: $batchInfo['unit_cost'],
                    date: $date,
                    reference: $reference,
                    warehouseId: $warehouseId,
                    customerId: $customerId,
                    notes: $batchNotes,
                    stockId: $batchInfo['batch_id'],
                    isIncrease: false,
                    actualItemId: $actualItemId
                );

                if ($mutation) {
                    $mutations[] = $mutation;
                }
            }
            return $mutations;
        });
        // } catch (Exception $e) {
        //     throw new Exception("Failed to decrease item stock: " . $e->getMessage());
        // }
    }
    public static function delete(
        string $referenceType,
        ?int $referenceId = null,
        bool $forceDelete = false,
        ?string $journalReferenceType
    ): array {
        try {

            $journals = Journal::where('transaction_type', $journalReferenceType)
                ->when($referenceId !== null, function ($query) use ($referenceId) {
                    $query->where('transaction_id', $referenceId);
                })
                ->where('transaction_type', $journalReferenceType)
                ->withTrashed()
                ->forceDelete();

            // $journals->forceDelete();
            // foreach ($journals as $journal) {
            //     // $journal?->transactions()->forceDelete();
            //     $journal?->forceDelete();
            // }
            $result = DB::transaction(function () use ($referenceType, $referenceId, $forceDelete) {
                // 1. First perform safety checks for increase operations (stock batches)
                $stockSafetyCheck = self::performStockSafetyCheck($referenceType, $referenceId);

                if (!$stockSafetyCheck['safe'] && !$forceDelete) {
                    return [
                        'success' => false,
                        'safety_check' => $stockSafetyCheck,
                        'mutations_deleted' => 0,
                        'stock_batches_deleted' => 0,
                        'stock_quantities_restored' => 0,
                        'error' => 'Cannot safely delete: ' . $stockSafetyCheck['reason']
                    ];
                }

                // 2. Handle OUT mutations (restore consumed quantities)
                $outMutationsQuery = ItemMutation::where('reference_type', $referenceType)
                    ->where('type', 'out');
                if ($referenceId !== null) {
                    $outMutationsQuery->where('reference_id', $referenceId);
                }

                $outMutations = $outMutationsQuery->get();
                $stockRestoredCount = 0;
                $restorationErrors = [];

                foreach ($outMutations as $mutation) {
                    if ($mutation->stock_id) {
                        $stockBatch = Stock::find($mutation->stock_id);
                        if ($stockBatch) {
                            // Validate restoration won't exceed original batch quantity
                            $newRemainingQuantity = $stockBatch->remaining_quantity + $mutation->quantity;

                            if ($newRemainingQuantity > $stockBatch->quantity) {
                                $restorationErrors[] = [
                                    'mutation_id' => $mutation->id,
                                    'item_id' => $mutation->item_id,
                                    'error' => "Restoration would exceed batch capacity. Batch: {$stockBatch->quantity}, Remaining: {$stockBatch->remaining_quantity}, Restore: {$mutation->quantity}"
                                ];

                                if (!$forceDelete) {
                                    continue; // Skip this restoration if not forced
                                }
                                // If forced, cap the restoration at batch capacity
                                $newRemainingQuantity = $stockBatch->quantity;
                            }

                            $stockBatch->remaining_quantity = $newRemainingQuantity;
                            $stockBatch->save();
                            $stockRestoredCount++;

                            Log::info("Stock quantity restored safely", [
                                'stock_id' => $stockBatch->id,
                                'batch_number' => $stockBatch->batch_number,
                                'restored_quantity' => $mutation->quantity,
                                'new_remaining_quantity' => $newRemainingQuantity,
                                'batch_capacity' => $stockBatch->quantity
                            ]);
                        } else {
                            $restorationErrors[] = [
                                'mutation_id' => $mutation->id,
                                'error' => "Stock batch not found for restoration: {$mutation->stock_id}"
                            ];
                        }
                    }
                }

                // 3. Delete ItemMutation records only if safe or forced
                if ($stockSafetyCheck['safe'] || $forceDelete) {
                    $mutationQuery = ItemMutation::where('reference_type', $referenceType);
                    if ($referenceId !== null) {
                        $mutationQuery->where('reference_id', $referenceId);
                    }
                    $mutationCount = $mutationQuery->count();
                    $mutationQuery->delete();

                    // 4. Delete Stock records (only for IN mutations - created batches)
                    // Only delete if no consumption detected or if forced
                    $stockQuery = Stock::where('reference_type', $referenceType);
                    if ($referenceId !== null) {
                        $stockQuery->where('reference_id', $referenceId);
                    }

                    if (!$forceDelete) {
                        // Additional safety: only delete stocks that haven't been consumed
                        $stockQuery->whereRaw('quantity = remaining_quantity');
                    }

                    $stockCount = $stockQuery->count();
                    $stockQuery->delete();
                    return [
                        'success' => true,
                        'safety_check' => $stockSafetyCheck,
                        'mutations_deleted' => $mutationCount,
                        'stock_batches_deleted' => $stockCount,
                        'stock_quantities_restored' => $stockRestoredCount,
                        'restoration_errors' => $restorationErrors,
                        'forced' => $forceDelete
                    ];
                } else {
                    return [
                        'success' => false,
                        'safety_check' => $stockSafetyCheck,
                        'mutations_deleted' => 0,
                        'stock_batches_deleted' => 0,
                        'stock_quantities_restored' => $stockRestoredCount,
                        'restoration_errors' => $restorationErrors,
                        'error' => 'Deletion aborted due to safety concerns'
                    ];
                }
            });
            if ($result === null) {
                throw new Exception("Failed to safely delete item mutations and stock: Transaction returned null");
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to safely delete item mutations and stock: " . $e->getMessage());
        }
    }

    /**
     * Create item mutation (internal helper method)
     *
     * @param int $itemId Item ID
     * @param string $type 'in' or 'out'
     * @param float $quantity Quantity in the specified unit
     * @param string $unit Unit name
     * @param float $price Price per unit
     * @param string $date Date of mutation
     * @param Model $reference Reference model
     * @param int|null $warehouseId Warehouse ID
     * @param int|null $vendorId Vendor ID
     * @param int|null $customerId Customer ID
     * @param string|null $notes Additional notes
     * @param int|null $stockId Stock batch ID (for tracking which batch was consumed)
     * @return ItemMutation|null
     */
    protected static function createMutation(
        int $itemId,
        string $type,
        float $quantity,
        string $unit,
        float $price,
        string $date,
        Model $reference,
        ?int $warehouseId = null,
        ?int $vendorId = null,
        ?int $customerId = null,
        ?string $notes = null,
        ?int $stockId = null,
        ?bool $isIncrease = true,
        ?int $sourceWarehouseId = null,
        ?int $actualItemId = null
    ): ?ItemMutation {
        $item = Item::find($itemId);
        if (!$item) {
            Log::error("Item not found", ['item_id' => $itemId]);
            return null;
        }

        // Resolve actual item if it's linked
        if($actualItemId) {
            $actualItem = Item::find($actualItemId);
        } else {
            $actualItem = $item;
        }
        $actualItem = $item;
        $targetItem = $item;

        if ($item->is_linked && $item->linkedItem) {
            $targetItem = $item->linkedItem;
        }



        // Get unit conversion
        $conversion = self::getUnitConversion($targetItem, $unit);
        if ($conversion === null) {
            Log::error("Unit conversion not found", [
                'item_id' => $targetItem->id,
                'item_name' => $targetItem->name ?? 'Unknown',
                'unit' => $unit
            ]);
            return null;
        }
        // Auto FIFO price detection - Apply sama seperti di method increase()
        if ($price == 0) {
            $fifoPrice = self::getFifoAveragePrice($itemId, $sourceWarehouseId);
            $price = $fifoPrice ?? 0;
            $basePrice = $price;
        } else {
            if ($isIncrease) {
                $basePrice = $price / $conversion;
            } else {
                $basePrice = $price;
            }
        }

        // Get target unit info
        $unitModel = $targetItem->units()->where('name', $unit)->first();
        $targetUnitName = $unitModel ? $unitModel->to : $unit;

        // Create mutation record
        $mutation = ItemMutation::create([
            'item_id' => $targetItem->id,
            'actual_item_id' => $actualItem->id,
            'date' => $date,
            'type' => $type,
            'reference_type' => get_class($reference),
            'reference_id' => $reference->id,
            'quantity' => $quantity,
            'price' => $basePrice,
            'unit' => $targetUnitName,
            'warehouse_id' => $warehouseId,
            'vendor_id' => $vendorId,
            'customer_id' => $customerId,
            'stock_id' => $stockId,
            'notes' => $notes ?? self::generateNotes($reference),
        ]);

        return $mutation;
    }

    /**
     * Get unit conversion factor
     *
     * @param Item $item The item
     * @param string $unitName Unit name
     * @return float|null Conversion factor or null if not found
     */
    protected static function getUnitConversion(Item $item, string $unitName): ?float
    {
        $unit = $item->units()->where('name', $unitName)->first();
        return $unit ? ($unit->conversion ?? 1) : 1;
    }

    /**
     * Generate default notes for reference
     *
     * @param Model $reference Reference model
     * @return string Generated notes
     */
    protected static function generateNotes(Model $reference): string
    {
        $className = class_basename(get_class($reference));
        return "{$className} #{$reference->id}";
    }

    /**
     * Create stock batch record
     *
     * @param int $itemId Item ID
     * @param float $quantity Quantity
     * @param string $unit Unit name
     * @param float $price Price per unit
     * @param string $date Date
     * @param Model $reference Reference model
     * @param int|null $warehouseId Warehouse ID
     * @param int|null $vendorId Vendor ID
     * @param string|null $notes Notes
     * @return Stock|null
     */
    protected static function createStockBatch(
        int $itemId,
        float $quantity,
        string $unit,
        float $price,
        string $date,
        Model $reference,
        ?int $warehouseId = null,
        ?int $vendorId = null,
        ?string $notes = null
    ): ?Stock {
        try {
            $stock = Stock::where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if (!$stock) {
                $stock = Stock::create([
                    'date' => $date,
                    'item_id' => $itemId,
                    'warehouse_id' => $warehouseId,
                    'unit' => $unit,
                    'price' => $price,
                    'total' => $price * $quantity,
                    'quantity' => $quantity,
                    'remaining_quantity' => $quantity,
                    'reference_type' => '',
                    'reference_id' => 0,
                    'batch_number' => '',
                    'notes' => ''
                ]);
            } else {
                $stock->quantity += $quantity;
                $stock->remaining_quantity += $quantity;
                $stock->total += $price * $quantity;
                $stock->price = $stock->total / $stock->quantity;
                $stock->save();
            }


            if ($itemId == 40) {
                Log::info("Creating or updating stock batch for item 40", [
                    'stock_id' => $stock->id,
                    'price' => $price,
                    'quantity' => $quantity,
                    'total' => $price * $quantity,
                 ], 'item_mutation');
            }

            return $stock;
        } catch (Exception $e) {
            return dd($e->getMessage());
        }
    }



    protected static function getBaseQuantity(Item $item, float $quantity, string $unit): ?float
    {
        $conversion = self::getUnitConversion($item, $unit);
        $quantity = $quantity * $conversion;
        return $quantity;
    }




    /**
     * Consume stock using FIFO method
     *
     * @param int $itemId Item ID
     * @param int|null $warehouseId Warehouse ID
     * @param float $quantity Quantity to consume
     * @return array|null Consumption details
     */
    protected static function consumeStockFifo(int $itemId, ?int $warehouseId, float $quantity, string $unit): ?array
    {

        $item = Item::find($itemId);
        if (!$item) {
            Log::error("Item not found", ['item_id' => $itemId]);
            return null;
        }

        // Resolve actual item if it's linked
        $actualItem = $item;
        $targetItem = $item;
        
        if ($item->is_linked && $item->linkedItem) {
            $targetItem = $item->linkedItem;
        }

        $remainingToConsume = self::getBaseQuantity($targetItem, $quantity, $unit);
        $consumedBatches = [];

        // Get available stock batch (already merged/averaged per warehouse)
        $availableBatch = Stock::where('item_id', $targetItem->id)
            ->where('remaining_quantity', '>', 0)
            ->orderBy('date')
            ->where('warehouse_id', $warehouseId)
            ->first();

        if (!$availableBatch) {
            throw new Exception("No stock batches available for consumption {$targetItem->name} {$quantity} {$remainingToConsume}");
        }

        // Since stock is already merged/averaged per warehouse, we consume from this single batch
        $consumeFromThisBatch = min($remainingToConsume, $availableBatch->remaining_quantity);
        $availableBatch->remaining_quantity -= $consumeFromThisBatch;
        $availableBatch->save();


        $consumedBatches[] = [
            'batch_id' => $availableBatch->id,
            'batch_number' => $availableBatch->batch_number,
            'quantity_consumed' => $consumeFromThisBatch,
            'unit_cost' => $availableBatch->price, // Already averaged price
            'total_cost' => $consumeFromThisBatch * $availableBatch->price,
            'batch_date' => $availableBatch->date,
            'remaining_in_batch' => $availableBatch->remaining_quantity
        ];

        $remainingToConsume -= $consumeFromThisBatch;

        $totalConsumed = $quantity - $remainingToConsume;
        $totalCost = collect($consumedBatches)->sum('total_cost');
        $averageCost = $totalConsumed > 0 ? $totalCost / $totalConsumed : 0;
        $result = [
            'total_consumed' => $totalConsumed,
            'total_cost' => $totalCost,
            'average_cost' => $averageCost,
            'batches_consumed' => $consumedBatches,
            'shortage' => $remainingToConsume
        ];
        return $result;
    }

    /**
     * Generate batch number
     *
     * @param string $date Date
     * @param Model $reference Reference model
     * @return string Batch number
     */
    protected static function generateBatchNumber(string $date, Model $reference): string
    {
        $dateFormatted = date('Ymd', strtotime($date));
        $referenceClass = class_basename(get_class($reference));
        return "BATCH-{$dateFormatted}-{$referenceClass}-{$reference->id}";
    }

    /**
     * Calculate FIFO average price for an item
     * This method gets weighted average price from all available stock batches
     *
     * @param int $itemId Item ID (can be linked item)
     * @return float|null Average price or null if no stock available
     */
    protected static function getFifoAveragePrice(int $itemId, ?int $warehouseId = null): ?float
    {
        try {
            $item = Item::find($itemId);
            if (!$item) {
                Log::error("Item not found for FIFO price calculation", ['item_id' => $itemId]);
                return null;
            }

            // Resolve target item (handle linked items)
            $targetItem = $item;
            if ($item->is_linked && $item->linkedItem) {
                $targetItem = $item->linkedItem;
            }

            // Get all available stock batches across all warehouses, ordered by FIFO
            $query = Stock::where('item_id', $targetItem->id)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('date')
                ->orderBy('id')
                ->when($warehouseId !== null, function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                });

            $availableBatches = $query->get();

            if ($availableBatches->count() == 0) {
                $purchasePrice = self::getPurchasePrice($itemId);
                Log::info("Using purchase price as fallback for FIFO average price", [
                    'item_id' => $itemId,
                    'purchase_price' => $purchasePrice
                ]);
                return $purchasePrice;
            }

            $totalValue = 0;
            $totalQuantity = 0;

            foreach ($availableBatches as $batch) {
                $batchValue = $batch->remaining_quantity * $batch->price;
                $totalValue += $batchValue;
                $totalQuantity += $batch->remaining_quantity;

                Log::debug("FIFO batch included", [
                    'batch_id' => $batch->id,
                    'quantity' => $batch->remaining_quantity,
                    'price' => $batch->price,
                    'value' => $batchValue
                ]);
            }

            if ($totalQuantity == 0) {
                Log::warning("Total quantity is zero for FIFO price calculation", [
                    'item_id' => $itemId,
                    'batches_count' => $availableBatches->count()
                ]);
                return null;
            }

            $averagePrice = $totalValue / $totalQuantity;

            Log::info("FIFO average price calculated", [
                'item_id' => $itemId,
                'target_item_id' => $targetItem->id,
                'total_value' => $totalValue,
                'total_quantity' => $totalQuantity,
                'average_price' => $averagePrice,
                'batches_used' => $availableBatches->count()
            ]);
            return $averagePrice;
        } catch (Exception $e) {
            Log::error("Failed to calculate FIFO average price", [
                'error' => $e->getMessage(),
                'item_id' => $itemId
            ]);
            return null;
        }
    }

    protected static function getPurchasePrice(int $itemId): ?float
    {
        $item = Item::find($itemId);
        $unit = $item->units()->where('is_purchase_price', true)->first();
        $units = $item->units()->get();
        if ($unit->name == $units[0]->name) {
            return $item->purchase_price;
        }
        return $item->purchase_price / $unit->conversion;
    }

    /**
     * Bulk increase for multiple items in single reference (Purchase, ItemIn, etc.)
     * 
     * @param Model $reference Reference model (Purchase, ItemIn, etc.)
     * @param array<\App\DTOs\ItemMutationData> $items Array of ItemMutationData objects
     * @param string|null $date Date override (uses reference date if null)
     * @param int|null $divisionId Division ID for journal entry (if applicable)
     * @param string|null $notes Additional notes for journal entry
     * @return BulkMutationResult Results of all operations with proper DTO structure
     */
    public static function bulkIncrease(
        Model $reference,
        array $items,
        ?string $date = null,
        ?int $divisionId = null,
        ?string $notes = null,
        ?string $journalReferenceType
    ): BulkMutationResult {
        // Validate all items are ItemMutationData
        foreach ($items as $index => $item) {
            if (!$item instanceof \App\DTOs\ItemMutationData) {
                throw new \InvalidArgumentException("Item at index {$index} must be ItemMutationData instance");
            }

            $errors = $item->getValidationErrors();
            if (!empty($errors)) {
                throw new \InvalidArgumentException("Item at index {$index} validation errors: " . implode(', ', $errors));
            }
        }

        $results = [];
        $totalItems = count($items);

        foreach ($items as $index => $itemData) {
            try {
                $mutation = self::increase(
                    itemId: $itemData->item_id,
                    quantity: $itemData->quantity,
                    unit: $itemData->unit,
                    price: $itemData->price,
                    date: $date ?? $itemData->date ?? now()->toDateString(),
                    reference: $reference,
                    warehouseId: $itemData->warehouse_id,
                    vendorId: $itemData->vendor_id,
                    notes: $itemData->notes,
                    sourceWarehouseId: $itemData->sourceWarehouseId,
                    actualItemId: $itemData->item_id
                );

                $results[] = new ItemMutationResult(
                    item_id: $itemData->item_id,
                    action: 'increased',
                    success: true,
                    quantity: $itemData->quantity,
                    unit: $itemData->unit,
                    mutation_id: $mutation?->id,
                    mutations: [$mutation]
                );
            } catch (Exception $e) {
                throw new \Exception("Failed to increase item ID {$itemData->item_id}: " . $e->getMessage());
            }
        }

        $successCount = collect($results)->where('success', true)->count();
        $failCount = $totalItems - $successCount;

        if ($failCount > 0) {
            throw new \Exception("Bulk increase operation failed for {$failCount} out of {$totalItems} items.");
        }
        if ($journalReferenceType) {
            $journal = Journal::create([
                'number' => Journal::generateNumber($date),
                'division_id' => $divisionId,
                'date' => $date,
                'notes' => $notes,
                'transaction_type' => $journalReferenceType,
                'transaction_id' => $reference->id,
                'credit' => 0,
                'debit' => collect($results)->sum(function ($r) {
                    return collect($r->mutations)->sum(fn($m) => $m->quantity * $m->price);
                }),
            ]);
            foreach ($results as $result) {
                foreach ($result->mutations as $mutation) {
                    $journal->transactions()->create([
                        'chart_of_account_id' => self::getInventoryAccountId(),
                        'transaction_type' => 'debit',
                        'debit' => $mutation->quantity * $mutation->price,
                        'credit' => 0,
                        'notes' => "{$mutation->item->name}"
                    ]);
                }
            }
        }


        $summary = new BulkMutationSummary(
            total: $totalItems,
            success: $successCount,
            failed: $failCount
        );

        return new BulkMutationResult(
            success: $failCount === 0,
            operation: 'bulk_increase',
            results: $results,
            summary: $summary
        );
    }

    /**
     * Bulk decrease for multiple items in single reference (Sale, ItemOut, etc.)
     * 
     * @param Model $reference Reference model (Sale, ItemOut, etc.)
     * @param array<\App\DTOs\ItemMutationData> $items Array of ItemMutationData objects
     * @param string|null $date Date override
     * @param int|null $divisionId Division ID for journal entry (if applicable)
     * @param string|null $notes Additional notes for journal entry
     * @return BulkMutationResult Results of all operations with proper DTO structure
     */
    public static function bulkDecrease(
        Model $reference,
        array $items,
        ?string $date = null,
        ?int $divisionId = null,
        ?string $notes = null,
        ?string $journalReferenceType
    ): BulkMutationResult {
        // Validate all items are ItemMutationData
        foreach ($items as $index => $item) {
            if (!$item instanceof \App\DTOs\ItemMutationData) {
                throw new \InvalidArgumentException("Item at index {$index} must be ItemMutationData instance");
            }

            $errors = $item->getValidationErrors();
            if (!empty($errors)) {
                throw new \InvalidArgumentException("Item at index {$index} validation errors: " . implode(', ', $errors));
            }
        }

        $results = [];
        $totalItems = count($items);

        foreach ($items as $index => $itemData) {
            $mutations = self::decrease(
                itemId: $itemData->item_id,
                quantity: $itemData->quantity,
                unit: $itemData->unit,
                date: $date ?? $itemData->date ?? now()->toDateString(),
                reference: $reference,
                warehouseId: $itemData->warehouse_id,
                customerId: $itemData->customer_id,
                notes: $itemData->notes,
                actualItemId: $itemData->item_id
            );

            $results[] = new ItemMutationResult(
                item_id: $itemData->item_id,
                action: 'decreased',
                success: true,
                quantity: $itemData->quantity,
                unit: $itemData->unit,
                mutation_id: is_array($mutations) && count($mutations) > 0 ? $mutations[0]?->id : null,
                mutations_count: is_array($mutations) ? count($mutations) : 0,
                mutations: $mutations
            );
        }

        $successCount = collect($results)->where('success', true)->count();
        $failCount = $totalItems - $successCount;


        if ($failCount > 0) {
            throw new \Exception("Bulk decrease operation failed for {$failCount} out of {$totalItems} items.");
        }
        if ($journalReferenceType) {
            $journal = Journal::create([
                'number' => Journal::generateNumber($date),
                'division_id' => $divisionId,
                'date' => $date,
                'notes' => $notes,
                'transaction_type' => $journalReferenceType,
                'transaction_id' => $reference->id,
                'credit' => collect($results)->sum(function ($r) {
                    return collect($r->mutations)->sum(fn($m) => $m->quantity * $m->price);
                }),
                'debit' => 0,
            ]);
            foreach ($results as $result) {
                foreach ($result->mutations as $mutation) {
                    $journal->transactions()->create([
                        'chart_of_account_id' => self::getInventoryAccountId(),
                        'transaction_type' => 'credit',
                        'debit' => 0,
                        'credit' => $mutation->quantity * $mutation->price,
                        'notes' => "{$mutation->item->name}"
                    ]);
                }
            }
        }



        $summary = new BulkMutationSummary(
            total: $totalItems,
            success: $successCount,
            failed: $failCount
        );

        return new BulkMutationResult(
            success: $failCount === 0,
            operation: 'bulk_decrease',
            results: $results,
            summary: $summary
        );
    }

    public static function bulkUpdate(
        Model $reference,
        array $newItems,
        ?string $operation = null,
        ?string $date = null,
        ?int $divisionId = null,
        ?string $notes = null,
        ?string $journalReferenceType,
    ): BulkMutationResult {
        // Validate all items are ItemMutationData
        foreach ($newItems as $index => $item) {
            if (!$item instanceof \App\DTOs\ItemMutationData) {
                throw new \InvalidArgumentException("Item at index {$index} must be ItemMutationData instance");
            }

            $errors = $item->getValidationErrors();
            if (!empty($errors)) {
                throw new \InvalidArgumentException("Item at index {$index} validation errors: " . implode(', ', $errors));
            }
        }

        // Auto-detect operation if not specified
        if ($operation === null) {
            $operation = self::detectOperation($reference);
        }

        // 1. Get existing mutations for this reference
        $existingMutations = ItemMutation::where('reference_type', get_class($reference))
            ->where('reference_id', $reference->id)
            ->get()
            ->groupBy('item_id');



        // 2. Process new items and track which items should exist
        $processedItemIds = [];
        $results = [];

        foreach ($newItems as $itemData) {
            $itemId = $itemData->item_id;
            $item = Item::find($itemId);
            if ($item->is_linked) {
                $itemId = $item->linked_item_id;
            }
            $processedItemIds[] = $itemId;

            $isExisting = isset($existingMutations[$itemId]);

            if ($operation === 'increase') {
                if ($isExisting) {
                    // Update existing item
                    $result = self::updateIncreaseOperation(
                        $itemId,
                        $itemData->quantity,
                        $itemData->unit,
                        $itemData->price,
                        $date ?? $itemData->date ?? now()->toDateString(),
                        $reference,
                        $itemData->warehouse_id,
                        $itemData->vendor_id,
                        $itemData->notes,
                        $itemData->item_id,
                    );
                    $action = 'updated_increase';
                } else {
                    // Create new item
                    $result = self::increase(
                        $itemId,
                        $itemData->quantity,
                        $itemData->unit,
                        $itemData->price,
                        $date ?? $itemData->date ?? now()->toDateString(),
                        $reference,
                        $itemData->warehouse_id,
                        $itemData->vendor_id,
                        $itemData->notes,
                        null,
                        $itemData->item_id
                    );
                    $action = 'created_increase';
                }
            } else {
                if ($isExisting) {
                    // Update existing item  
                    $result = self::updateDecreaseOperation(
                        $itemId,
                        $itemData->quantity,
                        $itemData->unit,
                        $itemData->price,
                        $date ?? $itemData->date ?? now()->toDateString(),
                        $reference,
                        $itemData->warehouse_id,
                        $itemData->customer_id,
                        $itemData->notes,
                        $itemData->item_id,
                    );

                    $action = 'updated_decrease';
                } else {
                    // Create new item
                    $result = self::decrease(
                        $itemId,
                        $itemData->quantity,
                        $itemData->unit,
                        $date ?? $itemData->date ?? now()->toDateString(),
                        $reference,
                        $itemData->warehouse_id,
                        $itemData->customer_id,
                        $itemData->notes,
                        $itemData->item_id
                    );
                    $action = 'created_decrease';
                }
            }

            // Safely extract mutation ID from result

            $mutationId = null;
            if (is_array($result) || $result instanceof Collection) {
                $mutationId = !empty($result) && isset($result[0]) ? $result[0]?->id : null;
            } else {
                $mutationId = $result?->id;
            }

            $results[] = new ItemMutationResult(
                item_id: $itemId,
                action: $action,
                success: true,
                quantity: $itemData->quantity,
                unit: $itemData->unit,
                mutation_id: $mutationId,
                mutations: is_array($result) ? $result : [$result]
            );
        }

        // 3. Delete items that are no longer in the new items list
        $itemsToDelete = $existingMutations->keys()->diff($processedItemIds);
        foreach ($itemsToDelete as $itemIdToDelete) {
            // Perform safe deletion with comprehensive checks
            $deleteResult = self::deleteSpecificItem($itemIdToDelete, $reference, false); // Never force delete in bulk operations

            if ($deleteResult['success']) {
                $results[] = new ItemMutationResult(
                    item_id: $itemIdToDelete,
                    action: 'deleted',
                    success: true,
                    quantity: 0,
                    unit: ''
                );
            } else {
                $results[] = new ItemMutationResult(
                    item_id: $itemIdToDelete,
                    action: 'delete_failed',
                    success: false,
                    quantity: 0,
                    unit: '',
                    error: $deleteResult['error'] ?? 'Unknown deletion error'
                );
            }
        }


        $totalOperations = count($results);
        $successCount = collect($results)->where('success', true)->count();
        $failCount = $totalOperations - $successCount;

        if ($failCount > 0) {
            throw new \Exception("Bulk update operation failed for {$failCount} out of {$totalOperations} operations.");
        }
        if ($journalReferenceType) {
            $journalTransactions = [];
            foreach ($results as $result) {
                foreach ($result?->mutations ?? [] as $mutationItems) {
                    if (is_array($mutationItems) || $mutationItems instanceof Collection) {
                        foreach ($mutationItems as $mutation) {
                            $journalTransactions[] = [
                                'chart_of_account_id' => self::getInventoryAccountId(),
                                'transaction_type' => $mutation->type === 'in' ? 'debit' : 'credit',
                                'debit' => $mutation->type === 'in' ? $mutation->quantity * $mutation->price : 0,
                                'credit' => $mutation->type === 'out' ? $mutation->quantity * $mutation->price : 0,
                                'notes' => "{$mutation->item->name}"
                            ];
                        }
                    } else {
                        $journalTransactions[] = [
                            'chart_of_account_id' => self::getInventoryAccountId(),
                            'transaction_type' => $mutationItems->type === 'in' ? 'debit' : 'credit',
                            'debit' => $mutationItems->type === 'in' ? $mutationItems->quantity * $mutationItems->price : 0,
                            'credit' => $mutationItems->type === 'out' ? $mutationItems->quantity * $mutationItems->price : 0,
                            'notes' => "{$mutationItems->item->name}"
                        ];
                    }
                }
            }

            $journal = Journal::updateOrCreate([
                'transaction_type' => $journalReferenceType,
                'transaction_id' => $reference->id,
            ], [
                'number' => Journal::generateNumber($date),
                'division_id' => $divisionId,
                'debit' => collect($journalTransactions)->sum('debit'),
                'credit' => collect($journalTransactions)->sum('credit'),
                'date' => $date ?? now()->toDateString(),
                'notes' => $notes,
            ]);
            $journal->transactions()->forceDelete();
            foreach ($journalTransactions as $jt) {
                $journal->transactions()->create($jt);
            }
        }



        $summary = new BulkMutationSummary(
            total: $totalOperations,
            success: $successCount,
            failed: $failCount
        );

        return new BulkMutationResult(
            success: $failCount === 0,
            operation: 'bulk_update',
            results: $results,
            summary: $summary
        );
    }


    /**
     * Auto-detect operation type from reference model
     * 
     * @param Model $reference Reference model
     * @return string 'increase' or 'decrease'
     */
    protected static function detectOperation(Model $reference): string
    {
        $increaseTypes = [
            'App\Models\Purchase',
            'App\Models\ItemIn',
            'App\Models\Production',
            'App\Models\SalesReturn',
            'App\Models\PurchaseReturn'
        ];

        $decreaseTypes = [
            'App\Models\Sale',
            'App\Models\ItemOut',
            'App\Models\StockMovement',
        ];

        $referenceClass = get_class($reference);

        if (in_array($referenceClass, $increaseTypes)) {
            return 'increase';
        } elseif (in_array($referenceClass, $decreaseTypes)) {
            return 'decrease';
        }

        // Default fallback
        Log::warning("Unknown reference type for operation detection", [
            'reference_type' => $referenceClass
        ]);
        return 'increase';
    }

    /**
     * Check if an item can be safely deleted from a reference
     * 
     * @param int $itemId Item ID to check
     * @param Model $reference Reference model
     * @param string $operation increase/decrease operation
     * @return array ['safe' => bool, 'reason' => string]
     */
    protected static function canSafelyDeleteItem(int $itemId, Model $reference, string $operation): array
    {
        if ($operation === 'increase') {
            // For increase operations, check if stock has been consumed
            $stock = Stock::where('reference_type', get_class($reference))
                ->where('reference_id', $reference->id)
                ->where('item_id', $itemId)
                ->first();

            if ($stock) {
                $consumed = $stock->quantity - $stock->remaining_quantity;
                if ($consumed > 0) {
                    return [
                        'safe' => false,
                        'reason' => "Item has been consumed. Consumed: {$consumed}, Remaining: {$stock->remaining_quantity}"
                    ];
                }
            }

            return ['safe' => true, 'reason' => 'No consumption detected'];
        } else {
            // For decrease operations, it's usually safe to delete and restore
            return ['safe' => true, 'reason' => 'Decrease operation can be safely reversed'];
        }
    }

    /**
     * Delete specific item from reference (both mutations and stocks) with safety checks
     * 
     * @param int $itemId Item ID to delete
     * @param Model $reference Reference model
     * @param bool $forceDelete Force delete even if consumed
     * @return array Delete results with safety information
     */
    protected static function deleteSpecificItem(int $itemId, Model $reference, bool $forceDelete = false): array
    {
        try {
            // 1. Check if this specific item is safe to delete
            $itemSafetyCheck = self::checkItemDeletionSafety($itemId, $reference);

            if (!$itemSafetyCheck['safe'] && !$forceDelete) {
                return [
                    'success' => false,
                    'mutations_deleted' => 0,
                    'stocks_deleted' => 0,
                    'stock_quantities_restored' => 0,
                    'safety_check' => $itemSafetyCheck,
                    'error' => 'Cannot safely delete item: ' . $itemSafetyCheck['reason']
                ];
            }

            // 2. Restore consumed quantities from OUT mutations
            $outMutations = ItemMutation::where('reference_type', get_class($reference))
                ->where('reference_id', $reference->id)
                ->where('item_id', $itemId)
                ->where('type', 'out')
                ->get();

            $stockRestoredCount = 0;
            $restorationErrors = [];

            foreach ($outMutations as $mutation) {
                if ($mutation->stock_id) {
                    $stockBatch = Stock::find($mutation->stock_id);
                    if ($stockBatch) {
                        // Validate restoration
                        $newRemainingQuantity = $stockBatch->remaining_quantity + $mutation->quantity;

                        if ($newRemainingQuantity > $stockBatch->quantity && !$forceDelete) {
                            $restorationErrors[] = [
                                'mutation_id' => $mutation->id,
                                'error' => "Restoration would exceed batch capacity"
                            ];
                            continue;
                        }

                        $stockBatch->remaining_quantity = min($newRemainingQuantity, $stockBatch->quantity);
                        $stockBatch->save();
                        $stockRestoredCount++;
                    }
                }
            }

            // 3. Delete records only if safe or forced
            if ($itemSafetyCheck['safe'] || $forceDelete) {
                $mutationsDeleted = ItemMutation::where('reference_type', get_class($reference))
                    ->where('reference_id', $reference->id)
                    ->where('item_id', $itemId)
                    ->delete();

                // Only delete unconsumed stock batches unless forced
                $stockQuery = Stock::where('reference_type', get_class($reference))
                    ->where('reference_id', $reference->id)
                    ->where('item_id', $itemId);

                if (!$forceDelete) {
                    $stockQuery->whereRaw('quantity = remaining_quantity');
                }

                $stocksDeleted = $stockQuery->delete();

                Log::info("Item deleted safely", [
                    'item_id' => $itemId,
                    'reference' => get_class($reference) . '#' . $reference->id,
                    'forced' => $forceDelete,
                    'mutations_deleted' => $mutationsDeleted,
                    'stocks_deleted' => $stocksDeleted
                ]);

                return [
                    'success' => true,
                    'mutations_deleted' => $mutationsDeleted,
                    'stocks_deleted' => $stocksDeleted,
                    'stock_quantities_restored' => $stockRestoredCount,
                    'safety_check' => $itemSafetyCheck,
                    'restoration_errors' => $restorationErrors,
                    'forced' => $forceDelete
                ];
            }

            return [
                'success' => false,
                'mutations_deleted' => 0,
                'stocks_deleted' => 0,
                'stock_quantities_restored' => $stockRestoredCount,
                'safety_check' => $itemSafetyCheck,
                'error' => 'Deletion aborted due to safety concerns'
            ];
        } catch (Exception $e) {
            Log::error("Failed to safely delete specific item", [
                'error' => $e->getMessage(),
                'item_id' => $itemId,
                'reference' => get_class($reference) . '#' . $reference->id
            ]);

            return [
                'success' => false,
                'mutations_deleted' => 0,
                'stocks_deleted' => 0,
                'stock_quantities_restored' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if a specific item can be safely deleted from a reference
     * 
     * @param int $itemId Item ID to check
     * @param Model $reference Reference model
     * @return array Safety check result
     */
    protected static function checkItemDeletionSafety(int $itemId, Model $reference): array
    {
        try {
            // Check stock batches created by this reference for this item
            $stockBatches = Stock::where('reference_type', get_class($reference))
                ->where('reference_id', $reference->id)
                ->where('item_id', $itemId)
                ->get();

            $totalBatches = $stockBatches->count();
            $consumedBatches = 0;
            $consumptionDetails = [];

            foreach ($stockBatches as $batch) {
                $consumed = $batch->quantity - $batch->remaining_quantity;

                if ($consumed > 0) {
                    $consumedBatches++;
                    $consumptionDetails[] = [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'total_quantity' => $batch->quantity,
                        'consumed_quantity' => $consumed,
                        'remaining_quantity' => $batch->remaining_quantity,
                        'consumption_percentage' => round(($consumed / $batch->quantity) * 100, 2)
                    ];
                }
            }

            $isSafe = $consumedBatches === 0;
            $reason = $isSafe
                ? "Item is safe to delete (no stock consumption detected)"
                : "Item has been consumed in {$consumedBatches} out of {$totalBatches} batches";

            return [
                'safe' => $isSafe,
                'reason' => $reason,
                'item_id' => $itemId,
                'total_batches' => $totalBatches,
                'consumed_batches' => $consumedBatches,
                'consumption_details' => $consumptionDetails
            ];
        } catch (Exception $e) {
            return [
                'safe' => false,
                'reason' => 'Safety check failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get deletion safety information without actually deleting
     * Useful for UI warnings and confirmations
     * 
     * @param string $referenceType Reference model class
     * @param int|null $referenceId Reference ID
     * @return array Safety information
     */
    public static function getDeletionSafetyInfo(string $referenceType, ?int $referenceId = null): array
    {
        $stockSafetyCheck = self::performStockSafetyCheck($referenceType, $referenceId);

        // Get additional information about mutations
        $mutationsQuery = ItemMutation::where('reference_type', $referenceType);
        if ($referenceId !== null) {
            $mutationsQuery->where('reference_id', $referenceId);
        }

        $mutations = $mutationsQuery->get();
        $mutationsSummary = [
            'total_mutations' => $mutations->count(),
            'increase_mutations' => $mutations->where('type', 'in')->count(),
            'decrease_mutations' => $mutations->where('type', 'out')->count(),
            'items_affected' => $mutations->pluck('item_id')->unique()->count()
        ];

        return [
            'safe_to_delete' => $stockSafetyCheck['safe'],
            'stock_safety' => $stockSafetyCheck,
            'mutations_summary' => $mutationsSummary,
            'recommendation' => $stockSafetyCheck['safe']
                ? 'Safe to delete - no stock consumption detected'
                : 'Deletion not recommended - some stock has been consumed. Consider using force delete if necessary.',
            'force_delete_available' => true
        ];
    }

    /**
     * Internal method for handling increase updates with safe update approach
     */
    protected static function updateIncreaseOperation(
        int $itemId,
        float $quantity,
        string $unit,
        float $price,
        string $date,
        Model $reference,
        ?int $warehouseId = null,
        ?int $vendorId = null,
        ?string $notes = null,
        ?int $actualItemId = null
    ) {
        $existingMutation = ItemMutation::where('reference_type', get_class($reference))
            ->where('reference_id', $reference->id)
            ->where('item_id', $itemId)
            ->first();

        if (!$existingMutation) {
            // If no existing mutation, create new one
            return self::increase($itemId, $quantity, $unit, $price, $date, $reference, $warehouseId, $vendorId, $notes, $actualItemId);
        }

        $item = Item::find($itemId);
        if (!$item) {
            throw new Exception("Item with ID $itemId not found");
        }

        $newBaseQuantity = self::getBaseQuantity($item, $quantity, $unit);
        $conversion = self::getUnitConversion($item, $unit);
        $baseUnit = self::getBaseUnit($item);
        $oldBaseQuantity = $existingMutation->quantity;
        $quantityDifference = $newBaseQuantity - $oldBaseQuantity;

        $price = $price / $conversion;
        $unit = $baseUnit;

        $oldWarehouseId = $existingMutation->warehouse_id;


        $existingMutation->update([
            'actual_item_id' => $actualItemId,
            'quantity' => $newBaseQuantity,
            'unit' => $unit,
            'price' => $price,
            'date' => $date,
            'warehouse_id' => $warehouseId,
            'vendor_id' => $vendorId,
            'notes' => $notes,
        ]);

        if ($quantityDifference != 0 && $warehouseId == $oldWarehouseId) {
            $existingStock = Stock::where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->first();


            if ($existingStock) {
                $consumedQuantity = $existingStock->quantity - $existingStock->remaining_quantity;

                $newTotalQuantity = $existingStock->quantity + $quantityDifference;
                $newRemainingQuantity = $newTotalQuantity - $consumedQuantity;

                if ($newRemainingQuantity < 0) {
                    throw new Exception("Cannot reduce stock {$existingStock->item->name} below consumed amount. Consumed: {$consumedQuantity}, New total: {$newTotalQuantity}");
                }

                $existingStock->update([
                    'quantity' => $newTotalQuantity,
                    'remaining_quantity' => $newRemainingQuantity,
                    'price' => $price,
                    'date' => $date,
                ]);
            } else if ($quantityDifference > 0) {
                self::createStockBatch(
                    itemId: $itemId,
                    quantity: $quantityDifference,
                    unit: $unit,
                    price: $price,
                    date: $date,
                    reference: $reference,
                    warehouseId: $warehouseId,
                    vendorId: $vendorId,
                    notes: $notes
                );
            } else {
                throw new Exception("Cannot decrease quantity: no existing stock batch found for this reference");
            }
        } else if($quantityDifference != 0 && $warehouseId != $oldWarehouseId) {
            // If warehouse changed, we need to adjust stocks in both warehouses
            // Revert old stock
            $oldStock = Stock::where('item_id', $itemId)
                ->where('warehouse_id', $oldWarehouseId)
                ->first();

            if ($oldStock) {
                $consumedQuantity = $oldStock->quantity - $oldStock->remaining_quantity;
                $newOldTotalQuantity = $oldStock->quantity - $oldBaseQuantity;
                $newOldRemainingQuantity = max(0, $newOldTotalQuantity - $consumedQuantity);

                if ($newOldRemainingQuantity < 0) {
                    throw new Exception("Cannot move stock {$oldStock->item->name} from warehouse {$oldWarehouseId} below consumed amount. Consumed: {$consumedQuantity}, New total: {$newOldTotalQuantity}");
                }

                $oldStock->update([
                    'quantity' => $newOldTotalQuantity,
                    'remaining_quantity' => $newOldRemainingQuantity,
                ]);
            }

            // Add to new stock
            $newStock = Stock::where('item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($newStock) {
                $newTotalQuantity = $newStock->quantity + $newBaseQuantity;
                $newRemainingQuantity = $newTotalQuantity - ($newStock->quantity - $newStock->remaining_quantity);

                $newStock->update([
                    'quantity' => $newTotalQuantity,
                    'remaining_quantity' => $newRemainingQuantity,
                    'price' => $price,
                    'date' => $date,
                ]);
            } else {
                self::createStockBatch(
                    itemId: $itemId,
                    quantity: $newBaseQuantity,
                    unit: $unit,
                    price: $price,
                    date: $date,
                    reference: $reference,
                    warehouseId: $warehouseId,
                    vendorId: $vendorId,
                    notes: $notes,
                );
            }
        }

        return $existingMutation;
    }

    /**
     * Internal method for handling decrease updates with safe update approach
     */
    protected static function updateDecreaseOperation(
        int $itemId,
        float $quantity,
        string $unit,
        float $price,
        string $date,
        Model $reference,
        ?int $warehouseId = null,
        ?int $customerId = null,
        ?string $notes = null,
        ?int $actualItemId = null
    ) {
        $existingMutations = ItemMutation::where('reference_type', get_class($reference))
            ->where('reference_id', $reference->id)
            ->where('item_id', $itemId)
            ->get();

        if ($existingMutations->isEmpty()) {
            return self::decrease($itemId, $quantity, $unit, $date, $reference, $warehouseId, $customerId, $notes, $actualItemId);
        }

        $item = Item::find($itemId);
        if (!$item) {
            throw new Exception("Item with ID $itemId not found");
        }
        Log::info("Updating decrease operation for item {$item->name} (ID: {$itemId}) in reference " . get_class($reference) . "#{$reference->id}");
           

        $newBaseQuantity = self::getBaseQuantity($item, $quantity, $unit);
        $oldBaseQuantity = $existingMutations->sum('quantity'); // total kuantitas lama (semua mutasi)
        $quantityDifference = $newBaseQuantity - $oldBaseQuantity;

       
        if ((int) $quantityDifference !== 0) {
            // Balikkan semua konsumsi lama
            foreach ($existingMutations as $mutation) {
                self::reverseStockConsumption($mutation);
            }

            $conversion = self::getUnitConversion($item, $unit);
            $baseUnit = self::getBaseUnit($item);

            $consumption = self::consumeStockFifo($itemId, $warehouseId, $quantity * $conversion, $baseUnit);

            if (!$consumption || empty($consumption['batches_consumed'])) {
                // Restore semua kalau gagal
                foreach ($existingMutations as $mutation) {
                    self::restoreStockConsumption($mutation);
                }
                throw new Exception("Insufficient stock for the updated quantity. Available stock is less than requested {$quantity} {$unit}");
            }

            // Hapus mutasi lama
            foreach ($existingMutations as $mutation) {
                $mutation->delete();
            }

            // Buat ulang mutasi sesuai batch konsumsi baru
            $newMutations = [];
            foreach ($consumption['batches_consumed'] as $batch) {
                $newMutations[] = ItemMutation::create([
                    'actual_item_id' => $actualItemId,
                    'item_id' => $itemId,
                    'quantity' => $batch['quantity_consumed'],
                    'unit' => $baseUnit,
                    'price' => $batch['unit_cost'],
                    'date' => $date,
                    'warehouse_id' => $warehouseId,
                    'customer_id' => $customerId,
                    'notes' => $notes,
                    'stock_id' => $batch['batch_id'],
                    'reference_type' => get_class($reference),
                    'reference_id' => $reference->id,
                ]);
            }

            return $newMutations;
        } else {
            // Kalau cuma update metadata
            foreach ($existingMutations as $mutation) {
                $mutation->update([
                    'actual_item_id' => $actualItemId,
                    'date' => $date,
                    'warehouse_id' => $warehouseId,
                    'customer_id' => $customerId,
                    'notes' => $notes,
                ]);
            }

            return $existingMutations;
        }
    }

    /**
     * Helper method to reverse stock consumption for an existing mutation
     * This restores the remaining_quantity in stock batches that were consumed
     */
    private static function reverseStockConsumption(ItemMutation $mutation): void
    {
        // Find the stock batch that was consumed by this mutation
        if ($mutation->stock_id) {
            $stockBatch = Stock::find($mutation->stock_id);
            if ($stockBatch) {
                // Restore the consumed quantity back to remaining_quantity
                $stockBatch->remaining_quantity += $mutation->quantity;
                $stockBatch->save();
            }
        } else {
            // For mutations without stock_id, we need to find which batches were affected
            // This is more complex and might require additional tracking
            Log::warning("Cannot reverse stock consumption: mutation has no stock_id reference", [
                'mutation_id' => $mutation->id,
                'item_id' => $mutation->item_id
            ]);
        }
    }

    /**
     * Helper method to restore stock consumption for an existing mutation
     * This is called when reversal fails and we need to put things back as they were
     */
    private static function restoreStockConsumption(ItemMutation $mutation): void
    {
        // Re-consume the stock that we just restored
        if ($mutation->stock_id) {
            $stockBatch = Stock::find($mutation->stock_id);
            if ($stockBatch) {
                // Re-consume the quantity by reducing remaining_quantity
                $stockBatch->remaining_quantity = max(0, $stockBatch->remaining_quantity - $mutation->quantity);
                $stockBatch->save();

                Log::info("Stock consumption restored after failed update", [
                    'mutation_id' => $mutation->id,
                    'stock_batch_id' => $stockBatch->id,
                    'reconsumed_quantity' => $mutation->quantity,
                    'remaining_quantity' => $stockBatch->remaining_quantity
                ]);
            }
        } else {
            Log::warning("Cannot restore stock consumption: mutation has no stock_id reference", [
                'mutation_id' => $mutation->id
            ]);
        }
    }

    protected static function getBaseUnit(Item $item): string
    {
        $unit = $item->units()->first();
        return $unit->name;
    }

    /**
     * Perform comprehensive safety check before deleting stock batches
     * 
     * @param string $referenceType Reference model class
     * @param int|null $referenceId Reference ID
     * @return array Safety check results
     */
    protected static function performStockSafetyCheck(string $referenceType, ?int $referenceId = null): array
    {
        try {
            // Find all stock batches that would be deleted
            $stockQuery = Stock::where('reference_type', $referenceType);
            if ($referenceId !== null) {
                $stockQuery->where('reference_id', $referenceId);
            }

            $stockBatches = $stockQuery->get();
            $unsafeItems = [];
            $totalBatches = $stockBatches->count();
            $consumedBatches = 0;

            foreach ($stockBatches as $batch) {
                $consumed = $batch->quantity - $batch->remaining_quantity;

                if ($consumed > 0) {
                    $consumedBatches++;
                    $unsafeItems[] = [
                        'stock_id' => $batch->id,
                        'item_id' => $batch->item_id,
                        'batch_number' => $batch->batch_number,
                        'total_quantity' => $batch->quantity,
                        'remaining_quantity' => $batch->remaining_quantity,
                        'consumed_quantity' => $consumed,
                        'consumption_percentage' => round(($consumed / $batch->quantity) * 100, 2),
                        'item_name' => $batch->item->name ?? 'Unknown'
                    ];
                }
            }

            $isSafe = count($unsafeItems) === 0;
            $reason = $isSafe
                ? 'All stock batches are safe to delete (no consumption detected)'
                : "Found {$consumedBatches} consumed batches out of {$totalBatches} total batches";

            Log::info("Stock safety check completed", [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'total_batches' => $totalBatches,
                'consumed_batches' => $consumedBatches,
                'is_safe' => $isSafe
            ]);

            return [
                'safe' => $isSafe,
                'reason' => $reason,
                'total_batches' => $totalBatches,
                'consumed_batches' => $consumedBatches,
                'unsafe_items' => $unsafeItems,
                'summary' => [
                    'safe_batches' => $totalBatches - $consumedBatches,
                    'consumed_batches' => $consumedBatches,
                    'safety_percentage' => $totalBatches > 0 ? round((($totalBatches - $consumedBatches) / $totalBatches) * 100, 2) : 100
                ]
            ];
        } catch (Exception $e) {
            Log::error("Stock safety check failed", [
                'error' => $e->getMessage(),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId
            ]);

            return [
                'safe' => false,
                'reason' => 'Safety check failed: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    protected static function getInventoryAccountId(): ?int
    {
        $account = Chart_ofAccount::where('name', 'Persediaan')->first();
        return $account ? $account->id : null;
    }

}
