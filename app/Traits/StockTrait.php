<?php

namespace App\Traits;

use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\ItemTransaction;
use Exception;

trait StockTrait
{
    public function increaseStockItem($item, $warehouseId, $referenceId = null, $referenceType = null, $remarks = null, $date)
    {
        $itemMaster = Item::find($item->item_id);
        if ($itemMaster->is_linked) {
            $itemMaster = Item::find($itemMaster->linked_item_id);
        }

        ItemTransaction::create([
            'date'  => $date,
            'item_id' => $itemMaster->id,
            'warehouse_id' => $warehouseId,
            'transaction_type' => 'IN',
            'quantity' => $item->quantity,
            'unit_price' => $item->price,
            'total' => $item->quantity * $item->price,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'remarks' => $remarks,
            'linking_item_id' => $item->item_id,
        ]);

        if ($item->item->is_stock_managed) {
            if ($item->item->stock_method == 'FIFO') {
                ItemBatch::create([
                    'item_id' => $itemMaster->id,
                    'warehouse_id' => $warehouseId,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);
            } else {
                $itemBatch = ItemBatch::where('item_id', $itemMaster->id)
                    ->where('warehouse_id', $warehouseId)
                    ->orderBy('created_at', 'asc')
                    ->first();
                if ($itemBatch) {
                    $previousPrice = $itemBatch->price * $itemBatch->quantity;
                    $newPrice = $item->price * $item->quantity;
                    $averagePrice = ($previousPrice + $newPrice) / ($itemBatch->quantity + $item->quantity);
                    $itemBatch->price = $averagePrice;
                    $itemBatch->quantity += $item->quantity;
                    $itemBatch->save();
                } else {
                    ItemBatch::create([
                        'item_id' => $itemMaster->id,
                        'warehouse_id' => $warehouseId,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ]);
                }
            }
        }
    }

    public function decreaseStockItem($item, $warehouseId, $referenceId = null, $referenceType = null, $remarks = null, $date)
    {

        $itemMaster = Item::find($item->item_id);
        if ($itemMaster->is_linked) {
            $itemMaster = Item::find($itemMaster->linked_item_id);
        }
        $costs = 0;

        if ($item->item->is_stock_managed) {
            $quantity = $item->quantity;
            if ($item->item->stock_method == 'FIFO') {
                while (true) {
                    $itemBatch = ItemBatch::query()
                        ->where('item_id', $itemMaster->id)
                        ->where('warehouse_id', $warehouseId)
                        ->where('quantity', '>', 0)
                        ->orderBy('created_at', 'asc')
                        ->first();
                    if ($itemBatch) {
                        if ($quantity > $itemBatch->quantity) {
                            $costs += $itemBatch->quantity * $itemBatch->price;
                            $quantity -= $itemBatch->quantity;
                            $itemBatch->quantity = 0;
                            $itemBatch->save();
                        } else {
                            $costs += $quantity * $itemBatch->price;
                            $itemBatch->quantity -= $quantity;
                            $itemBatch->save();
                            break;
                        }
                    } else {
                        throw new Exception("Stok tidak cukup untuk item {$item->item->code} {$item->item->name}");
                    }
                }
            } else {
                $itemBatches = ItemBatch::query()
                    ->where('item_id', $itemMaster->id)
                    ->where('warehouse_id', $warehouseId)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc')
                    ->get();
                foreach ($itemBatches as $itemBatch) {
                    if ($quantity > $itemBatch->quantity) {
                        $costs += $itemBatch->quantity * $itemBatch->price;
                        $quantity -= $itemBatch->quantity;
                        $itemBatch->delete();
                    } else {
                        $costs += $quantity * $itemBatch->price;
                        $itemBatch->quantity -= $quantity;
                        $itemBatch->save();
                        $quantity = 0;
                        break;
                    }
                }
                if ($quantity > 0) {
                    throw new Exception("Stok tidak cukup untuk item {$item->item->code} {$item->item->name}");
                }
            }
        }
        ItemTransaction::create([
            'date' => $date,
            'item_id' => $itemMaster->id,
            'warehouse_id' => $warehouseId,
            'transaction_type' => 'OUT',
            'quantity' => $item->quantity,
            'unit_price' => $costs / $item->quantity,
            'total' => $costs,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'remarks' => $remarks,
            'linking_item_id' => $item->item_id,
        ]);
    }

    // public function appendBatch($items, $to)
    // {
    //     foreach ($items as $item) {
    //         $masterItem = Item::find($itemMaster->id);
    //         if ($masterItem->is_stock_managed == 0) {
    //             continue;
    //         }
    //         if ($masterItem->stock_method === 'FIFO') {
    //             ItemBatch::create([
    //                 'item_id' => $itemMaster->id,
    //                 'warehouse_id' => $to,
    //                 'quantity' => $item->quantity,
    //                 'price' => $item->price,
    //                 'insert_from_model' => $item->insert_from_model,
    //                 'insert_from_id' => $item->insert_from_id,
    //             ]);
    //         } else {
    //             $itemBatch = ItemBatch::where('item_id', $itemMaster->id)
    //                 ->where('warehouse_id', $to)
    //                 ->orderBy('id', 'desc')
    //                 ->first();
    //             if ($itemBatch) {
    //                 $newSubtotal = $item->quantity * $item->price;
    //                 $oldSubtotal = $itemBatch->quantity * $itemBatch->price;
    //                 $averagePrice = ($newSubtotal + $oldSubtotal) / ($item->quantity + $itemBatch->quantity);
    //                 $itemBatch->quantity += $item->quantity;
    //                 $itemBatch->price = $averagePrice;
    //                 $itemBatch->save();
    //             } else {
    //                 ItemBatch::create([
    //                     'item_id' => $itemMaster->id,
    //                     'warehouse_id' => $to,
    //                     'quantity' => $item->quantity,
    //                     'price' => $item->price,
    //                     'insert_from_model' => $item->insert_from_model,
    //                     'insert_from_id' => $item->insert_from_id,
    //                 ]);
    //             }
    //         }
    //     }
    // }



    // public function rollbackBatch($insertFromModel, $insertFromId)
    // {
    //     $itemBatches = ItemBatch::where('insert_from_model', $insertFromModel)
    //         ->where('insert_from_id', $insertFromId)
    //         ->get();
    //     foreach ($itemBatches as $itemBatch) {
    //         $itemBatch->delete();
    //     }
    // }

    // public function usingBatch($items, $warehouseId)
    // {
    //     $hpp = 0;

    //     foreach ($items as $item) {
    //         $masterItem = Item::find($itemMaster->id);
    //         if ($masterItem->is_stock_managed == 0) {
    //             continue;
    //         }
    //         $need = $item->quantity;
    //         $itemHpp = 0;

    //         while ($need > 0) {
    //             $itemBatch = ItemBatch::where('item_id', $item->id)
    //                 ->where('quantity', '>', 0)
    //                 ->where('warehouse_id', $warehouseId)
    //                 ->orderBy('id')
    //                 ->first();

    //             if (!$itemBatch) {
    //                 // Jika tidak ada batch tersedia, hentikan proses
    //                 break;
    //             }

    //             $take = min($itemBatch->quantity, $need); // Jumlah yang diambil
    //             $itemHpp += $take * $itemBatch->price; // Hitung HPP untuk jumlah tersebut

    //             // Kurangi stok pada batch
    //             $itemBatch->quantity -= $take;
    //             $itemBatch->used_by_model = 'Sale';
    //             $itemBatch->used_by_id = $item->sale_id;
    //             $itemBatch->save();

    //             // Kurangi kebutuhan
    //             $need -= $take;
    //         }

    //         $hpp += $itemHpp; // Tambahkan total HPP per item ke total HPP
    //     }

    //     return $hpp;
    // }
}
