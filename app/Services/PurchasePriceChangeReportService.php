<?php

namespace App\Services;

use App\Models\PurchaseItem;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\Purchase;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PurchasePriceChangeReportService
{
    /**
     * Generate laporan pembelian per bulan, untuk satu atau semua vendor.
     *
     * @param  int       $year
     * @param  int|null  $vendorId  Jika null → semua vendor
     * @return array|array[]  Array satu elemen (jika $vendorId) atau array of arrays
     */
    public function generate(
        string $monthFrom,
        string $monthTo,
        ?int $vendorId = null,
        ?int $itemId = null,
        ?int $warehouseId = null
    ): array {
        $dateFrom = Carbon::parse($monthFrom)->startOfMonth();
        $dateTo   = Carbon::parse($monthTo)->endOfMonth();

        $item = Item::find($itemId);
        $usedItemIds = [];
        if ($item) {
            if (!$item->linked_item_id) {
                $items = Item::where('linked_item_id', $item->id)
                    ->orWhere('id', $item->id)
                    ->get();
                $usedItemIds = $items->pluck('id')->toArray();
            } else {
                $usedItemIds = [$item->id];
            }
        }


        // ambil semua vendor yang punya purchase di tahun itu
        $vendorIds = Purchase::query()
            ->when($vendorId, function ($query) use ($vendorId) {
                return $query->where('vendor_id', $vendorId);
            })
            ->when($warehouseId, function ($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->when($usedItemIds, function ($query) use ($usedItemIds) {
                return $query->whereHas('items', function ($q) use ($usedItemIds) {
                    $q->whereIn('item_id', $usedItemIds);
                });
            })
            ->whereBetween('purchase_date', [$dateFrom, $dateTo])
            ->distinct()
            ->pluck('vendor_id');

        $reports = [];
        foreach (Vendor::whereIn('id', $vendorIds)->get() as $vend) {
            $reports[] = $this->generateForVendor($vend->id, $dateFrom, $dateTo, $itemId, $warehouseId);
        }

        return [...$reports];
    }

    /**
     * Bangun laporan untuk satu vendor.
     */
    protected function generateForVendor(int $vendorId, $dateFrom, $dateTo, ?int $itemId = null, ?int $warehouseId): array
    {
        $vendor = Vendor::findOrFail($vendorId);

        $item = Item::find($itemId);
        $usedItemIds = [];
        if ($item) {
            if (!$item->linked_item_id) {
                $items = Item::where('linked_item_id', $item->id)
                    ->orWhere('id', $item->id)
                    ->get();
                $usedItemIds = $items->pluck('id')->toArray();
            } else {
                $usedItemIds = [$item->id];
            }
        }


        // 1) Ambil seluruh agregasi pembelian per bulan & per item & per warehouse
        $rows = PurchaseItem::query()
            ->selectRaw("
            MONTH(purchases.purchase_date) as month,
            YEAR(purchases.purchase_date) as year,
            purchase_items.item_id as item_id,
            purchase_items.item_name as name,
            purchase_items.unit as unit,
            purchases.warehouse_id as warehouse_id,
            warehouses.name as warehouse_name,
            SUM(purchase_items.quantity) as jumlah,
            SUM(purchase_items.price * purchase_items.quantity) as total
        ")
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'purchases.warehouse_id')
            ->where('purchases.vendor_id', $vendorId)
            ->when($usedItemIds, function ($query) use ($usedItemIds) {
                return $query->whereIn('purchase_items.item_id', $usedItemIds);
            })
            ->when($warehouseId, function ($query) use ($warehouseId) {
                return $query->where('purchases.warehouse_id', $warehouseId);
            })
            ->with('item.linkedItem')
            ->whereBetween('purchases.purchase_date', [$dateFrom, $dateTo])
            ->groupByRaw('MONTH(purchases.purchase_date), YEAR(purchases.purchase_date), purchase_items.item_id, purchase_items.item_name, purchase_items.unit, purchases.warehouse_id, warehouses.name')
            ->orderByRaw('YEAR(purchases.purchase_date) ASC, MONTH(purchases.purchase_date) ASC, purchase_items.item_name ASC, warehouses.name ASC')
            ->get();


        // 2) Group data by month and item, collecting warehouse breakdowns
        $groupedData = [];
        foreach ($rows as $row) {
            $monthKey = $row->year . '-' . str_pad($row->month, 2, '0', STR_PAD_LEFT);
            $itemKey = $row->item_id ?: $row->name;

            if (!isset($groupedData[$monthKey])) {
                $groupedData[$monthKey] = [];
            }

            if (!isset($groupedData[$monthKey][$itemKey])) {
                $groupedData[$monthKey][$itemKey] = [
                    'item_id' => $row->item_id,
                    'name' => $row->name,
                    'unit' => $row->unit,
                    'total_jumlah' => 0,
                    'total_amount' => 0,
                    'warehouses' => []
                ];
            }

            $groupedData[$monthKey][$itemKey]['warehouses'][] = [
                'warehouse_id' => $row->warehouse_id,
                'warehouse_name' => $row->warehouse_name,
                'jumlah' => (int) $row->jumlah,
                'total' => (int) $row->total,
                'harga' => (int) floor($row->total / max(1, $row->jumlah))
            ];

            $groupedData[$monthKey][$itemKey]['total_jumlah'] += (int) $row->jumlah;
            $groupedData[$monthKey][$itemKey]['total_amount'] += (int) $row->total;
        }

        // 3) Build item list from grouped data
        $itemList = [];
        foreach ($groupedData as $monthData) {
            foreach ($monthData as $itemKey => $itemData) {
                $itemList[$itemKey] = $itemData['name'];
            }
        }


        // 4) Bangun struktur per‐bulan
        $months = [];
        $period = CarbonPeriod::create($dateFrom, '1 month', $dateTo);
        foreach ($period as $dt) {
            $key = $dt->format('Y-m');           // misal "2025-04"
            $label = $dt->locale('id')->isoFormat('MMMM YYYY'); // "April 2025"
            $months[$key] = [
                'key'        => $key,
                'name'       => $label,
                'items'      => [],
                'monthTotal' => 0,
            ];

            foreach ($itemList as $itemId => $itemName) {
                $monthData = $groupedData[$key] ?? [];
                $itemData = $monthData[$itemId] ?? null;

                if ($itemData) {
                    $months[$key]['items'][] = [
                        'name' => $itemName,
                        'unit' => $itemData['unit'],
                        'harga' => (int) floor($itemData['total_amount'] / max(1, $itemData['total_jumlah'])),
                        'jumlah' => $itemData['total_jumlah'],
                        'total' => $itemData['total_amount'],
                        'warehouses' => $itemData['warehouses'],
                        'linked_item' => Item::find($itemId)->linkedItem ?? null,
                    ];
                    $months[$key]['monthTotal'] += $itemData['total_amount'];
                } else {
                    $months[$key]['items'][] = [
                        'name' => $itemName,
                        'unit' => '',
                        'harga' => 0,
                        'jumlah' => 0,
                        'total' => 0,
                        'warehouses' => [],
                        'linked_item' => Item::find($itemId)->linkedItem ?? null,
                    ];
                }
            }
        }

        // 4) Hitung total pembelian (total semua bulan)
        $totalPurchase = array_sum(array_column($months, 'monthTotal'));

        return [
            'vendor' => [
                'id'   => $vendor->id,
                'code' => $vendor->code,
                'name' => $vendor->name,
            ],
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'total_purchase'  => $totalPurchase,
            'months' => array_values($months),
        ];
    }
}