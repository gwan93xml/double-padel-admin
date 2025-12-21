interface CourtScheduleStatusProps {
    status: 'available' | 'booked' | 'waiting_for_payment';
}

export default function CourtScheduleStatus({ status }: CourtScheduleStatusProps) {
    const statusConfig: Record<string, { label: string; className: string }> = {
        available: {
            label: 'Tersedia',
            className: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        },
        booked: {
            label: 'Dipesan',
            className: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
        },
        waiting_for_payment: {
            label: 'Menunggu Pembayaran',
            className: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        },
    };

    const config = statusConfig[status] || statusConfig.available;

    return (
        <span className={`inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${config.className}`}>
            {config.label}
        </span>
    );
}
