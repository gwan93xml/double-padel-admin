<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemBatch;
use App\Services\ActualStockService;
use Illuminate\Http\Request;

class StockReportService
{
    protected $actualStockService;
    
    public function __construct(ActualStockService $actualStockService)
    {
        $this->actualStockService = $actualStockService;
    }
    
    public function getStockReport(Request $request, bool $paginate = true)
    {
        $baseQuery = Item::query()
            ->where('is_non_stock', false)
            ->where('is_linked', false)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('code', 'like', '%' . $request->search . '%');
                });
            })
            ->with(['units']);

        if ($paginate) {
            $items = $baseQuery->paginate(50);
            
            // Transform the paginated items
            $transformedItems = $items->items();
            foreach ($transformedItems as $key => $item) {
                $transformedItems[$key] = $this->calculateStockData($item);
            }
            
            return $items;
        } else {
            $items = $baseQuery->get();
            return $items->map(function ($item) {
                return $this->calculateStockData($item);
            });
        }
    }

    private function calculateStockData($item)
    {
        // Use the new ActualStockService for stock calculation
        $stockData = $this->actualStockService->getItemStockWithActualCalculation($item);
        
        $item->stock = $stockData['stock'];
        $item->avg_price = $stockData['avg_price'];
        $item->total = $stockData['total'];
        
        return $item;
    }
    
    /**
     * Get stock comparison between batch and actual calculation
     */
    public function getStockComparison(Request $request)
    {
        $baseQuery = Item::query()
            ->where('is_non_stock', false)
            ->where('is_linked', false)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('code', 'like', '%' . $request->search. '%');
                });
            })
            ->with(['units']);

        $items = $baseQuery->get();
        
        $comparisons = [];
        foreach ($items as $item) {
            $comparison = $this->actualStockService->compareWithBatchStock($item->id);
            $stockData = $this->actualStockService->getItemStockWithActualCalculation($item);
            
            $comparisons[] = [
                'item' => $item,
                'batch_stock' => $comparison['batch_stock'],
                'actual_stock' => $comparison['actual_stock'],
                'difference' => $comparison['difference'],
                'is_matching' => $comparison['is_matching'],
                'actual_stock_formatted' => $stockData['stock']
            ];
        }
        
        return $comparisons;
    }

    private function formatStock($item, $baseStock, $unitReport1, $unitReport2)
    {
        if ($item->units->count() > 0) {
            if ($item->unit_report_1 == null) {
                return "$baseStock $item->unit";
            } else {
                if ($unitReport1 == $unitReport2) {
                    if ($unitReport1 == $item->units[0]?->name) {
                        return "$baseStock $unitReport1";
                    } else {
                        $conversion = $item->units->where('name', $unitReport1)->first()->conversion;
                        $converted = floor($baseStock / $conversion);
                        return "$converted $unitReport1";
                    }
                } else {
                    $conversion1 = $item->units->where('name', $unitReport1)->first();
                    $conversionRate1 = $conversion1 ? $conversion1->conversion : dd("Conversion not found for $unitReport1 in item $item->name");
                    $conversion2 = $item->units->where('name', $unitReport2)->first();
                    $conversionRate2 = $conversion2 ? $conversion2->conversion : dd("Conversion not found for $unitReport2  in item $item->name");
                    $converted1 = floor($baseStock / $conversionRate1);
                    $remainder = $baseStock - $conversionRate1 * $converted1;
                    $converted2 = floor($remainder / $conversionRate2);
                    if ($converted1 == 0) {
                        return "$converted2 $unitReport2";
                    } else {
                        return "$converted1 $unitReport1 $converted2 $unitReport2";
                    }
                }
            }
        } else {
            return "$baseStock $item->unit";
        }
    }
}
