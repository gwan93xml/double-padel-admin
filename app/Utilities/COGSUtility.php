<?php

namespace App\Utilities;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class COGSUtility
{
    /**
     * Calculate Cost of Goods Sold for a sale item
     *
     * @param object $saleItem The sale item object
     * @param Item $item The item model
     * @return float
     */
    public static function calculateCOGS($saleItem, Item $item): float
    {
        $costPerUnit = self::getCostPerUnit($item, $saleItem->sale->sale_date ?? now()->toDateString());
        return $costPerUnit * $saleItem->quantity;
    }

    /**
     * Get cost per unit for an item based on purchase history
     *
     * @param Item $item The item model
     * @param string|null $transactionDate The transaction date (optional)
     * @return float
     */
    public static function getCostPerUnit(Item $item,  $transactionDate = null): float
    {
        $date = $transactionDate ?? now()->toDateString();

        $purchaseItems = self::getPurchaseHistory($item->id, $date);

        if ($purchaseItems->isEmpty()) {
            // Try to find linked items if no purchase history found
            $linkedItems = Item::where('linked_item_id', $item->id)->get();

            if ($linkedItems->isNotEmpty()) {
                foreach ($linkedItems as $linkedItem) {
                    $linkedPurchaseItems = self::getPurchaseHistory($linkedItem->id, $date);
                    if ($linkedPurchaseItems->isNotEmpty()) {
                        Log::info("Using purchase history from linked item {$linkedItem->id} for item {$item->id}");
                        return self::calculateWeightedAverageCost($linkedPurchaseItems, $linkedItem);
                    }
                }
            }

            // Try to find sibling items if no linked items found
            $siblingCost = self::getCostFromSiblings($item, $date);
            if ($siblingCost > 0) {
                Log::info("Using cost from sibling items for item {$item->id}");
                return $siblingCost;
            }

            Log::warning("No purchase history found for item {$item->id}, linked items, or siblings before {$date}, using item purchase_price");
            return $item->purchase_price ?? 0;
        }

        return self::calculateWeightedAverageCost($purchaseItems, $item);
    }

    /**
     * Get purchase history for an item before a specific date
     *
     * @param int $itemId The item ID
     * @param string $beforeDate The date before which to get purchases
     * @return \Illuminate\Support\Collection
     */
    public static function getPurchaseHistory(int $itemId, string $beforeDate)
    {
        return DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchase_items.item_id', $itemId)
            ->where('purchase_items.is_stock', true)
            ->where('purchases.purchase_date', '<=', $beforeDate)
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
    }

    /**
     * Calculate weighted average cost from purchase history
     *
     * @param \Illuminate\Support\Collection $purchaseItems
     * @param Item $item
     * @return float
     */
    public static function calculateWeightedAverageCost($purchaseItems, Item $item): float
    {
        $totalCost = 0;
        $totalWeightedQuantity = 0;

        // Get purchase price unit for reference
        $purchasePriceUnit = self::getPurchasePriceUnit($item);

        foreach ($purchaseItems as $purchaseItem) {
            $conversion = self::getUnitConversion($item, $purchaseItem->unit);
            $baseUnitPrice = $purchaseItem->price / $conversion;
            $baseQuantity = $purchaseItem->quantity * $conversion;

            $totalCost += $baseUnitPrice * $baseQuantity;
            $totalWeightedQuantity += $baseQuantity;
        }

        if ($totalWeightedQuantity == 0) {
            Log::warning("Total weighted quantity is zero for item {$item->id}");
            return 0;
        }

        $averageCostPerUnit = $totalCost / $totalWeightedQuantity;

        Log::info("COGS calculation for item {$item->id}", [
            'purchase_items_count' => $purchaseItems->count(),
            'average_cost_per_unit' => $averageCostPerUnit,
            'total_weighted_quantity' => $totalWeightedQuantity,
            'total_cost' => $totalCost,
            'purchase_price_unit' => $purchasePriceUnit ? $purchasePriceUnit->name : 'none'
        ]);

        return $averageCostPerUnit;
    }

    /**
     * Get unit conversion factor
     *
     * @param Item $item
     * @param string|null $unitName
     * @return float
     */
    public static function getUnitConversion(Item $item, ?string $unitName): float
    {
        if (!$unitName) {
            return 1.0;
        }

        $unitData = $item->units()->where('name', $unitName)->first();
        return $unitData ? ($unitData->conversion ?? 1.0) : 1.0;
    }

    /**
     * Get purchase price unit for an item
     *
     * @param Item $item
     * @return ItemUnit|null
     */
    public static function getPurchasePriceUnit(Item $item): ?object
    {
        return $item->units()->where('is_purchase_price', true)->first();
    }

    /**
     * Check if unit is marked as purchase price unit
     *
     * @param Item $item
     * @param string|null $unitName
     * @return bool
     */
    public static function isPurchasePriceUnit(Item $item, ?string $unitName): bool
    {
        if (!$unitName) {
            return false;
        }

        $unitData = $item->units()->where('name', $unitName)->first();
        return $unitData ? ($unitData->is_purchase_price ?? false) : false;
    }

