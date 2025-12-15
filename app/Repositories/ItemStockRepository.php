<?php

namespace App\Repositories;

use Modules\Master\Entities\Item;
use Modules\Transaction\Entities\ItemStock;
use Modules\Transaction\Entities\ItemTransaction;

class ItemStockRepository
{
    public function increase(
        string $referenceUUID,
        string $referenceType,
        string $itemUUID,
        string $locationUUID,
        int $quantity,
        float | null $price,
        string | null $remarks,
    ) {
        if ($price == null) {
            $price = Item::find($itemUUID)->purchase_price;
        }
        ItemTransaction::create([
            'reference_uuid' => $referenceUUID,
            'reference_type' => $referenceType,
            'type' => 'IN',
            'item_uuid' => $itemUUID,
            'location_uuid' => $locationUUID,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $price * $quantity,
            'remarks' => $remarks,
        ]);
        ItemStock::create([
            'item_uuid' => $itemUUID,
            'location_uuid' => $locationUUID,
            'quantity' => $quantity,
            'price' => $price,
            'total' => $price * $quantity,
        ]);
    }

    public function decrease(
        string $referenceUUID,
        string $referenceType,
        string $itemUUID,
        string $locationUUID,
        int $quantity,
        string | null $remarks
    ) {
        $itemCost = 0;
        $earlyQuantity = $quantity;
        while (true) {
            $itemStock = ItemStock::where('item_uuid', $itemUUID)
                ->where('location_uuid', $locationUUID)
                ->where('quantity', '>', 0)
                ->orderBy('created_at')
                ->first();

            if ($itemStock) {
                if ($quantity <= $itemStock->quantity) {
                    $itemStock->quantity -= $quantity;
                    $itemStock->total -= $quantity * $itemStock->price;
                    $itemStock->save();
                    $itemCost += $quantity * $itemStock->price;
                    break;
                } else {
                    $quantity -= $itemStock->quantity;
                    $itemCost += $itemStock->quantity * $itemStock->price;
                    $itemStock->quantity = 0;
                    $itemStock->total = 0;
                    $itemStock->save();
                }
            } else {
                throw new \Exception('Stock is not enough');
            }
        }

        ItemTransaction::create([
            'reference_uuid' => $referenceUUID,
            'reference_type' => $referenceType,
            'location_uuid' => $locationUUID,
            'item_uuid' => $itemUUID,
            'quantity' => $earlyQuantity,
            'price' => $itemCost / $earlyQuantity,
            'total' => $itemCost,
            'type' => 'OUT',
            'remarks' => $remarks,
        ]);
        return $itemCost;
    }

    public function reverse(
        string $referenceUUID,
        string $referenceType,
        string $itemUUID,
        string $locationUUID,
        int $quantity,
        string | null $remarks
    ) {
        $itemTransaction = ItemTransaction::where('reference_uuid', $referenceUUID)
            ->where('reference_type', $referenceType)
            ->where('item_uuid', $itemUUID)
            ->where('location_uuid', $locationUUID)
            ->where('status', 'Active')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($itemTransaction) {
            if ($itemTransaction->type == 'IN') {
                while (true) {
                    $itemStock = ItemStock::where('item_uuid', $itemUUID)
                        ->where('location_uuid', $locationUUID)
                        ->where('quantity', '>', 0)
                        ->orderBy('created_at')
                        ->first();
                    if ($itemStock) {
                        if ($quantity <= $itemStock->quantity) {
                            $itemStock->quantity -= $quantity;
                            $itemStock->total -= $quantity * $itemStock->price;
                            $itemStock->save();
                            break;
                        } else {
                            $quantity -= $itemStock->quantity;
                            $itemStock->quantity = 0;
                            $itemStock->total = 0;
                            $itemStock->save();
                        }
                    } else {
                        throw new \Exception('Stock is not enough');
                    }
                }
            } else {
                $itemStock = ItemStock::create([
                    'item_uuid' => $itemUUID,
                    'location_uuid' => $locationUUID,
                    'quantity' => $quantity,
                    'price' => $itemTransaction->price,
                    'total' => $itemTransaction->price * $itemTransaction->quantity,
                ]);
            }
            $itemTransaction->status = 'Reversed';
            $itemTransaction->save();
        } else {
            throw new \Exception('Transaction not found');
        }
    }
}
