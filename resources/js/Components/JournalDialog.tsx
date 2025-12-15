import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { useEffect, useState } from "react";
import axios from "axios";
import { useToast } from "@/hooks/use-toast";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import CurrencyFormatter from "@/Components/ui/currency-formatter";
import { Loader2 } from "lucide-react";
import moment from "moment";

interface JournalDialogProps {
    /**
     * Dialog open state
     */
    open: boolean;
    
    /**
     * Callback when dialog should close
     */
    onOpenChange: (open: boolean) => void;
    
    /**
     * Transaction ID to find journal for
     */
    transactionId?: string | number;
    
    /**
     * Transaction type to find journal for
     */
    transactionType?: string;
    
    /**
     * Optional title override
     */
    title?: string;
}

interface JournalData {
    id: string;
    number: string;
    date: string;
    notes: string;
    division?: {
        name: string;
        code: string;
    };
    transactions: Array<{
        id: string;
        debit: number;
        credit: number;
        journal_id: string;
        chart_of_account: {
            code: string;
            name: string;
        };
        notes : string
    }>;
}

export default function JournalDialog({
    open,
    onOpenChange,
    transactionId,
    transactionType,
    title = "Detail Jurnal"
}: JournalDialogProps) {
    const [journal, setJournal] = useState<JournalData | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const { toast } = useToast();

    /**
     * Fetch journal data when dialog opens and params are available
     */
    useEffect(() => {
        if (open && transactionId && transactionType) {
            fetchJournal();
        }
    }, [open, transactionId, transactionType]);

    /**
     * Reset state when dialog closes
     */
    useEffect(() => {
        if (!open) {
            setJournal(null);
            setError(null);
        }
    }, [open]);

    /**
     * Fetch journal data from API
     */
    const fetchJournal = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get('/admin/journal/find-by-transaction', {
                params: {
                    transaction_id: transactionId,
                    transaction_type: transactionType
                }
            });

            setJournal(response.data.data);
        } catch (error: any) {
            console.error('Error fetching journal:', error);
            
            if (error.response?.status === 404) {
                setError('Jurnal tidak ditemukan untuk transaksi ini.');
            } else {
                setError('Gagal memuat data jurnal. Silakan coba lagi.');
                toast({
                    variant: 'destructive',
                    title: "Error",
                    description: "Gagal memuat data jurnal",
                });
            }
        } finally {
            setLoading(false);
        }
    };

    /**
     * Calculate total debit
     */
    const getTotalDebit = () => {
        return journal?.transactions?.reduce((sum, transaction) => sum + transaction.debit, 0) || 0;
    };

    /**
     * Calculate total credit
     */
    const getTotalCredit = () => {
        return journal?.transactions?.reduce((sum, transaction) => sum + transaction.credit, 0) || 0;
    };

    /**
     * Render loading state
     */
    const renderLoading = () => (
        <div className="flex items-center justify-center p-8">
            <Loader2 className="h-8 w-8 animate-spin mr-2" />
            <span>Memuat data jurnal...</span>
        </div>
    );

    /**
     * Render error state
     */
    const renderError = () => (
        <div className="text-center p-8">
            <p className="text-red-500 mb-2">{error}</p>
            <button 
                onClick={fetchJournal}
                className="text-blue-500 hover:text-blue-700 underline"
            >
                Coba lagi
            </button>
        </div>
    );

    /**
     * Render journal content
     */
    const renderJournal = () => {
        if (!journal) return null;

        return (
            <div className="space-y-4">
                {/* Journal Header Info */}
                <div className="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label className="font-semibold text-sm text-gray-600">Nomor Jurnal:</label>
                        <p className="font-mono">{journal.number}</p>
                    </div>
                    <div>
                        <label className="font-semibold text-sm text-gray-600">Tanggal:</label>
                        <p>{moment(journal.date).format('DD MMMM YYYY')}</p>
                    </div>
                    {journal.division && (
                        <div>
                            <label className="font-semibold text-sm text-gray-600">Divisi:</label>
                            <p>{journal.division?.code ?? ''} - {journal.division?.name ?? ''}</p>
                        </div>
                    )}
                    <div>
                        <label className="font-semibold text-sm text-gray-600">Keterangan:</label>
                        <p>{journal.notes}</p>
                    </div>
                </div>

                {/* Journal Transactions Table */}
                <div>
                    <h4 className="font-semibold mb-2">Detail Transaksi</h4>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Akun</TableHead>
                                <TableHead>
                                    Catatan
                                </TableHead>
                                <TableHead className="text-right">Debit</TableHead>
                                <TableHead className="text-right">Kredit</TableHead>

                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {journal.transactions?.map((transaction) => (
                                <TableRow key={transaction.id} className={`${transaction.journal_id !== journal.id ? 'bg-yellow-50' : ''}`}>
                                    <TableCell>
                                        <div>
                                            <div className="font-medium">
                                                {transaction.chart_of_account?.code ?? ''}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {transaction.chart_of_account?.name ?? ''}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        {transaction.notes}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        {transaction.debit > 0 && (
                                            <CurrencyFormatter amount={transaction.debit} />
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        {transaction.credit > 0 && (
                                            <CurrencyFormatter amount={transaction.credit} />
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {/* Totals */}
                <div className="border-t pt-4">
                    <div className="flex justify-between items-center">
                        <span className="font-semibold">Total:</span>
                        <div className="flex space-x-8">
                            <div className="text-right">
                                <div className="text-sm text-gray-600">Debit</div>
                                <div className="font-semibold">
                                    <CurrencyFormatter amount={getTotalDebit()} />
                                </div>
                            </div>
                            <div className="text-right">
                                <div className="text-sm text-gray-600">Kredit</div>
                                <div className="font-semibold">
                                    <CurrencyFormatter amount={getTotalCredit()} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-4xl max-h-[80vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>
                        {title}
                        {transactionType && transactionId && (
                            <span className="text-sm font-normal text-gray-500 ml-2">
                                ({transactionType} #{transactionId})
                            </span>
                        )}
                    </DialogTitle>
                </DialogHeader>
                
                <div className="mt-4">
                    {loading && renderLoading()}
                    {error && !loading && renderError()}
                    {journal && !loading && !error && renderJournal()}
                </div>
            </DialogContent>
        </Dialog>
    );
}
