<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemBatch;
use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemStockService
{
    public function getItemsWithStock(?string $search = null, ?array $warehouseIds = null, int $perPage = 15, bool $paginate = true)
    {
        $query = Item::with(['linkedItem', 'units'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('code', 'like', '%' . $search . '%');
                });
            });

        if (!$paginate) {
            $items = $query->get();
            $warehouses = $warehouseIds ? Warehouse::whereIn('id', $warehouseIds)->get() : Warehouse::all();
            
            $processedItems = [];
            foreach ($items as $item) {
                $item->warehouses = $this->calculateWarehouseStocks($item, $warehouses, $warehouseIds);
                $item->stock = $this->calculateTotalStock($item, $warehouseIds);
                $processedItems[] = $item;
            }
            
            return collect($processedItems);
        }

        $items = $query->paginate($perPage);
        $warehouses = $warehouseIds ? Warehouse::whereIn('id', $warehouseIds)->get() : Warehouse::all();

        $paginatedItems = [];
        foreach ($items->items() as $item) {
            $item->warehouses = $this->calculateWarehouseStocks($item, $warehouses, $warehouseIds);
            $item->stock = $this->calculateTotalStock($item, $warehouseIds);
            $paginatedItems[] = $item;
        }

        return new LengthAwarePaginator(
            $paginatedItems,
            $items->total(),
            $items->perPage(),
            $items->currentPage(),
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    private function calculateWarehouseStocks(Item $item, $warehouses, ?array $warehouseIds = null)
    {
        return $warehouses->map(function ($warehouse) use ($item) {
            $baseStock = ItemBatch::where('item_id', $item->id)
                ->where('warehouse_id', $warehouse->id)
                ->sum('quantity');
            
            return [
                'quantity' => $this->formatStock($item, $baseStock)
            ];
        });
    }

    private function calculateTotalStock(Item $item, ?array $warehouseIds = null): string
    {
        if ($warehouseIds) {
            $baseStock = ItemBatch::where('item_id', $item->id)
                ->whereIn('warehouse_id', $warehouseIds)
                ->sum('quantity');
        } else {
            $baseStock = ItemBatch::where('item_id', $item->id)->sum('quantity');
        }
        
        return $this->formatStock($item, $baseStock);
    }

    private function formatStock(Item $item, float $baseStock): string
    {
        $unitReport1 = $item->unit_report_1;
        $unitReport2 = $item->unit_report_2;

        if ($item->units->count() === 0) {
            return "$baseStock {$item->unit}";
        }

        if ($unitReport1 === null) {
            return "$baseStock {$item->unit}";
        }

        if ($unitReport1 === $unitReport2) {
            return $this->formatSingleUnit($item, $baseStock, $unitReport1);
        }

        return $this->formatDualUnit($item, $baseStock, $unitReport1, $unitReport2);
    }

    private function formatSingleUnit(Item $item, float $baseStock, string $unitReport1): string
    {
        if ($unitReport1 === $item->units[0]?->name) {
            return "$baseStock $unitReport1";
        }

        $unit = $item->units->where('name', $unitReport1)->first();
        if (!$unit) {
            return "$baseStock {$item->unit}";
        }
        
        $conversion = $unit->conversion;
        $converted = floor($baseStock / $conversion);
        return "$converted $unitReport1";
    }

    private function formatDualUnit(Item $item, float $baseStock, string $unitReport1, string $unitReport2): string
    {
        $unit1 = $item->units->where('name', $unitReport1)->first();
        $unit2 = $item->units->where('name', $unitReport2)->first();
        
        if (!$unit1 || !$unit2) {
            return "$baseStock {$item->unit}";
        }
        
        $conversion1 = $unit1->conversion;
        $conversion2 = $unit2->conversion;
        
        $converted1 = floor($baseStock / $conversion1);
        $remainder = $baseStock - $conversion1 * $converted1;
        $converted2 = floor($remainder / $conversion2);
        
        if ($converted1 == 0) {
            return "$converted2 $unitReport2";
        }
        
        return "$converted1 $unitReport1 $converted2 $unitReport2";
    }
}
