# Journal Dialog Components

Komponen-komponen untuk menampilkan journal dari transaksi dalam bentuk dialog modal yang reusable.

## 📁 Struktur File

```
resources/js/
├── Components/
│   ├── JournalDialog.tsx          # Dialog component utama
│   └── ShowJournalButton.tsx      # Button component yang trigger dialog
├── hooks/
│   └── use-journal-dialog.ts      # Custom hook untuk state management
```

## 🎯 Komponen Utama

### 1. JournalDialog.tsx

Dialog modal untuk menampilkan detail jurnal berdasarkan transaction_id dan transaction_type.

#### Props:
```typescript
interface JournalDialogProps {
    open: boolean;                    // Dialog open state
    onOpenChange: (open: boolean) => void;  // Close callback
    transactionId?: string | number;  // Transaction ID
    transactionType?: string;         // Transaction type
    title?: string;                   // Optional title override
}
```

#### Fitur:
- ✅ Auto-fetch journal data when opened
- ✅ Loading state dengan spinner
- ✅ Error handling dengan retry option
- ✅ Detailed journal information display
- ✅ Responsive table untuk transactions
- ✅ Real-time calculation untuk totals
- ✅ Formatted currency display

### 2. ShowJournalButton.tsx

Button component yang otomatis trigger JournalDialog ketika diklik.

#### Props:
```typescript
interface ShowJournalButtonProps {
    transactionId: string | number;   // Transaction ID
    transactionType: string;          // Transaction type
    variant?: "default" | "outline" | ...; // Button variant
    size?: "default" | "sm" | "lg" | "icon"; // Button size
    children?: React.ReactNode;       // Custom button content
    dialogTitle?: string;             // Custom dialog title
}
```

### 3. useJournalDialog Hook

Custom hook untuk managing journal dialog state.

#### Returns:
```typescript
interface UseJournalDialogReturn {
    isOpen: boolean;                  // Dialog state
    transactionId: string | number | undefined;
    transactionType: string | undefined;
    showJournal: (id, type) => void;  // Show dialog function
    hideJournal: () => void;          // Hide dialog function
    toggleJournal: () => void;        // Toggle dialog function
}
```

## 🚀 Cara Penggunaan

### 1. Quick & Simple Usage

Paling mudah menggunakan `ShowJournalButton`:

```tsx
import ShowJournalButton from "@/Components/ShowJournalButton";

function SalesTable() {
    return (
        <Table>
            <TableBody>
                {sales.map(sale => (
                    <TableRow key={sale.id}>
                        <TableCell>{sale.number}</TableCell>
                        <TableCell>
                            <ShowJournalButton 
                                transactionId={sale.id} 
                                transactionType="Sale" 
                            />
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
```

### 2. Custom Button Styling

```tsx
<ShowJournalButton 
    transactionId={purchase.id} 
    transactionType="Purchase"
    variant="ghost"
    size="sm"
    dialogTitle="Jurnal Pembelian"
>
    <Eye className="h-4 w-4 mr-1" />
    Lihat Jurnal
</ShowJournalButton>
```

### 3. Hook-based Usage (Advanced)

Untuk kontrol yang lebih fleksibel:

```tsx
import { useJournalDialog } from "@/hooks/use-journal-dialog";
import JournalDialog from "@/Components/JournalDialog";

function MyComponent() {
    const journalDialog = useJournalDialog();

    const handleShowJournal = (transactionId: string) => {
        journalDialog.showJournal(transactionId, 'CashIn');
    };

    return (
        <div>
            <Button onClick={() => handleShowJournal('123')}>
                Show Journal
            </Button>
            
            <JournalDialog
                open={journalDialog.isOpen}
                onOpenChange={journalDialog.hideJournal}
                transactionId={journalDialog.transactionId}
                transactionType={journalDialog.transactionType}
                title="Detail Jurnal Kas Masuk"
            />
        </div>
    );
}
```

### 4. Direct Dialog Usage

```tsx
import JournalDialog from "@/Components/JournalDialog";

function CustomComponent() {
    const [showDialog, setShowDialog] = useState(false);

    return (
        <>
            <Button onClick={() => setShowDialog(true)}>
                View Journal
            </Button>
            
            <JournalDialog
                open={showDialog}
                onOpenChange={setShowDialog}
                transactionId="123"
                transactionType="Sale"
                title="Detail Jurnal Penjualan"
            />
        </>
    );
}
```

## 📝 Transaction Types

Komponen mendukung semua transaction types yang ada:

- `Sale` - Penjualan
- `Purchase` - Pembelian  
- `CashIn` - Kas Masuk
- `CashOut` - Kas Keluar
- `CashTransfer` - Transfer Kas
- `SalesReturn` - Retur Penjualan
- `PurchaseReturn` - Retur Pembelian
- `ItemIn` - Barang Masuk
- `ItemOut` - Barang Keluar
- `Receivable` - Piutang
- `Debt` - Hutang
- `Asset` - Aset
- Dan lainnya...

## 🎨 UI Features

### Dialog Layout:
1. **Header Info**: Nomor jurnal, tanggal, divisi, keterangan
2. **Transaction Table**: Detail debit/kredit per akun
3. **Summary**: Total debit dan kredit
4. **Responsive Design**: Mobile-friendly layout

### States:
- **Loading**: Spinner dengan pesan loading
- **Error**: Error message dengan retry button
- **Success**: Full journal data display
- **Empty**: Pesan jika journal tidak ditemukan

## 🔧 API Integration

Komponen menggunakan endpoint:
```
GET /admin/journal/find-by-transaction?transaction_id=123&transaction_type=Sale
```

Response format:
```json
{
    "data": {
        "id": "1",
        "number": "JRN001",
        "date": "2024-01-01",
        "description": "Penjualan barang",
        "division": {
            "code": "DIV001",
            "name": "Divisi Utama"
        },
        "transactions": [
            {
                "id": "1",
                "debit": 100000,
                "credit": 0,
                "chart_of_account": {
                    "code": "1001",
                    "name": "Kas"
                }
            }
        ]
    }
}
```

## ⚡ Performance

- **Lazy Loading**: Data di-fetch hanya saat dialog dibuka
- **Auto Cleanup**: State direset ketika dialog ditutup
- **Error Boundaries**: Proper error handling tanpa crash
- **Memoization**: Optimized re-renders

## 🎯 Use Cases

1. **Transaction Lists**: Tambah button "Lihat Jurnal" di setiap row
2. **Detail Pages**: Show journal button di halaman detail transaksi
3. **Reports**: Link ke journal dari laporan
4. **Audit Trail**: Tracking jurnal untuk audit
5. **Debugging**: Tools untuk developer debug jurnal

## 🔒 Error Handling

- **404**: "Jurnal tidak ditemukan untuk transaksi ini"
- **Network Error**: "Gagal memuat data jurnal. Silakan coba lagi"
- **Validation Error**: Tampilkan error validation
- **Retry Mechanism**: Tombol "Coba lagi" untuk retry

Komponen ini menyediakan cara yang konsisten dan user-friendly untuk menampilkan journal dari berbagai jenis transaksi di seluruh aplikasi.