    /**
     * Get purchase price unit information
     *
     * @param Item $item
     * @return array
     */
    public static function getPurchasePriceUnitInfo(Item $item): array
    {
        $purchasePriceUnit = self::getPurchasePriceUnit($item);

        if ($purchasePriceUnit) {
            return [
                'unit_name' => $purchasePriceUnit->name,
                'conversion' => $purchasePriceUnit->conversion,
                'is_purchase_price' => true
            ];
        }

        return [
            'unit_name' => $item->unit ?? 'default',
            'conversion' => 1.0,
            'is_purchase_price' => false
        ];
    }

    /**
     * Validate purchase price units for an item
     *
     * @param Item $item
     * @return array
     */
    public static function validatePurchasePriceUnits(Item $item): array
    {
        $units = $item->units;
        $purchasePriceUnits = $units->where('is_purchase_price', true);

        $result = [
            'has_purchase_price_unit' => $purchasePriceUnits->count() > 0,
            'purchase_price_unit_count' => $purchasePriceUnits->count(),
            'total_units' => $units->count(),
            'purchase_price_units' => $purchasePriceUnits->pluck('name')->toArray(),
            'warnings' => []
        ];

        if ($purchasePriceUnits->count() > 1) {
            $result['warnings'][] = 'Multiple units marked as purchase price unit';
        }

        if ($purchasePriceUnits->count() == 0) {
            $result['warnings'][] = 'No unit marked as purchase price unit';
        }

        return $result;
    }

    /**
     * Get cost per unit from sibling items
     *
     * @param Item $item The item model
     * @param string $transactionDate The transaction date
     * @return float
     */
    public static function getCostFromSiblings(Item $item, string $transactionDate): float
    {
        $siblingCosts = [];

        // Find siblings by same linked_item_id
        if ($item->linked_item_id) {
            $siblings = Item::where('linked_item_id', $item->linked_item_id)
                ->where('id', '!=', $item->id)
                ->limit(10)
                ->get();

            foreach ($siblings as $sibling) {
                $siblingPurchaseItems = self::getPurchaseHistory($sibling->id, $transactionDate);
                if ($siblingPurchaseItems->isNotEmpty()) {
                    $cost = self::calculateWeightedAverageCost($siblingPurchaseItems, $sibling);
                    if ($cost > 0) {
                        $siblingCosts[] = $cost;
                    }
                }
            }
        }

        // Also check if other items are linked TO this item
        $reverseSiblings = Item::where('linked_item_id', $item->id)
            ->limit(10)
            ->get();

        foreach ($reverseSiblings as $sibling) {
            $siblingPurchaseItems = self::getPurchaseHistory($sibling->id, $transactionDate);
            if ($siblingPurchaseItems->isNotEmpty()) {
                $cost = self::calculateWeightedAverageCost($siblingPurchaseItems, $sibling);
                if ($cost > 0) {
                    $siblingCosts[] = $cost;
                }
            }
        }

        // Return average of sibling costs if found
        if (!empty($siblingCosts)) {
            $averageSiblingCost = array_sum($siblingCosts) / count($siblingCosts);
            Log::info("Found sibling costs for item {$item->id} (linked_item_id: {$item->linked_item_id}): " . json_encode($siblingCosts) . ", using average: {$averageSiblingCost}");
            return $averageSiblingCost;
        }

        return 0;
    }

    /**
     * Calculate COGS for multiple items at once
     *
     * @param array $saleItems Array of sale items
     * @return array Array of COGS values indexed by item ID
     */
    public static function calculateBulkCOGS(array $saleItems): array
    {
        $results = [];

        foreach ($saleItems as $saleItem) {
            $item = Item::find($saleItem->item_id);
            if ($item) {
                $results[$saleItem->item_id] = self::calculateCOGS($saleItem, $item);
            }
        }

        return $results;
    }

    /**
     * Get cost per unit for multiple items at once
     *
     * @param array $itemIds Array of item IDs
     * @param string|null $transactionDate The transaction date (optional)
     * @return array Array of cost per unit values indexed by item ID
     */
    public static function getBulkCostPerUnit(array $itemIds, ?string $transactionDate): array
    {
        $results = [];

        foreach ($itemIds as $itemId) {
            $item = Item::find($itemId);
            if ($item) {
                $results[$itemId] = self::getCostPerUnit($item, $transactionDate);
            }
        }

        return $results;
    }

    /**
     * Calculate inventory value based on current cost per unit
     *
     * @param Item $item The item model
     * @param float $quantity The quantity to calculate
     * @param string|null $transactionDate The transaction date (optional)
     * @return float
     */
    public static function calculateInventoryValue(Item $item, float $quantity, ?string $transactionDate ): float
    {
        $costPerUnit = self::getCostPerUnit($item, $transactionDate);
        return $costPerUnit * $quantity;
    }
}
