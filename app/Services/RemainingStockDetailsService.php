<?php

namespace App\Services;

use App\Models\Item;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use App\Models\ItemInItem;
use App\Models\ItemOutItem;
use App\Models\StockMovementItem;
use App\Models\Production;
use App\Models\ProductionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RemainingStockDetailsService
{
    public function getStockCard(Request $request): array
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $itemId = $request->item_id;
        $warehouseId = $request->warehouse_id;

        // Ambil item utama
        $item = Item::with('units', 'linkedItem')->findOrFail($itemId);

        // Tentukan item IDs yang akan diproses
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

        // Hitung saldo awal untuk semua item
        $startingBalance = 0;
        foreach ($itemIds as $id) {
            $startingBalance += $this->calculateStartingBalance($id, $warehouseId, $fromDate);
        }

        // Ambil semua transaksi dalam periode untuk semua item
        $allTransactions = [];
        foreach ($itemIds as $id) {
            $transactions = $this->getAllTransactions($id, $warehouseId, $fromDate, $toDate);
            $allTransactions = array_merge($allTransactions, $transactions);
        }

        // Sort transaksi gabungan
        usort($allTransactions, function ($a, $b) {
            $dateCompare = strcmp($a['date'], $b['date']);
            if ($dateCompare === 0) {
                return $a['sort_order'] <=> $b['sort_order'];
            }
            return $dateCompare;
        });

        // Hitung totals dan format transaksi
        $result = $this->calculateTotalsAndFormatTransactions(
            $allTransactions,
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
        $balanceIn = 0;
        $balanceOut = 0;

        // Purchase transactions (IN)
        $balanceIn += $this->getPurchaseBalance($itemId, $warehouseId, $fromDate);

        // Item In transactions (IN)
        $balanceIn += $this->getItemInBalance($itemId, $warehouseId, $fromDate);

        // Stock Movement IN (when warehouse is destination)
        $balanceIn += $this->getStockMovementInBalance($itemId, $warehouseId, $fromDate);

        // Production IN (when item is produced)
        $balanceIn += $this->getProductionInBalance($itemId, $warehouseId, $fromDate);


        // Sale transactions (OUT)
        $balanceOut += $this->getSaleBalance($itemId, $warehouseId, $fromDate);

        // Item Out transactions (OUT)
        $balanceOut += $this->getItemOutBalance($itemId, $warehouseId, $fromDate);

        // Stock Movement OUT (when warehouse is source)
        $balanceOut += $this->getStockMovementOutBalance($itemId, $warehouseId, $fromDate);

        // Production OUT (when item is used as material)
        $balanceOut += $this->getProductionOutBalance($itemId, $warehouseId, $fromDate);

        return $balanceIn - $balanceOut;
    }

    protected function getAllTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate): array
    {
        $transactions = [];

        // Purchase transactions
        $transactions = array_merge($transactions, $this->getPurchaseTransactions($itemId, $warehouseId, $fromDate, $toDate));

        // Sale transactions
        $transactions = array_merge($transactions, $this->getSaleTransactions($itemId, $warehouseId, $fromDate, $toDate));

        // Item In transactions
        $transactions = array_merge($transactions, $this->getItemInTransactions($itemId, $warehouseId, $fromDate, $toDate));

        // Item Out transactions
        $transactions = array_merge($transactions, $this->getItemOutTransactions($itemId, $warehouseId, $fromDate, $toDate));

        // Stock Movement transactions
        $transactions = array_merge($transactions, $this->getStockMovementTransactions($itemId, $warehouseId, $fromDate, $toDate));

        // Production transactions
        $transactions = array_merge($transactions, $this->getProductionTransactions($itemId, $warehouseId, $fromDate, $toDate));

        // Sort by date and ID
        usort($transactions, function ($a, $b) {
            $dateCompare = strcmp($a['date'], $b['date']);
            if ($dateCompare === 0) {
                return $a['sort_order'] <=> $b['sort_order'];
            }
            return $dateCompare;
        });

        return $transactions;
    }

    protected function getPurchaseBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $query = PurchaseItem::with(['item.units'])
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchase_items.item_id', $itemId)
            ->where('purchase_items.is_stock', true)
            ->where('purchases.purchase_date', '<', $fromDate);

        if ($warehouseId) {
            $query->where('purchases.warehouse_id', $warehouseId);
        }

        $items = $query->get();
        $total = 0;

        foreach ($items as $item) {
            $baseQuantity = Item::toBaseQuantity($item->item, $item->unit, $item->quantity);
            $total += $baseQuantity;
        }

        return $total;
    }

    protected function getSaleBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $query = SaleItem::with(['item.units'])
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.item_id', $itemId)
            ->where('sale_items.is_stock', true)
            ->where('sales.sale_date', '<', $fromDate);

        if ($warehouseId) {
            $query->where('sales.warehouse_id', $warehouseId);
        }

        $items = $query->get();
        $total = 0;

        foreach ($items as $item) {
            $baseQuantity = Item::toBaseQuantity($item->item, $item->unit, $item->quantity);
            $total += $baseQuantity;
        }

        return $total;
    }

    protected function getItemInBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $query = ItemInItem::with(['item.units'])
            ->join('item_ins', 'item_in_items.item_in_id', '=', 'item_ins.id')
            ->where('item_in_items.item_id', $itemId)
            ->where('item_ins.date', '<', $fromDate);

        if ($warehouseId) {
            $query->where('item_ins.warehouse_id', $warehouseId);
        }

        $items = $query->get();
        $total = 0;

        foreach ($items as $item) {
            $baseQuantity = Item::toBaseQuantity($item->item, $item->unit, $item->quantity);
            $total += $baseQuantity;
        }

        return $total;
    }

    protected function getItemOutBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $query = ItemOutItem::with(['item.units'])
            ->join('item_outs', 'item_out_items.item_out_id', '=', 'item_outs.id')
            ->where('item_out_items.item_id', $itemId)
            ->where('item_outs.date', '<', $fromDate);

        if ($warehouseId) {
            $query->where('item_outs.warehouse_id', $warehouseId);
        }

        $items = $query->get();
        $total = 0;

        foreach ($items as $item) {
            $baseQuantity = Item::toBaseQuantity($item->item, $item->unit, $item->quantity);
            $total += $baseQuantity;
        }

        return $total;
    }

    protected function getStockMovementInBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $query = StockMovementItem::with(['item.units'])
            ->join('stock_movements', 'stock_movement_items.stock_movement_id', '=', 'stock_movements.id')
            ->where('stock_movement_items.item_id', $itemId)
            ->where('stock_movements.date', '<', $fromDate);

        if ($warehouseId) {
            $query->where('stock_movements.warehouse_in_id', $warehouseId);
        }

        $items = $query->get();
        $total = 0;

        foreach ($items as $item) {
            $baseQuantity = Item::toBaseQuantity($item->item, $item->unit, $item->quantity);
            $total += $baseQuantity;
        }

        return $total;
    }

    protected function getStockMovementOutBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $query = StockMovementItem::with(['item.units'])
            ->join('stock_movements', 'stock_movement_items.stock_movement_id', '=', 'stock_movements.id')
            ->where('stock_movement_items.item_id', $itemId)
            ->where('stock_movements.date', '<', $fromDate);

        if ($warehouseId) {
            $query->where('stock_movements.warehouse_out_id', $warehouseId);
        }

        $items = $query->get();
        $total = 0;

        foreach ($items as $item) {
            $baseQuantity = Item::toBaseQuantity($item->item, $item->unit, $item->quantity);
            $total += $baseQuantity;
        }

        return $total;
    }

    protected function getProductionInBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $query = Production::with(['item.units'])
            ->where('item_id', $itemId)
            ->where('date', '<', $fromDate);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $productions = $query->get();
        $total = 0;

        foreach ($productions as $production) {
            // Production biasanya sudah dalam satuan dasar item yang diproduksi
            $baseQuantity = Item::toBaseQuantity($production->item, $production->unit ?? $production->item->unit, $production->quantity);
            $total += $baseQuantity;
        }

        return $total;
    }

    protected function getProductionOutBalance(int $itemId, ?int $warehouseId, string $fromDate): float
    {
        $productionItems = ProductionItem::with(['production', 'item.units'])
            ->where('item_id', $itemId)
            ->whereHas('production', function ($query) use ($fromDate, $warehouseId) {
                $query->where('date', '<', $fromDate);
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }
            })
            ->get();
        $total = 0;
        foreach ($productionItems as $item) {
            $baseQuantity = Item::toBaseQuantity($item->item, $item->unit, $item->quantity);
            $total += $baseQuantity;
        }
        return $total;
    }

    protected function getPurchaseTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate): array
    {
        $query = PurchaseItem::with(['purchase.vendor', 'item.units', 'item.linkedItem'])
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchase_items.item_id', $itemId)
            ->where('purchase_items.is_stock', true)
            ->whereBetween('purchases.purchase_date', [$fromDate, $toDate])
            ->whereNull('purchases.deleted_at')
            ->whereNull('purchase_items.deleted_at');

        if ($warehouseId) {
            $query->where('purchases.warehouse_id', $warehouseId);
        }

        // Debug: Log query untuk debugging
        Log::info('Purchase Query', [
            'itemId' => $itemId,
            'warehouseId' => $warehouseId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $items = $query->orderBy('purchases.purchase_date')
            ->orderBy('purchases.id')
            ->get();

        Log::info('Purchase Items Found', ['count' => $items->count()]);

        $transactions = [];
        foreach ($items as $item) {
            // Simpan quantity asli tanpa konversi - konversi akan dilakukan di calculateTotalsAndFormatTransactions

            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $item->item->linked_item_id ? " [{$item->item->code} - {$item->item->name}]" : "";

            $transactions[] = [
                'id' => 'purchase_' . $item->id,
                'date' => $item->purchase->purchase_date,
                'transaction_type' => 'IN',
                'quantity' => $item->quantity, // Gunakan quantity asli
                'reference_type' => 'Purchase',
                'reference_id' => $item->purchase->id,
                'remarks' => "Pembelian #{$item->purchase->no} {$item->purchase->invoice_number}",
                'vendor' => $item->purchase->vendor,
                'customer' => null,
                'warehouse' => $item->purchase->warehouse ?? null,
                'linking_item' => $item->item->linkedItem ?? null,
                'item_detail' => $item->item,
                'sort_order' => 2,
                'unit' => $item->unit,
            ];
        }

        return $transactions;
    }

    protected function getSaleTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate): array
    {
        $query = SaleItem::with(['sale.customer', 'item.units', 'item.linkedItem'])
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sale_items.item_id', $itemId)
            ->where('sale_items.is_stock', true)
            ->whereBetween('sales.sale_date', [$fromDate, $toDate])
            ->whereNull('sales.deleted_at')
            ->whereNull('sale_items.deleted_at');

        if ($warehouseId) {
            $query->where('sales.warehouse_id', $warehouseId);
        }

        $items = $query->orderBy('sales.sale_date')
            ->orderBy('sales.id')
            ->get();

        $transactions = [];
        foreach ($items as $item) {
            // Simpan quantity asli tanpa konversi - konversi akan dilakukan di calculateTotalsAndFormatTransactions

            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $item->item->linked_item_id ? " [{$item->item->code} - {$item->item->name}]" : "";

            $transactions[] = [
                'id' => 'sale_' . $item->id,
                'date' => $item->sale->sale_date,
                'transaction_type' => 'OUT',
                'quantity' => $item->quantity, // Gunakan quantity asli
                'reference_type' => 'Sale',
                'reference_id' => $item->sale->id,
                'remarks' => "Penjualan #{$item->sale->no} {$item->sale->purchase_order_number}",
                'vendor' => null,
                'customer' => $item->sale->customer,
                'warehouse' => $item->sale->warehouse ?? null,
                'linking_item' => $item->item->linkedItem ?? null,
                'item_detail' => $item->item,
                'sort_order' => 5,
                'unit' => $item->unit,
            ];
        }

        return $transactions;
    }

    protected function getItemInTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate): array
    {
        $query = ItemInItem::with(['itemIn.warehouse', 'item.units', 'item.linkedItem'])
            ->join('item_ins', 'item_in_items.item_in_id', '=', 'item_ins.id')
            ->where('item_in_items.item_id', $itemId)
            ->whereBetween('item_ins.date', [$fromDate, $toDate]);

        if ($warehouseId) {
            $query->where('item_ins.warehouse_id', $warehouseId);
        }

        $items = $query->orderBy('item_ins.date')
            ->orderBy('item_ins.id')
            ->get();

        $transactions = [];
        foreach ($items as $item) {
            // Simpan quantity asli tanpa konversi - konversi akan dilakukan di calculateTotalsAndFormatTransactions

            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $item->item->linked_item_id ? " [{$item->item->code} - {$item->item->name}]" : "";
            $transactions[] = [
                'id' => 'item_in_' . $item->id,
                'date' => $item->itemIn->date,
                'transaction_type' => 'IN',
                'quantity' => $item->quantity, // Gunakan quantity asli
                'reference_type' => 'Item In',
                'reference_id' => $item->itemIn->id,
                'remarks' => "Barang Masuk #{$item->itemIn->number}{$itemInfo}",
                'vendor' => null,
                'customer' => null,
                'warehouse' => $item->itemIn->warehouse ?? null,
                'linking_item' => $item->item->linkedItem ?? null,
                'item_detail' => $item->item,
                'sort_order' => 1,
                'unit' => $item->unit,
            ];
        }

        return $transactions;
    }

    protected function getItemOutTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate): array
    {
        $query = ItemOutItem::with(['itemOut.warehouse', 'item.units', 'item.linkedItem'])
            ->join('item_outs', 'item_out_items.item_out_id', '=', 'item_outs.id')
            ->where('item_out_items.item_id', $itemId)
            ->whereBetween('item_outs.date', [$fromDate, $toDate]);

        if ($warehouseId) {
            $query->where('item_outs.warehouse_id', $warehouseId);
        }

        $items = $query->orderBy('item_outs.date')
            ->orderBy('item_outs.id')
            ->get();

        $transactions = [];
        foreach ($items as $item) {
            // Simpan quantity asli tanpa konversi - konversi akan dilakukan di calculateTotalsAndFormatTransactions

            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $item->item->linked_item_id ? " [{$item->item->code} - {$item->item->name}]" : "";

            $transactions[] = [
                'id' => 'item_out_' . $item->id,
                'date' => $item->itemOut->date,
                'transaction_type' => 'OUT',
                'quantity' => $item->quantity, // Gunakan quantity asli
                'reference_type' => 'Item Out',
                'reference_id' => $item->itemOut->id,
                'remarks' => "Barang Keluar #{$item->itemOut->number}{$itemInfo}",
                'vendor' => null,
                'customer' => null,
                'warehouse' => $item->itemOut->warehouse ?? null,
                'linking_item' => $item->item->linkedItem ?? null,
                'item_detail' => $item->item,
                'sort_order' => 6,
                'unit' => $item->unit,
            ];
        }

        return $transactions;
    }

    protected function getStockMovementTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate): array
    {
        $transactions = [];

        // Stock Movement OUT (from source warehouse)
        $queryOut = StockMovementItem::with(['stockMovement.warehouseOut', 'item.units', 'item.linkedItem'])
            ->join('stock_movements', 'stock_movement_items.stock_movement_id', '=', 'stock_movements.id')
            ->where('stock_movement_items.item_id', $itemId)
            ->whereBetween('stock_movements.date', [$fromDate, $toDate]);

        if ($warehouseId) {
            $queryOut->where('stock_movements.warehouse_out_id', $warehouseId);
        }

        $outItems = $queryOut->orderBy('stock_movements.date')
            ->orderBy('stock_movements.id')
            ->get();

        foreach ($outItems as $item) {
            // Simpan quantity asli tanpa konversi - konversi akan dilakukan di calculateTotalsAndFormatTransactions

            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $item->item->linked_item_id ? " [{$item->item->code} - {$item->item->name}]" : "";

            $transactions[] = [
                'id' => 'stock_movement_out_' . $item->id,
                'date' => $item->stockMovement->date,
                'transaction_type' => 'OUT',
                'quantity' => $item->quantity, // Gunakan quantity asli
                'reference_type' => 'Stock Movement',
                'reference_id' => $item->stockMovement->id,
                'remarks' => "Pindah Gudang #{$item->stockMovement->number} (Keluar){$itemInfo}",
                'vendor' => null,
                'customer' => null,
                'warehouse' => $item->stockMovement->warehouseOut ?? null,
                'linking_item' => $item->item->linkedItem ?? null,
                'item_detail' => $item->item,
                'sort_order' => 7,
                'unit' => $item->unit,
            ];
        }

        // Stock Movement IN (to destination warehouse)
        $queryIn = StockMovementItem::with(['stockMovement.warehouseIn', 'item.units', 'item.linkedItem'])
            ->join('stock_movements', 'stock_movement_items.stock_movement_id', '=', 'stock_movements.id')
            ->where('stock_movement_items.item_id', $itemId)
            ->whereBetween('stock_movements.date', [$fromDate, $toDate]);

        if ($warehouseId) {
            $queryIn->where('stock_movements.warehouse_in_id', $warehouseId);
        }

        $inItems = $queryIn->orderBy('stock_movements.date')
            ->orderBy('stock_movements.id')
            ->get();

        foreach ($inItems as $item) {
            // Simpan quantity asli tanpa konversi - konversi akan dilakukan di calculateTotalsAndFormatTransactions

            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $item->item->linked_item_id ? " [{$item->item->code} - {$item->item->name}]" : "";

            $transactions[] = [
                'id' => 'stock_movement_in_' . $item->id,
                'date' => $item->stockMovement->date,
                'transaction_type' => 'IN',
                'quantity' => $item->quantity, // Gunakan quantity asli
                'reference_type' => 'Stock Movement',
                'reference_id' => $item->stockMovement->id,
                'remarks' => "Pindah Gudang #{$item->stockMovement->number} (Masuk){$itemInfo}",
                'vendor' => null,
                'customer' => null,
                'warehouse' => $item->stockMovement->warehouseIn ?? null,
                'linking_item' => $item->item->linkedItem ?? null,
                'item_detail' => $item->item,
                'sort_order' => 4,
                'unit' => $item->unit,
            ];
        }

        return $transactions;
    }

    protected function getProductionTransactions(int $itemId, ?int $warehouseId, string $fromDate, string $toDate): array
    {
        $query = Production::with(['warehouse', 'item.units', 'item.linkedItem'])
            ->where('item_id', $itemId)
            ->whereBetween('date', [$fromDate, $toDate]);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $productions = $query->orderBy('date')
            ->orderBy('id')
            ->get();

        $transactions = [];
        foreach ($productions as $production) {
            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $production->item->linked_item_id ? " [{$production->item->code} - {$production->item->name}]" : "";

            $transactions[] = [
                'id' => 'production_' . $production->id,
                'date' => $production->date,
                'transaction_type' => 'IN',
                'quantity' => $production->quantity,
                'reference_type' => 'Production',
                'reference_id' => $production->id,
                'remarks' => "Produksi #{$production->number}{$itemInfo}",
                'vendor' => null,
                'customer' => null,
                'warehouse' => $production->warehouse ?? null,
                'linking_item' => $production->item->linkedItem ?? null,
                'item_detail' => $production->item,
                'sort_order' => 3,
                'unit' => $production->unit,
            ];
        }

        $productionItems = ProductionItem::with(['production', 'item.units', 'item.linkedItem'])
            ->where('item_id', $itemId)
            ->whereHas('production', function ($query) use ($itemId, $warehouseId, $fromDate, $toDate) {
                $query->whereBetween('date', [$fromDate, $toDate]);
                if ($warehouseId) {
                    $query->where('warehouse_id', $warehouseId);
                }
            })
            ->get();

        foreach ($productionItems as $productionItem) {
            // Tambahkan info item jika ada linked_item_id
            $itemInfo = $productionItem->item->linked_item_id ? " [{$productionItem->item->code} - {$productionItem->item->name}]" : "";

            $transactions[] = [
                'id' => 'production_item_' . $productionItem->id,
                'date' => $productionItem->production->date,
                'transaction_type' => 'OUT',
                'quantity' => $productionItem->quantity,
                'reference_type' => 'Production Material',
                'reference_id' => $productionItem->production->id,
                'remarks' => "Bahan Produksi #{$productionItem->production->id} {$itemInfo}",
                'vendor' => null,
                'customer' => null,
                'warehouse' => $productionItem->production->warehouse ?? null,
                'linking_item' => $productionItem->item->linkedItem ?? $productionItem->item,
                'item_detail' => $productionItem->item,
                'sort_order' => 8,
                'unit' => $productionItem->unit,
            ];
        }

        return $transactions;
    }

    protected function calculateTotalsAndFormatTransactions(array $transactions, float $startingBalance, Item $item): array
    {
        $balance = $startingBalance;
        $totalIn = 0;
        $totalOut = 0;

        $unit = $item->unit;
        $unitReport1 = $item->unit_report_1;
        $unitReport2 = $item->unit_report_2;

        foreach ($transactions as &$transaction) {
            // Konversi quantity ke satuan terkecil menggunakan unit dari transaksi
            $itemDetail = $transaction['item_detail'] ?? $item;
            $transactionUnit = $transaction['unit'] ?? $itemDetail->unit;
            $baseQuantity = Item::toBaseQuantity($itemDetail, $transactionUnit, $transaction['quantity']);

            // Update balance menggunakan base quantity
            $balance += $transaction['transaction_type'] === 'IN' ? $baseQuantity : -$baseQuantity;

            // Update totals menggunakan base quantity
            if ($transaction['transaction_type'] === 'IN') {
                $totalIn += $baseQuantity;
            } else {
                $totalOut += $baseQuantity;
            }

            // Simpan baseQuantity sebagai quantity untuk perhitungan, lalu format untuk display
            $transaction['quantity'] = Item::formatQuantity(
                $baseQuantity,
                $unit,
                $unitReport1,
                $unitReport2,
                $item->units
            );

            $transaction['balance'] = Item::formatQuantity(
                $balance,
                $unit,
                $unitReport1,
                $unitReport2,
                $item->units
            );

            // Convert to object for consistency with frontend
            $transaction = (object) $transaction;
        }

        $closingBalance = $balance;

        return [
            'transactions' => $transactions,
            'starting_balance_formatted' => Item::formatQuantity($startingBalance, $unit, $unitReport1, $unitReport2, $item->units),
            'total_in_formatted' => Item::formatQuantity($totalIn, $unit, $unitReport1, $unitReport2, $item->units),
            'total_out_formatted' => Item::formatQuantity($totalOut, $unit, $unitReport1, $unitReport2, $item->units),
            'closing_balance_formatted' => Item::formatQuantity($closingBalance, $unit, $unitReport1, $unitReport2, $item->units),
        ];
    }
}
