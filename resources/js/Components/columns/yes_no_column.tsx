import { cn } from "@/lib/utils"
import { CheckCircle, XCircle } from "lucide-react"

interface YesNoColumnProps {
    value: boolean
    yesLabel?: string
    noLabel?: string
    className?: string
    showIcon?: boolean
}

export function YesNoColumn({ value, yesLabel = "Ya", noLabel = "Tidak", className, showIcon = true }: YesNoColumnProps) {
    return (
        <div
            className={cn(
                "inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium",
                value
                    ? "bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400"
                    : "bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400",
                className,
            )}
        >
            {showIcon && (value ? <CheckCircle className="h-3.5 w-3.5" /> : <XCircle className="h-3.5 w-3.5" />)}
            <span>{value ? yesLabel : noLabel}</span>
        </div>
    )
}
