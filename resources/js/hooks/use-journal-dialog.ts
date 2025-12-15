import { useState } from 'react';

interface UseJournalDialogReturn {
    /**
     * Dialog open state
     */
    isOpen: boolean;
    
    /**
     * Current transaction ID
     */
    transactionId: string | number | undefined;
    
    /**
     * Current transaction type
     */
    transactionType: string | undefined;
    
    /**
     * Show journal dialog for a transaction
     */
    showJournal: (transactionId: string | number, transactionType: string) => void;
    
    /**
     * Hide journal dialog
     */
    hideJournal: () => void;
    
    /**
     * Toggle journal dialog state
     */
    toggleJournal: () => void;
}

/**
 * Custom hook for managing journal dialog state
 */
export const useJournalDialog = (): UseJournalDialogReturn => {
    const [isOpen, setIsOpen] = useState(false);
    const [transactionId, setTransactionId] = useState<string | number | undefined>();
    const [transactionType, setTransactionType] = useState<string | undefined>();

    /**
     * Show journal dialog with transaction details
     */
    const showJournal = (transactionId: string | number, transactionType: string) => {
        setTransactionId(transactionId);
        setTransactionType(transactionType);
        setIsOpen(true);
    };

    /**
     * Hide journal dialog and reset state
     */
    const hideJournal = () => {
        setIsOpen(false);
        // Reset after animation completes
        setTimeout(() => {
            setTransactionId(undefined);
            setTransactionType(undefined);
        }, 300);
    };

    /**
     * Toggle dialog state
     */
    const toggleJournal = () => {
        setIsOpen(!isOpen);
    };

    return {
        isOpen,
        transactionId,
        transactionType,
        showJournal,
        hideJournal,
        toggleJournal,
    };
};
