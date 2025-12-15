<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemIn;
use App\Models\ItemInItem;
use App\Models\ItemOut;
use App\Models\ItemOutItem;
use App\Models\ItemBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class ActualStockService
{
    /**
     * Calculate stock based on ItemIn - ItemOut + Purchase - Sale
     * All calculations are done in base unit
     */
    public function calculateActualStock($itemId, $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now();

        // Get item for unit conversion and linked item logic
        $item = Item::with('units', 'linkedItem')->findOrFail($itemId);

        // Tentukan item IDs yang akan diproses (sama seperti RemainingStockDetailsService)
        $itemIds = [$itemId];

        // Jika item yang dipilih adalah anak (memiliki linked_item_id), ambil semua anak dari parent yang sama + parent
        if ($item->linked_item_id) {
            $parentId = $item->linked_item_id;
            $childItems = Item::where('linked_item_id', $parentId)->pluck('id')->toArray();
            $itemIds = array_merge([$parentId], $childItems); // Include parent + all children
        }
        // Jika item yang dipilih adalah parent, ambil dirinya sendiri + semua anak yang linked ke dia
        else {
            $childItems = Item::where('linked_item_id', $itemId)->pluck('id')->toArray();
            if (!empty($childItems)) {
                $itemIds = array_merge([$itemId], $childItems); // Include parent + all children
            }
        }

        $totalItemInQuantity = 0;
        $totalItemOutQuantity = 0;
        $totalPurchaseQuantity = 0;
        $totalSaleQuantity = 0;

        // Hitung untuk setiap item ID
        foreach ($itemIds as $id) {
            // Get item in quantity (positive) - convert to base unit
            $itemInQuantity = ItemInItem::join('item_ins', 'item_in_items.item_in_id', '=', 'item_ins.id')
                ->where('item_in_items.item_id', $id)
                ->where('item_ins.date', '<=', $asOfDate)
                ->whereNull('item_ins.deleted_at')
                ->whereNull('item_in_items.deleted_at')
                ->get()
                ->sum(function ($itemInItem) use ($item) {
                    // Convert quantity to base unit if needed
                    return Item::toBaseQuantity($item, $itemInItem->unit ?? $item->unit, $itemInItem->quantity);
                });

            // Get item out quantity (negative) - convert to base unit
            $itemOutQuantity = ItemOutItem::join('item_outs', 'item_out_items.item_out_id', '=', 'item_outs.id')
                ->where('item_out_items.item_id', $id)
                ->where('item_outs.date', '<=', $asOfDate)
                ->get()
                ->sum(function ($itemOutItem) use ($item) {
                    // Convert quantity to base unit if needed
                    return Item::toBaseQuantity($item, $itemOutItem->unit ?? $item->unit, $itemOutItem->quantity);
                });

            // Get purchase quantity (positive) - convert to base unit
            $purchaseQuantity = PurchaseItem::join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->where('purchase_items.item_id', $id)
                ->where('purchases.purchase_date', '<=', $asOfDate)
                ->whereNull('purchases.deleted_at')
                ->whereNull('purchase_items.deleted_at')
                ->get()
                ->sum(function ($purchaseItem) use ($item) {
                    // Convert quantity to base unit if needed
                    return Item::toBaseQuantity($item, $purchaseItem->unit ?? $item->unit, $purchaseItem->quantity);
                });

            // Get sale quantity (negative) - convert to base unit
            $saleQuantity = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sale_items.item_id', $id)
                ->where('sales.sale_date', '<=', $asOfDate)
                ->get()
                ->sum(function ($saleItem) use ($item) {
                    // Convert quantity to base unit if needed
                    return Item::toBaseQuantity($item, $saleItem->unit ?? $item->unit, $saleItem->quantity);
                });

            $totalItemInQuantity += $itemInQuantity;
            $totalItemOutQuantity += $itemOutQuantity;
            $totalPurchaseQuantity += $purchaseQuantity;
            $totalSaleQuantity += $saleQuantity;
        }

        // Calculate actual stock in base unit
        $actualStock = $totalItemInQuantity - $totalItemOutQuantity + $totalPurchaseQuantity - $totalSaleQuantity;

        return max(0, $actualStock); // Ensure stock doesn't go negative
    }

    /**
     * Calculate stock for multiple items
     */
    public function calculateBulkActualStock($itemIds, $asOfDate = null)
    {
        $results = [];

        foreach ($itemIds as $itemId) {
            $results[$itemId] = $this->calculateActualStock($itemId, $asOfDate);
        }

        return $results;
    }

    /**
     * Get detailed stock calculation breakdown
     */
    public function getStockBreakdown($itemId, $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now();
        
        // Get item for unit conversion and linked item logic
        $item = Item::with('units', 'linkedItem')->findOrFail($itemId);

        // Tentukan item IDs yang akan diproses (sama seperti RemainingStockDetailsService)
        $itemIds = [$itemId];

        // Jika item yang dipilih adalah anak (memiliki linked_item_id), ambil semua anak dari parent yang sama + parent
        if ($item->linked_item_id) {
            $parentId = $item->linked_item_id;
            $childItems = Item::where('linked_item_id', $parentId)->pluck('id')->toArray();
            $itemIds = array_merge([$parentId], $childItems); // Include parent + all children
        }
        // Jika item yang dipilih adalah parent, ambil dirinya sendiri + semua anak yang linked ke dia
        else {
            $childItems = Item::where('linked_item_id', $itemId)->pluck('id')->toArray();
            if (!empty($childItems)) {
                $itemIds = array_merge([$itemId], $childItems); // Include parent + all children
            }
        }

        $totalItemInQuantity = 0;
        $totalItemOutQuantity = 0;
        $totalPurchaseQuantity = 0;
        $totalSaleQuantity = 0;

        // Hitung untuk setiap item ID
        foreach ($itemIds as $id) {
            $itemInQuantity = ItemInItem::join('item_ins', 'item_in_items.item_in_id', '=', 'item_ins.id')
                ->where('item_in_items.item_id', $id)
                ->where('item_ins.date', '<=', $asOfDate)
                ->whereNull('item_ins.deleted_at')
                ->whereNull('item_in_items.deleted_at')
                ->get()
                ->sum(function ($itemInItem) use ($item) {
                    return Item::toBaseQuantity($item, $itemInItem->unit ?? $item->unit, $itemInItem->quantity);
                });

            $itemOutQuantity = ItemOutItem::join('item_outs', 'item_out_items.item_out_id', '=', 'item_outs.id')
                ->where('item_out_items.item_id', $id)
                ->where('item_outs.date', '<=', $asOfDate)
                ->get()
                ->sum(function ($itemOutItem) use ($item) {
                    return Item::toBaseQuantity($item, $itemOutItem->unit ?? $item->unit, $itemOutItem->quantity);
                });

            $purchaseQuantity = PurchaseItem::join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->where('purchase_items.item_id', $id)
                ->where('purchases.purchase_date', '<=', $asOfDate)
                ->whereNull('purchases.deleted_at')
                ->whereNull('purchase_items.deleted_at')
                ->get()
                ->sum(function ($purchaseItem) use ($item) {
                    return Item::toBaseQuantity($item, $purchaseItem->unit ?? $item->unit, $purchaseItem->quantity);
                });

            $saleQuantity = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sale_items.item_id', $id)
                ->where('sales.sale_date', '<=', $asOfDate)
                ->get()
                ->sum(function ($saleItem) use ($item) {
                    return Item::toBaseQuantity($item, $saleItem->unit ?? $item->unit, $saleItem->quantity);
                });

            $totalItemInQuantity += $itemInQuantity;
            $totalItemOutQuantity += $itemOutQuantity;
            $totalPurchaseQuantity += $purchaseQuantity;
            $totalSaleQuantity += $saleQuantity;
        }

        return [
            'item_in' => $totalItemInQuantity,
            'item_out' => $totalItemOutQuantity,
            'purchase' => $totalPurchaseQuantity,
            'sale' => $totalSaleQuantity,
            'actual_stock' => max(0, $totalItemInQuantity - $totalItemOutQuantity + $totalPurchaseQuantity - $totalSaleQuantity),
            'calculation' => "{$totalItemInQuantity} - {$totalItemOutQuantity} + {$totalPurchaseQuantity} - {$totalSaleQuantity}",
            'unit' => 'base unit'
        ];
    }

    /**
     * Get stock movement history for an item
     */
    public function getStockMovements($itemId, $startDate = null, $endDate = null)
    {
        $startDate = $startDate ?? now()->subMonths(3);
        $endDate = $endDate ?? now();
        
        // Get item for unit conversion and linked item logic
        $item = Item::with('units', 'linkedItem')->findOrFail($itemId);

        // Tentukan item IDs yang akan diproses (sama seperti RemainingStockDetailsService)
        $itemIds = [$itemId];

        // Jika item yang dipilih adalah anak (memiliki linked_item_id), ambil semua anak dari parent yang sama + parent
        if ($item->linked_item_id) {
            $parentId = $item->linked_item_id;
            $childItems = Item::where('linked_item_id', $parentId)->pluck('id')->toArray();
            $itemIds = array_merge([$parentId], $childItems); // Include parent + all children
        }
        // Jika item yang dipilih adalah parent, ambil dirinya sendiri + semua anak yang linked ke dia
        else {
            $childItems = Item::where('linked_item_id', $itemId)->pluck('id')->toArray();
            if (!empty($childItems)) {
                $itemIds = array_merge([$itemId], $childItems); // Include parent + all children
            }
        }

        $movements = collect();

        // Proses setiap item ID untuk mengambil semua transaksi
        foreach ($itemIds as $id) {
            // Item In movements
            $itemIns = ItemInItem::join('item_ins', 'item_in_items.item_in_id', '=', 'item_ins.id')
                ->where('item_in_items.item_id', $id)
                ->whereBetween('item_ins.date', [$startDate, $endDate])
                ->select('item_in_items.id', 'item_in_items.quantity', 'item_in_items.unit', 'item_ins.date', 'item_ins.notes')
                ->get()
                ->map(function ($itemInItem) use ($item) {
                    $baseQuantity = Item::toBaseQuantity($item, $itemInItem->unit ?? $item->unit, $itemInItem->quantity);
                    return [
                        'type' => 'item_in',
                        'quantity' => $itemInItem->quantity,
                        'unit' => $itemInItem->unit ?? $item->unit,
                        'base_quantity' => $baseQuantity,
                        'date' => $itemInItem->date,
                        'notes' => $itemInItem->notes,
                        'reference_id' => $itemInItem->id,
                        'effect' => '+' . $baseQuantity
                    ];
                });

            // Item Out movements
            $itemOuts = ItemOutItem::join('item_outs', 'item_out_items.item_out_id', '=', 'item_outs.id')
                ->where('item_out_items.item_id', $id)
                ->whereBetween('item_outs.date', [$startDate, $endDate])
                ->select('item_out_items.id', 'item_out_items.quantity', 'item_out_items.unit', 'item_outs.date', 'item_outs.notes')
                ->get()
                ->map(function ($itemOutItem) use ($item) {
                    $baseQuantity = Item::toBaseQuantity($item, $itemOutItem->unit ?? $item->unit, $itemOutItem->quantity);
                    return [
                        'type' => 'item_out',
                        'quantity' => $itemOutItem->quantity,
                        'unit' => $itemOutItem->unit ?? $item->unit,
                        'base_quantity' => $baseQuantity,
                        'date' => $itemOutItem->date,
                        'notes' => $itemOutItem->notes,
                        'reference_id' => $itemOutItem->id,
                        'effect' => '-' . $baseQuantity
                    ];
                });

            // Purchase movements
            $purchases = PurchaseItem::join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->where('purchase_items.item_id', $id)
                ->whereBetween('purchases.purchase_date', [$startDate, $endDate])
                ->whereNull('purchases.deleted_at')
                ->whereNull('purchase_items.deleted_at')
                ->select(
                    'purchases.id',
                    'purchase_items.quantity',
                    'purchase_items.unit',
                    'purchases.purchase_date',
                    'purchases.notes',
                    'purchases.purchase_number'
                )
                ->get()
                ->map(function ($purchaseItem) use ($item) {
                    $baseQuantity = Item::toBaseQuantity($item, $purchaseItem->unit ?? $item->unit, $purchaseItem->quantity);
                    return [
                        'type' => 'purchase',
                        'quantity' => $purchaseItem->quantity,
                        'unit' => $purchaseItem->unit ?? $item->unit,
                        'base_quantity' => $baseQuantity,
                        'date' => $purchaseItem->purchase_date,
                        'notes' => $purchaseItem->notes ?? "Purchase #{$purchaseItem->purchase_number}",
                        'reference_id' => $purchaseItem->id,
                        'effect' => '+' . $baseQuantity
                    ];
                });

            // Sale movements
            $sales = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sale_items.item_id', $id)
                ->whereBetween('sales.sale_date', [$startDate, $endDate])
                ->select(
                    'sales.id',
                    'sale_items.quantity',
                    'sale_items.unit',
                    'sales.sale_date',
                    'sales.notes',
                    'sales.sale_number'
                )
                ->get()
                ->map(function ($saleItem) use ($item) {
                    $baseQuantity = Item::toBaseQuantity($item, $saleItem->unit ?? $item->unit, $saleItem->quantity);
                    return [
                        'type' => 'sale',
                        'quantity' => $saleItem->quantity,
                        'unit' => $saleItem->unit ?? $item->unit,
                        'base_quantity' => $baseQuantity,
                        'date' => $saleItem->sale_date,
                        'notes' => $saleItem->notes ?? "Sale #{$saleItem->sale_number}",
                        'reference_id' => $saleItem->id,
                        'effect' => '-' . $baseQuantity
                    ];
                });

            // Combine movements for this item ID
            $movements = $movements
                ->concat($itemIns)
                ->concat($itemOuts)
                ->concat($purchases)
                ->concat($sales);
        }

        // Sort all movements by date
        $movements = $movements->sortBy('date');

        return $movements->values();
    }

    /**
     * Calculate weighted average price from actual transactions
     * Based on Purchase transactions with FIFO-like approach
     */
    private function calculateWeightedAveragePrice($itemId, $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now();
        
        // Get item for unit conversion and linked item logic
        $item = Item::with('units', 'linkedItem')->findOrFail($itemId);

        // Tentukan item IDs yang akan diproses (sama seperti perhitungan stock)
        $itemIds = [$itemId];

        // Jika item yang dipilih adalah anak (memiliki linked_item_id), ambil semua anak dari parent yang sama + parent
        if ($item->linked_item_id) {
            $parentId = $item->linked_item_id;
            $childItems = Item::where('linked_item_id', $parentId)->pluck('id')->toArray();
            $itemIds = array_merge([$parentId], $childItems);
        }
        // Jika item yang dipilih adalah parent, ambil dirinya sendiri + semua anak yang linked ke dia
        else {
            $childItems = Item::where('linked_item_id', $itemId)->pluck('id')->toArray();
            if (!empty($childItems)) {
                $itemIds = array_merge([$itemId], $childItems);
            }
        }

        $totalValue = 0;
        $totalQuantity = 0;

        // Hitung weighted average dari purchase transactions
        foreach ($itemIds as $id) {
            $purchaseItems = PurchaseItem::join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->where('purchase_items.item_id', $id)
                ->where('purchases.purchase_date', '<=', $asOfDate)
                ->where('purchase_items.is_stock', true)
                ->whereNull('purchases.deleted_at')
                ->whereNull('purchase_items.deleted_at')
                ->orderBy('purchases.purchase_date', 'desc') // Latest first for better average
                ->select('purchase_items.quantity', 'purchase_items.unit', 'purchase_items.price', 'purchase_items.total', 'purchase_items.subtotal')
                ->get();

            foreach ($purchaseItems as $purchaseItem) {
                // Convert quantity to base unit
                $baseQuantity = Item::toBaseQuantity($item, $purchaseItem->unit ?? $item->unit, $purchaseItem->quantity);
                
                if ($baseQuantity > 0) {
                    // Use subtotal (before tax) if available, otherwise use total, otherwise calculate from price * quantity  
                    $totalCostForPurchase = $purchaseItem->subtotal ?? ($purchaseItem->total ?? ($purchaseItem->price * $purchaseItem->quantity));
                    $pricePerBaseUnit = $totalCostForPurchase / $baseQuantity;
                    
                    $totalValue += $totalCostForPurchase; // Use total cost directly
                    $totalQuantity += $baseQuantity;
                }
            }
        }

        // Fallback ke ItemIn jika tidak ada purchase data
        if ($totalQuantity == 0) {
            foreach ($itemIds as $id) {
                $itemInItems = ItemInItem::join('item_ins', 'item_in_items.item_in_id', '=', 'item_ins.id')
                    ->where('item_in_items.item_id', $id)
                    ->where('item_ins.date', '<=', $asOfDate)
                    ->orderBy('item_ins.date', 'desc')
                    ->select('item_in_items.quantity', 'item_in_items.unit', 'item_in_items.price')
                    ->get();

                foreach ($itemInItems as $itemInItem) {
                    $baseQuantity = Item::toBaseQuantity($item, $itemInItem->unit ?? $item->unit, $itemInItem->quantity);
                    
                    if ($baseQuantity > 0 && $itemInItem->price > 0) {
                        // Calculate price per base unit: total cost / base quantity  
                        $totalCostForTransaction = $itemInItem->price * $itemInItem->quantity;
                        $pricePerBaseUnit = $totalCostForTransaction / $baseQuantity;
                        
                        $totalValue += $totalCostForTransaction; // Use total cost directly
                        $totalQuantity += $baseQuantity;
                    }
                }
            }
        }

        // Calculate weighted average price
        $avgPrice = $totalQuantity > 0 ? ($totalValue / $totalQuantity) : 0;
        
        // Fallback to item's default price if no transaction data
        if ($avgPrice == 0) {
            $avgPrice = $item->price ?? 0;
        }

        return $avgPrice; // Return exact price without rounding
    }

    /**
     * Get detailed price calculation breakdown
     */
    public function getPriceBreakdown($itemId, $asOfDate = null)
    {
        $asOfDate = $asOfDate ?? now();
        
        // Get item for unit conversion and linked item logic
        $item = Item::with('units', 'linkedItem')->findOrFail($itemId);

        // Tentukan item IDs yang akan diproses
        $itemIds = [$itemId];

        if ($item->linked_item_id) {
            $parentId = $item->linked_item_id;
            $childItems = Item::where('linked_item_id', $parentId)->pluck('id')->toArray();
            $itemIds = array_merge([$parentId], $childItems);
        } else {
            $childItems = Item::where('linked_item_id', $itemId)->pluck('id')->toArray();
            if (!empty($childItems)) {
                $itemIds = array_merge([$itemId], $childItems);
            }
        }

        $purchaseData = [];
        $itemInData = [];
        $totalPurchaseValue = 0;
        $totalPurchaseQuantity = 0;
        $totalItemInValue = 0;
        $totalItemInQuantity = 0;

        // Collect purchase transaction data
        foreach ($itemIds as $id) {
            $purchaseItems = PurchaseItem::join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
                ->where('purchase_items.item_id', $id)
                ->where('purchases.purchase_date', '<=', $asOfDate)
                ->where('purchase_items.is_stock', true)
                ->whereNull('purchases.deleted_at')
                ->whereNull('purchase_items.deleted_at')
                ->orderBy('purchases.purchase_date', 'desc')
                ->select('purchase_items.quantity', 'purchase_items.unit', 'purchase_items.price', 'purchase_items.total', 'purchase_items.subtotal', 'purchases.purchase_date')
                ->get();

            foreach ($purchaseItems as $purchaseItem) {
                $baseQuantity = Item::toBaseQuantity($item, $purchaseItem->unit ?? $item->unit, $purchaseItem->quantity);
                if ($baseQuantity > 0) {
                    // Use subtotal (before tax) if available, otherwise use total, otherwise calculate from price * quantity
                    $totalCostForPurchase = $purchaseItem->subtotal ?? ($purchaseItem->total ?? ($purchaseItem->price * $purchaseItem->quantity));
                    $pricePerBaseUnit = $totalCostForPurchase / $baseQuantity;
                    
                    $totalPurchaseValue += $totalCostForPurchase;
                    $totalPurchaseQuantity += $baseQuantity;
                    $purchaseData[] = [
                        'date' => $purchaseItem->purchase_date,
                        'quantity' => $baseQuantity, // Quantity in base unit for calculation
                        'price' => $pricePerBaseUnit, // Price per base unit (for calculation)
                        'display_price' => $purchaseItem->price, // Original price per purchase unit (for display)
                        'display_unit' => $purchaseItem->unit ?? $item->unit, // Original unit (for display)
                        'display_quantity' => $purchaseItem->quantity, // Original quantity (for display)
                        'total_cost' => $totalCostForPurchase, // Total cost for this purchase
                        'value' => $totalCostForPurchase // Same as total cost
                    ];
                }
            }
        }

        // Collect ItemIn data if no purchase data
        if ($totalPurchaseQuantity == 0) {
            foreach ($itemIds as $id) {
                $itemInItems = ItemInItem::join('item_ins', 'item_in_items.item_in_id', '=', 'item_ins.id')
                    ->where('item_in_items.item_id', $id)
                    ->where('item_ins.date', '<=', $asOfDate)
                    ->whereNull('item_ins.deleted_at')
                    ->whereNull('item_in_items.deleted_at')
                    ->orderBy('item_ins.date', 'desc')
                    ->select('item_in_items.quantity', 'item_in_items.unit', 'item_in_items.price', 'item_ins.date')
                    ->get();

                foreach ($itemInItems as $itemInItem) {
                    $baseQuantity = Item::toBaseQuantity($item, $itemInItem->unit ?? $item->unit, $itemInItem->quantity);
                    if ($baseQuantity > 0 && $itemInItem->price > 0) {
                        // Calculate total cost and price per base unit
                        $totalCostForTransaction = $itemInItem->price * $itemInItem->quantity;
                        $pricePerBaseUnit = $totalCostForTransaction / $baseQuantity;
                        
                        $totalItemInValue += $totalCostForTransaction;
                        $totalItemInQuantity += $baseQuantity;
                        $itemInData[] = [
                            'date' => $itemInItem->date,
                            'quantity' => $baseQuantity, // Quantity in base unit for calculation
                            'price' => $pricePerBaseUnit, // Price per base unit (for calculation)
                            'display_price' => $itemInItem->price, // Original price per item_in unit (for display)
                            'display_unit' => $itemInItem->unit ?? $item->unit, // Original unit (for display)
                            'display_quantity' => $itemInItem->quantity, // Original quantity (for display)
                            'total_cost' => $totalCostForTransaction, // Total cost for this transaction
                            'value' => $totalCostForTransaction // Same as total cost
                        ];
                    }
                }
            }
        }

        $avgPrice = 0;
        $sourceType = 'none';

        if ($totalPurchaseQuantity > 0) {
            $avgPrice = $totalPurchaseValue / $totalPurchaseQuantity;
            $sourceType = 'purchase';
        } elseif ($totalItemInQuantity > 0) {
            $avgPrice = $totalItemInValue / $totalItemInQuantity;
            $sourceType = 'item_in';
        } else {
            $avgPrice = $item->price ?? 0;
            $sourceType = 'item_default';
        }

        return [
            'average_price' => $avgPrice, // Don't round here to keep precision
            'source_type' => $sourceType,
            'purchase_data' => $purchaseData,
            'item_in_data' => $itemInData,
            'total_purchase_value' => $totalPurchaseValue,
            'total_purchase_quantity' => $totalPurchaseQuantity,
            'total_item_in_value' => $totalItemInValue,
            'total_item_in_quantity' => $totalItemInQuantity,
            'item_default_price' => $item->price ?? 0,
            'calculation_method' => 'weighted_average'
        ];
    }

    /**
     * Get weighted average price in display format using purchase price unit (is_purchase_price = true)
     * This shows the price in the format clients are familiar with using the designated purchase unit
     */
    public function getDisplayWeightedAveragePrice($itemId, $asOfDate = null)
    {
        $breakdown = $this->getPriceBreakdown($itemId, $asOfDate);
        $item = Item::with('units')->findOrFail($itemId);
        
        // Find the purchase price unit (is_purchase_price = true)
        $purchasePriceUnit = $item->units->where('is_purchase_price', true)->first();
        
        // If no purchase price unit is found, fall back to most common unit logic
        if (!$purchasePriceUnit) {
            return $this->getDisplayWeightedAveragePriceWithCommonUnit($itemId, $asOfDate);
        }
        
        $displayUnit = $purchasePriceUnit->name;
        
        // If we have purchase data, calculate weighted average in purchase price unit format
        if (!empty($breakdown['purchase_data'])) {
            $purchaseData = collect($breakdown['purchase_data']);
            
            // Calculate price per base unit from total cost / total base quantity
            $totalCost = $breakdown['total_purchase_value'];
            $totalBaseQuantity = $breakdown['total_purchase_quantity'];
            
            if ($totalBaseQuantity > 0) {
                $pricePerBaseUnit = $totalCost / $totalBaseQuantity;
                
                // Convert price per base unit to price per purchase price unit
                // Hitung berapa base unit dalam 1 purchase price unit
                $baseUnitsPerDisplayUnit = Item::toBaseQuantity($item, $displayUnit, 1);
                $displayPrice = $pricePerBaseUnit * $baseUnitsPerDisplayUnit;
                
                return [
                    'display_price' => round($displayPrice, 0),
                    'display_unit' => $displayUnit,
                    'source' => 'weighted_average_purchase',
                    'date' => $purchaseData->first()['date'], // Latest date for reference
                    'transaction_count' => $purchaseData->count()
                ];
            }
        }
        
        // If we have item_in data, calculate weighted average in purchase price unit format
        if (!empty($breakdown['item_in_data'])) {
            $itemInData = collect($breakdown['item_in_data']);
            
            // Calculate price per base unit from total cost / total base quantity
            $totalCost = $breakdown['total_item_in_value'];
            $totalBaseQuantity = $breakdown['total_item_in_quantity'];
            
            if ($totalBaseQuantity > 0) {
                $pricePerBaseUnit = $totalCost / $totalBaseQuantity;
                
                // Convert price per base unit to price per purchase price unit
                $baseUnitsPerDisplayUnit = Item::toBaseQuantity($item, $displayUnit, 1);
                $displayPrice = $pricePerBaseUnit * $baseUnitsPerDisplayUnit;
                
                return [
                    'display_price' => round($displayPrice, 0),
                    'display_unit' => $displayUnit,
                    'source' => 'weighted_average_item_in',
                    'date' => $itemInData->first()['date'], // Latest date for reference
                    'transaction_count' => $itemInData->count()
                ];
            }
        }
        
        // Fallback to item default price in purchase price unit
        $baseUnitsPerDisplayUnit = Item::toBaseQuantity($item, $displayUnit, 1);
        $displayPrice = ($item->price ?? 0) * $baseUnitsPerDisplayUnit;
        
        return [
            'display_price' => round($displayPrice, 0),
            'display_unit' => $displayUnit,
            'source' => 'item_default',
            'date' => null,
            'transaction_count' => 0
        ];
    }

    /**
     * Fallback method for items without purchase price unit - uses most common unit logic
     */
    private function getDisplayWeightedAveragePriceWithCommonUnit($itemId, $asOfDate = null)
    {
        $breakdown = $this->getPriceBreakdown($itemId, $asOfDate);
        
        // If we have purchase data, calculate weighted average in display format
        if (!empty($breakdown['purchase_data'])) {
            $purchaseData = collect($breakdown['purchase_data']);
            $item = Item::with('units')->findOrFail($itemId);
            
            // Find most frequently used unit in purchases
            $unitFrequency = $purchaseData->groupBy('display_unit')->map->count();
            $mostCommonUnit = $unitFrequency->keys()->sortByDesc(function($unit) use ($unitFrequency) {
                return $unitFrequency[$unit];
            })->first();
            
            // Calculate price per base unit from total cost / total base quantity
            $totalCost = $breakdown['total_purchase_value']; // Total cost dari semua transaksi
            $totalBaseQuantity = $breakdown['total_purchase_quantity']; // Total base quantity
            
            if ($totalBaseQuantity > 0) {
                $pricePerBaseUnit = $totalCost / $totalBaseQuantity;
                
                // Convert price per base unit to price per most common display unit
                // Hitung berapa base unit dalam 1 display unit
                $baseUnitsPerDisplayUnit = Item::toBaseQuantity($item, $mostCommonUnit, 1);
                $displayPrice = $pricePerBaseUnit * $baseUnitsPerDisplayUnit;
                
                return [
                    'display_price' => round($displayPrice, 0),
                    'display_unit' => $mostCommonUnit,
                    'source' => 'weighted_average_purchase',
                    'date' => $purchaseData->first()['date'], // Latest date for reference
                    'transaction_count' => $purchaseData->count()
                ];
            }
        }
        
        // If we have item_in data, calculate weighted average in display format
        if (!empty($breakdown['item_in_data'])) {
            $itemInData = collect($breakdown['item_in_data']);
            $item = Item::with('units')->findOrFail($itemId);
            
            // Find most frequently used unit in item_in transactions
            $unitFrequency = $itemInData->groupBy('display_unit')->map->count();
            $mostCommonUnit = $unitFrequency->keys()->sortByDesc(function($unit) use ($unitFrequency) {
                return $unitFrequency[$unit];
            })->first();
            
            // Calculate price per base unit from total cost / total base quantity
            $totalCost = $breakdown['total_item_in_value']; // Total cost dari semua transaksi item_in
            $totalBaseQuantity = $breakdown['total_item_in_quantity']; // Total base quantity
            
            if ($totalBaseQuantity > 0) {
                $pricePerBaseUnit = $totalCost / $totalBaseQuantity;
                
                // Convert price per base unit to price per most common display unit
                $baseUnitsPerDisplayUnit = Item::toBaseQuantity($item, $mostCommonUnit, 1);
                $displayPrice = $pricePerBaseUnit * $baseUnitsPerDisplayUnit;
                
                return [
                    'display_price' => round($displayPrice, 0),
                    'display_unit' => $mostCommonUnit,
                    'source' => 'weighted_average_item_in',
                    'date' => $itemInData->first()['date'], // Latest date for reference
                    'transaction_count' => $itemInData->count()
                ];
            }
        }
        
        // Fallback to item default price
        $item = Item::findOrFail($itemId);
        return [
            'display_price' => $item->price ?? 0,
            'display_unit' => $item->unit,
            'source' => 'item_default',
            'date' => null,
            'transaction_count' => 0
        ];
    }

    /**
     * Get comprehensive pricing information for frontend display
     */
    public function getPricingInfoForDisplay($itemId, $asOfDate = null)
    {
        $breakdown = $this->getPriceBreakdown($itemId, $asOfDate);
        $displayPrice = $this->getDisplayWeightedAveragePrice($itemId, $asOfDate);
        
        return [
            'calculated_average_price' => $breakdown['average_price'], // For internal calculation (per base unit)
            'display_price' => $displayPrice['display_price'], // For client display (weighted average in purchase unit)
            'display_unit' => $displayPrice['display_unit'],
            'price_source' => $displayPrice['source'],
            'last_price_date' => $displayPrice['date'],
            'calculation_method' => 'weighted_average',
            'display_calculation_method' => 'weighted_average_in_purchase_units',
            'total_transactions' => count($breakdown['purchase_data']) + count($breakdown['item_in_data']),
            'purchase_count' => count($breakdown['purchase_data']),
            'item_in_count' => count($breakdown['item_in_data']),
            'transaction_count_for_display' => $displayPrice['transaction_count'] ?? 0
        ];
    }

    /**
     * Compare actual stock with batch stock
     */
    public function compareWithBatchStock($itemId)
    {
        $actualStock = $this->calculateActualStock($itemId);

        $batchStock = ItemBatch::where('item_id', $itemId)
            ->sum('quantity');

        return [
            'actual_stock' => $actualStock,
            'batch_stock' => $batchStock,
            'difference' => $actualStock - $batchStock,
            'is_matching' => $actualStock == $batchStock
        ];
    }

    /**
     * Update StockReportService to use this calculation
     */
    public function getItemStockWithActualCalculation($item, $asOfDate = null, $useDisplayPrice = false)
    {
        $actualStock = $this->calculateActualStock($item->id, $asOfDate);

        // Format stock using Item's formatQuantity method
        $unitReport1 = $item->unit_report_1;
        $unitReport2 = $item->unit_report_2;

        $stock = Item::formatQuantity($actualStock, $item->unit, $unitReport1, $unitReport2, $item->units);

        if ($useDisplayPrice) {
            // Get display pricing info for frontend
            $pricingInfo = $this->getPricingInfoForDisplay($item->id, $asOfDate);
            
            // Use the same price calculation basis for both display and total
            // The calculated_average_price is per base unit, so use it for total calculation
            $avgPricePerBaseUnit = $pricingInfo['calculated_average_price'];
            
            return [
                'stock' => $stock,
                'stock_quantity' => $actualStock,
                'avg_price' => $avgPricePerBaseUnit, // Price per base unit for calculation consistency
                'display_price' => $pricingInfo['display_price'],
                'display_unit' => $pricingInfo['display_unit'],
                'price_source' => $pricingInfo['price_source'],
                'total' => $avgPricePerBaseUnit * $actualStock // Use consistent base unit price
            ];
        } else {
            // Calculate weighted average price from actual transactions (original behavior)
            $avgPrice = $this->calculateWeightedAveragePrice($item->id, $asOfDate);

            return [
                'stock' => $stock,
                'stock_quantity' => $actualStock,
                'avg_price' => $avgPrice,
                'total' => $avgPrice * $actualStock
            ];
        }
    }

    /**
     * Reconcile batch stock with actual stock calculation
     */
    public function reconcileBatchStock($itemId, $createJournalEntry = true)
    {
        $comparison = $this->compareWithBatchStock($itemId);

        if ($comparison['is_matching']) {
            return [
                'status' => 'no_action_needed',
                'message' => 'Batch stock already matches actual stock',
                'actual_stock' => $comparison['actual_stock'],
                'batch_stock' => $comparison['batch_stock']
            ];
        }

        $difference = $comparison['difference'];

        if ($createJournalEntry && $difference != 0) {
            // Create inventory adjustment journal entry
            $adjustmentType = $difference > 0 ? 'IN' : 'OUT';
            $adjustmentQuantity = abs($difference);

            // This would create an ItemIn or ItemOut record to reconcile the difference
            if ($adjustmentType === 'IN') {
                $this->createStockAdjustmentIn($itemId, $adjustmentQuantity, 'Stock reconciliation adjustment');
            } else {
                $this->createStockAdjustmentOut($itemId, $adjustmentQuantity, 'Stock reconciliation adjustment');
            }
        }

        return [
            'status' => 'reconciled',
            'message' => "Stock reconciled with {$adjustmentType} adjustment of {$adjustmentQuantity}",
            'adjustment_type' => $adjustmentType,
            'adjustment_quantity' => $adjustmentQuantity,
            'previous_batch_stock' => $comparison['batch_stock'],
            'actual_stock' => $comparison['actual_stock'],
            'difference' => $difference
        ];
    }

    /**
     * Create stock adjustment IN record
     */
    protected function createStockAdjustmentIn($itemId, $quantity, $notes)
    {
        // Get item details for pricing
        $item = Item::findOrFail($itemId);

        // Use average price from existing batches or default price
        $avgPrice = ItemBatch::where('item_id', $itemId)
            ->avg('price') ?? $item->price ?? 0;

        return ItemIn::create([
            'item_id' => $itemId,
            'quantity' => $quantity,
            'price' => $avgPrice,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Create stock adjustment OUT record
     */
    protected function createStockAdjustmentOut($itemId, $quantity, $notes)
    {
        return ItemOut::create([
            'item_id' => $itemId,
            'quantity' => $quantity,
            'notes' => $notes,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get stock variance report
     */
    public function getStockVarianceReport($threshold = 0)
    {
        $items = Item::where('is_non_stock', false)
            ->where('is_linked', false)
            ->with('units')
            ->get();

        $variances = [];

        foreach ($items as $item) {
            $comparison = $this->compareWithBatchStock($item->id);

            if (abs($comparison['difference']) > $threshold) {
                $stockData = $this->getItemStockWithActualCalculation($item);

                $variances[] = [
                    'item' => $item,
                    'batch_stock' => $comparison['batch_stock'],
                    'actual_stock' => $comparison['actual_stock'],
                    'difference' => $comparison['difference'],
                    'percentage_variance' => $comparison['batch_stock'] != 0
                        ? round(($comparison['difference'] / $comparison['batch_stock']) * 100, 2)
                        : 0,
                    'actual_stock_formatted' => $stockData['stock'],
                    'estimated_value_impact' => $stockData['avg_price'] * $comparison['difference']
                ];
            }
        }

        // Sort by absolute difference descending
        usort($variances, function ($a, $b) {
            return abs($b['difference']) - abs($a['difference']);
        });

        return $variances;
    }
}
