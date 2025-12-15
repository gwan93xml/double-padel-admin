<?php

namespace App\Services;

use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SalesPriceChangeReportService
{
    /**
     * Generate laporan penjualan per bulan, untuk satu atau semua customer.
     *
     * @param  int       $year
     * @param  int|null  $customerId  Jika null → semua customer
     * @return array|array[]  Array satu elemen (jika $customerId) atau array of arrays
     */
    public function generate(
        string $monthFrom,
        string $monthTo,
        ?int $customerId = null,
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


        // ambil semua customer yang punya sales di tahun itu
        $customerIds = Sale::query()
            ->when($customerId, function ($query) use ($customerId) {
                return $query->where('customer_id', $customerId);
            })
            ->when($warehouseId, function ($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->when($usedItemIds, function ($query) use ($usedItemIds) {
                return $query->whereHas('items', function ($q) use ($usedItemIds) {
                    $q->whereIn('item_id', $usedItemIds);
                });
            })
            ->whereBetween('sale_date', [$dateFrom, $dateTo])
            ->distinct()
            ->pluck('customer_id');

        $reports = [];
        foreach (Customer::whereIn('id', $customerIds)->get() as $cust) {
            $reports[] = $this->generateForCustomer($cust->id, $dateFrom, $dateTo, $itemId, $warehouseId);
        }

        return [...$reports];
    }

    /**
     * Bangun laporan untuk satu customer.
     */
    protected function generateForCustomer(int $customerId, $dateFrom, $dateTo, ?int $itemId = null, ?int $warehouseId = null): array
    {
        $customer = Customer::findOrFail($customerId);

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


        // 1) Ambil seluruh agregasi penjualan per bulan & per item & per warehouse
        $rows = SaleItem::query()
            ->selectRaw("
            MONTH(sales.sale_date) as month,
            YEAR(sales.sale_date) as year,
            sale_items.item_id as item_id,
            sale_items.item_name as name,
            sale_items.unit as unit,
            sales.warehouse_id as warehouse_id,
            warehouses.name as warehouse_name,
            SUM(sale_items.quantity) as jumlah,
            SUM(sale_items.price * sale_items.quantity) as total
        ")
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'sales.warehouse_id')
            ->where('sales.customer_id', $customerId)
            ->when($warehouseId, function ($query) use ($warehouseId) {
                return $query->where('sales.warehouse_id', $warehouseId);
            })
            
            ->when($usedItemIds, function ($query) use ($usedItemIds) {
                return $query->whereIn('sale_items.item_id', $usedItemIds);
            })
            ->with('item.linkedItem')
            ->whereBetween('sales.sale_date', [$dateFrom, $dateTo])
            ->groupByRaw('MONTH(sales.sale_date), YEAR(sales.sale_date), sale_items.item_id, sale_items.item_name, sale_items.unit, sales.warehouse_id, warehouses.name')
            ->orderByRaw('YEAR(sales.sale_date) ASC, MONTH(sales.sale_date) ASC, sale_items.item_name ASC, warehouses.name ASC')
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

        // 4) Hitung omset (total semua bulan)
        $omset = array_sum(array_column($months, 'monthTotal'));

        return [
            'customer' => [
                'id'   => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
            ],
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'omset'  => $omset,
            'months' => array_values($months),
        ];
    }
}
