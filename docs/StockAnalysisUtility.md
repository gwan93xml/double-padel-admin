# Stock Analysis Utility Documentation

## Overview
`ActualStockService` adalah utility untuk menghitung stok actual berdasarkan formula:
**ItemIn - ItemOut + Purchase - Sale**

Service ini juga menyediakan fungsi untuk membandingkan dengan batch stock dan melakukan rekonsiliasi.

## Main Methods

### 1. calculateActualStock($itemId, $asOfDate = null)
Menghitung stok actual untuk satu item.

```php
$actualStockService = new ActualStockService();
$stock = $actualStockService->calculateActualStock(123); // Item ID 123
$stockAsOfYesterday = $actualStockService->calculateActualStock(123, '2024-12-31');
```

### 2. calculateBulkActualStock($itemIds, $asOfDate = null)
Menghitung stok actual untuk multiple items sekaligus.

```php
$stocks = $actualStockService->calculateBulkActualStock([123, 124, 125]);
// Returns: [123 => 100, 124 => 50, 125 => 0]
```

### 3. getStockBreakdown($itemId, $asOfDate = null)
Mendapatkan detail breakdown perhitungan stok.

```php
$breakdown = $actualStockService->getStockBreakdown(123);
/* Returns:
{
    "item_in": 200,
    "item_out": 50,
    "purchase": 100,
    "sale": 80,
    "actual_stock": 170,
    "calculation": "200 - 50 + 100 - 80"
}
*/
```

### 4. getStockMovements($itemId, $startDate = null, $endDate = null)
Mendapatkan history pergerakan stok.

```php
$movements = $actualStockService->getStockMovements(123, '2024-01-01', '2024-12-31');
```

### 5. compareWithBatchStock($itemId)
Membandingkan actual stock dengan batch stock.

```php
$comparison = $actualStockService->compareWithBatchStock(123);
/* Returns:
{
    "actual_stock": 170,
    "batch_stock": 165,
    "difference": 5,
    "is_matching": false
}
*/
```

### 6. getStockVarianceReport($threshold = 0)
Mendapatkan laporan variance untuk semua item.

```php
$variances = $actualStockService->getStockVarianceReport(10); // Only items with variance > 10
```

### 7. reconcileBatchStock($itemId, $createJournalEntry = true)
Merekonsiliasi batch stock dengan actual stock.

```php
$result = $actualStockService->reconcileBatchStock(123, true);
/* Returns:
{
    "status": "reconciled",
    "message": "Stock reconciled with IN adjustment of 5",
    "adjustment_type": "IN",
    "adjustment_quantity": 5,
    "previous_batch_stock": 165,
    "actual_stock": 170,
    "difference": 5
}
*/
```

## Integration dengan StockReportService

StockReportService telah diupdate untuk menggunakan ActualStockService:

```php
// Sebelum
$stockReport = new StockReportService();
$report = $stockReport->getStockReport($request);

// Sekarang menggunakan actual stock calculation
$stockReport = new StockReportService(new ActualStockService());
$report = $stockReport->getStockReport($request);
```

## API Endpoints

### GET /admin/stock-analysis/item/{itemId}/actual-stock
Mendapatkan detail actual stock untuk satu item.

### GET /admin/stock-analysis/comparison
Membandingkan batch stock vs actual stock untuk semua item.
- Query parameters: `search`, `discrepancies_only=true`

### POST /admin/stock-analysis/bulk-actual-stock
Menghitung bulk actual stock.
- Body: `{"item_ids": [123, 124, 125], "as_of_date": "2024-12-31"}`

### GET /admin/stock-analysis/variance-report
Mendapatkan laporan variance.
- Query parameters: `threshold=10`

### POST /admin/stock-analysis/item/{itemId}/reconcile
Merekonsiliasi stok untuk satu item.
- Body: `{"create_journal_entry": true}`

## Usage Examples

### 1. Cek seluruh item yang ada perbedaan stok
```bash
GET /admin/stock-analysis/comparison?discrepancies_only=true
```

### 2. Lihat laporan variance dengan threshold
```bash
GET /admin/stock-analysis/variance-report?threshold=50
```

### 3. Rekonsiliasi semua item yang ada perbedaan
```php
$stockReport = new StockReportService(new ActualStockService());
$comparisons = $stockReport->getStockComparison(new Request());

foreach ($comparisons as $comparison) {
    if (!$comparison['is_matching']) {
        $actualStockService->reconcileBatchStock($comparison['item']->id, true);
    }
}
```

### 4. Monitor item tertentu
```php
$itemId = 123;
$movements = $actualStockService->getStockMovements($itemId, now()->subDays(30));
$breakdown = $actualStockService->getStockBreakdown($itemId);
$comparison = $actualStockService->compareWithBatchStock($itemId);
```

## Benefits

1. **Akurasi Stok**: Menghitung stok berdasarkan transaksi actual (ItemIn/Out + Purchase/Sale)
2. **Transparansi**: Breakdown calculation yang jelas
3. **Rekonsiliasi Otomatis**: Bisa otomatis memperbaiki perbedaan stok
4. **Audit Trail**: History movements yang lengkap
5. **Variance Analysis**: Laporan perbedaan yang mudah dianalisa
6. **Bulk Operations**: Operasi untuk banyak item sekaligus

## Best Practices

1. Jalankan stock comparison secara berkala untuk memastikan data konsisten
2. Set threshold yang reasonable untuk variance report
3. Selalu backup data sebelum melakukan bulk reconciliation
4. Monitor variance report untuk identify pattern masalah stok
5. Use as_of_date parameter untuk analisa historis
