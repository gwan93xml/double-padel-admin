<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemTransaction;
use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class StockReportMonthlyService
{
    /**
     * Generate laporan rekap stok bulanan per gudang.
     *
     * @param  string        $fromDate     Format YYYY-MM
     * @param  string        $toDate       Format YYYY-MM
     * @param  array<int>    $warehouseIds Daftar ID gudang
     * @param  bool          $export       Jika true kembalikan Collection, else paginasi
     * @param  int           $perPage      Jumlah item per halaman
     * @return LengthAwarePaginator|Collection
     */
    public function generate(
        string $fromDate,
        string $toDate,
        array $warehouseIds = [],
        bool $export = false,
        int $perPage = 50
    ) {
        // 1) Parse tanggal
        $start = Carbon::parse($fromDate)->startOfMonth();
        $end   = Carbon::parse($toDate)->endOfMonth();

        // 2) Siapkan query Item + relasi unit & kategori
        $itemQuery = Item::with(['units', 'category'])
            ->orderBy('item_category_id');

        // 3) Ambil data (export=all, else paginate)
        if ($export) {
            /** @var Collection<int,Item> $items */
            $items = $itemQuery->get();
        } else {
            /** @var LengthAwarePaginator<int,Item> $items */
            $items = $itemQuery->paginate($perPage);
        }

        // 4) Dapatkan daftar gudang yang dibutuhkan
        $warehouses = Warehouse::when($warehouseIds, fn($q) => $q->whereIn('id', $warehouseIds))
            ->get();

        // 5) Transformasi tiap Item ke struktur laporan
        $data = ($export ? $items : $items->getCollection())->map(function (Item $item) use ($start, $end, $warehouses) {
            $total = 0;
            $warehouseItems = $warehouses->map(function (Warehouse $warehouse) use ($item, $start, $end) {
                // saldo awal sebelum periode
                $inBefore  = ItemTransaction::where('item_id', $item->id)
                    ->where('transaction_type', 'IN')
                    ->where('date', '<', $start)
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('quantity');
                $outBefore = ItemTransaction::where('item_id', $item->id)
                    ->where('transaction_type', 'OUT')
                    ->where('date', '<', $start)
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('quantity');
                $startingBalance = $inBefore - $outBefore;

                // pergerakan masuk/keluar dalam periode
                $totalIn  = ItemTransaction::where('item_id', $item->id)
                    ->where('transaction_type', 'IN')
                    ->whereBetween('date', [$start, $end])
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('quantity');
                $totalOut = ItemTransaction::where('item_id', $item->id)
                    ->where('transaction_type', 'OUT')
                    ->whereBetween('date', [$start, $end])
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('quantity');

                $endingBalance = $startingBalance + $totalIn - $totalOut;

                return [
                    'warehouse'        => [
                        'id'   => $warehouse->id,
                        'code' => $warehouse->code,
                        'name' => $warehouse->name,
                    ],
                    'unformatted_ending_balance' => $endingBalance,
                    'ending_balance'   => Item::formatQuantity(
                        $endingBalance,
                        $item->unit,
                        $item->unit_report_1,
                        $item->unit_report_2,
                        $item->units
                    ),
                ];
            })->toArray();

            foreach ($warehouseItems as $warehouseItem) {
                // tambahkan ending balance ke total
                $total += $warehouseItem['unformatted_ending_balance'];
            }

            return [
                'category'        => $item->category,
                'id'              => $item->id,
                'name'            => $item->name,
                'code'            => $item->code,
                'unit'            => $item->unit,
                'unit_report_1'   => $item->unit_report_1,
                'unit_report_2'   => $item->unit_report_2,
                'warehouses'      => $warehouseItems,
                'total'  => Item::formatQuantity(
                    $total,
                    $item->unit,
                    $item->unit_report_1,
                    $item->unit_report_2,
                    $item->units
                ),
            ];
        });

        // 6) Kembalikan sesuai mode
        if ($export) {
            return $data;
        }

        // ganti collection paginator dengan data transformasi
        $items->setCollection($data);
        return $items;
    }
}
