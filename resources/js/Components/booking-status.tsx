import { CheckCircle, Clock, XCircle, CheckCheck } from "lucide-react"
import { cn } from "@/lib/utils"

type BookingStatus = "pending" | "confirmed" | "cancelled" | "completed"

interface BookingStatusProps {
    status: BookingStatus
    className?: string
}

const statusConfig = {
    pending: {
        label: "Menunggu",
        icon: Clock,
        className: "bg-yellow-100 text-yellow-800 border-yellow-200",
    },
    confirmed: {
        label: "Dikonfirmasi",
        icon: CheckCircle,
        className: "bg-blue-100 text-blue-800 border-blue-200",
    },
    cancelled: {
        label: "Dibatalkan",
        icon: XCircle,
        className: "bg-red-100 text-red-800 border-red-200",
    },
    completed: {
        label: "Selesai",
        icon: CheckCheck,
        className: "bg-green-100 text-green-800 border-green-200",
    },
}

export function BookingStatus({ status, className }: BookingStatusProps) {
    const config = statusConfig[status]
    const Icon = config.icon

    return (
        <div
            className={cn(
                "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-medium",
                config.className,
                className,
            )}
        >
            <Icon className="h-3 w-3" />
            {config.label}
        </div>
    )
}
