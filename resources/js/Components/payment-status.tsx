import { Check, X, Clock } from "lucide-react"
import { cn } from "@/lib/utils"

type PaymentStatus = "paid" | "unpaid" | "partial"

interface PaymentStatusProps {
    status: PaymentStatus
    className?: string
}

const statusConfig = {
    paid: {
        label: "Lunas",
        icon: Check,
        className: "bg-green-100 text-green-800 border-green-200",
    },
    unpaid: {
        label: "Belum",
        icon: X,
        className: "bg-red-100 text-red-800 border-red-200",
    },
    partial: {
        label: "Sebagian",
        icon: Clock,
        className: "bg-yellow-100 text-yellow-800 border-yellow-200",
    },
}

export function PaymentStatus({ status, className }: PaymentStatusProps) {
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