# ActualStockService - Frontend Integration Guide

## Overview
The `ActualStockService` has been enhanced to provide pricing information using **weighted average prices in purchase units** - giving clients both accuracy and familiarity.

## Key Changes: Latest → Weighted Average

### ❌ Old Method (Latest Price)
- Showed price from most recent transaction only
- Could be misleading if latest transaction had unusual price
- Example: Shows "110 per box" (from latest) vs actual average of "107 per box"

### ✅ New Method (Weighted Average Display Price) 
- Calculates weighted average in original purchase units
- More accurate representation of true purchase costs
- Example: Shows "107 per box" (weighted average of all purchases)
- Still displays in familiar purchase units (not base units)

## Key Methods for Frontend

### 1. `getPricingInfoForDisplay($itemId, $asOfDate = null)`
Returns comprehensive pricing information optimized for frontend display.

**Response:**
```php
[
    'calculated_average_price' => 10.7,           // For internal calculations (per base unit)
    'display_price' => 107,                      // Show this to clients (weighted avg in purchase units)
    'display_unit' => 'box',                     // Unit clients understand  
    'price_source' => 'weighted_average_purchase', // Source of display price
    'last_price_date' => '2024-01-20',          // Latest transaction date for reference
    'calculation_method' => 'weighted_average',
    'display_calculation_method' => 'weighted_average_in_purchase_units',
    'total_transactions' => 3,
    'purchase_count' => 3,
    'item_in_count' => 0,
    'transaction_count_for_display' => 3         // Number of transactions used for display price
]
```

### 2. `getItemStockWithActualCalculation($item, $asOfDate = null, $useDisplayPrice = false)`
Enhanced stock calculation with display price support.

**With `$useDisplayPrice = true`:**
```php
[
    'stock' => '70 pcs',
    'stock_quantity' => 70,
    'avg_price' => 10.57,          // For value calculations
    'display_price' => 120,        // Show this to clients
    'display_unit' => 'box',       // Unit clients understand
    'price_source' => 'latest_purchase',
    'total' => 739.90             // Accurate total value
]
```

### 3. `getPriceBreakdown($itemId, $asOfDate = null)`
Enhanced breakdown with display-friendly information.

**Purchase/ItemIn data now includes:**
```php
[
    'date' => '2024-01-15',
    'quantity' => 50,              // Base unit quantity (for calculations)
    'price' => 10,                 // Price per base unit (for calculations)
    'display_price' => 100,        // Original purchase price (for display)
    'display_unit' => 'box',       // Original purchase unit (for display)
    'display_quantity' => 5,       // Original purchase quantity (for display)
    'total_cost' => 500,           // Total cost of transaction
    'value' => 500                 // Same as total cost
]
```

## Frontend Display Best Practices

### ✅ DO: Show Client-Friendly Information
- Display: "120 per box" (not "10.57 per pcs")
- Show: "Latest purchase price" as source
- Use: Original purchase units and quantities

### ✅ DO: Use Calculated Values for Totals
- Total stock value: Use `calculated_average_price × stock_quantity`
- This ensures accurate financial calculations

### ❌ DON'T: Show Base Unit Prices to Clients
- Don't show: "10.57 per pcs" (confusing)
- Don't show: Internal calculation details

## Example Frontend Implementation

```javascript
// Get pricing info
const pricingInfo = await api.getPricingInfoForDisplay(itemId);

// Display to client
displayPrice.text = `${pricingInfo.display_price} per ${pricingInfo.display_unit}`;
priceSource.text = `Source: ${pricingInfo.price_source}`;

// Use for calculations
totalValue = pricingInfo.calculated_average_price * stockQuantity;
```

## Benefits

1. **Client Clarity**: Clients see familiar purchase prices (e.g., "120 per box")
2. **Accurate Calculations**: Internal calculations use proper weighted averages
3. **No Conversion Confusion**: No mental math required for clients
4. **Transparent Sourcing**: Shows where prices come from
5. **Backward Compatible**: Existing code continues to work

## Migration Notes

- Existing `getItemStockWithActualCalculation()` calls work unchanged
- Add `$useDisplayPrice = true` parameter when you want display-friendly data
- New methods provide additional frontend-optimized information
- All calculations remain mathematically correct
