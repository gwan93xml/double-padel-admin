import { Button } from "./ui/button";
interface ProcessingButtonProps {
    processing?: boolean
    label: string
    processingLabel: string
    onClick?: () => void
    className?: string
    type?: "button" | "submit" | "reset"
    variant?: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link"
}
export default function ProcessingButton({ processing, label, processingLabel, onClick, className, type = "button", variant }: ProcessingButtonProps) {
    return (
        <Button
            type={type}
            disabled={processing}
            onClick={onClick}
            className={className}
            variant={variant}
        >
            {processing ? <><div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> {processingLabel}</> : label}
        </Button>
    )
}