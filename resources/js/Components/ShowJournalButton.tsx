import { Button } from "@/Components/ui/button";
import JournalDialog from "@/Components/JournalDialog";
import { useJournalDialog } from "@/hooks/use-journal-dialog";
import { Eye } from "lucide-react";

interface ShowJournalButtonProps {
    /**
     * Transaction ID
     */
    transactionId: string | number;
    
    /**
     * Transaction type (e.g., 'Sale', 'Purchase', 'CashIn', etc.)
     */
    transactionType: string;
    
    /**
     * Button variant
     */
    variant?: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link";
    
    /**
     * Button size
     */
    size?: "default" | "sm" | "lg" | "icon";
    
    /**
     * Custom button text
     */
    children?: React.ReactNode;
    
    /**
     * Custom dialog title
     */
    dialogTitle?: string;
}

/**
 * Reusable button component that shows journal dialog when clicked
 * 
 * @example
 * // Basic usage
 * <ShowJournalButton 
 *   transactionId={sale.id} 
 *   transactionType="Sale" 
 * />
 * 
 * @example
 * // Custom styling
 * <ShowJournalButton 
 *   transactionId={purchase.id} 
 *   transactionType="Purchase"
 *   variant="outline"
 *   size="sm"
 *   dialogTitle="Jurnal Pembelian"
 * >
 *   <Eye className="h-4 w-4 mr-1" />
 *   Lihat Jurnal
 * </ShowJournalButton>
 */
export default function ShowJournalButton({
    transactionId,
    transactionType,
    variant = "outline",
    size = "sm",
    children,
    dialogTitle
}: ShowJournalButtonProps) {
    const { isOpen, hideJournal, showJournal } = useJournalDialog();

    const handleClick = () => {
        showJournal(transactionId, transactionType);
    };

    return (
        <>
            <Button 
                variant={variant} 
                size={size}
                onClick={handleClick}
                type="button"
            >
                {children || (
                    <>
                        <Eye className="h-4 w-4 mr-1" />
                        Jurnal
                    </>
                )}
            </Button>
            
            <JournalDialog
                open={isOpen}
                onOpenChange={hideJournal}
                transactionId={transactionId}
                transactionType={transactionType}
                title={dialogTitle}
            />
        </>
    );
}

/**
 * Alternative hook-based usage for more complex scenarios
 * 
 * @example
 * function MyComponent() {
 *   const journalDialog = useJournalDialog();
 * 
 *   const handleViewJournal = (id: string, type: string) => {
 *     journalDialog.showJournal(id, type);
 *   };
 * 
 *   return (
 *     <div>
 *       <button onClick={() => handleViewJournal('123', 'Sale')}>
 *         View Journal
 *       </button>
 *       
 *       <JournalDialog
 *         open={journalDialog.isOpen}
 *         onOpenChange={journalDialog.hideJournal}
 *         transactionId={journalDialog.transactionId}
 *         transactionType={journalDialog.transactionType}
 *       />
 *     </div>
 *   );
 * }
 */
