import { PinIcon } from "lucide-react";
import { Button } from "@/Components/ui/button";

interface PinButtonProps {
    isPinned: boolean;
    onToggle: () => void;
    className?: string;
}

export default function PinButton({ isPinned, onToggle, className = "" }: PinButtonProps) {
    return (
        <div className={`mb-3 pb-3 flex justify-end ${className}`}>
            <Button
                variant={isPinned ? 'ghost' : 'outline'}
                size={'sm'}
                className={isPinned ? 'bg-yellow-400 hover:bg-yellow-400 transition-all' : ''}
                onClick={onToggle}
            >
                <PinIcon
                    className={isPinned ? 'transform rotate-45 transition-all' : 'transition-transform'}
                />
                {isPinned ? 'Pinned' : 'Pin Me!'}
            </Button>
        </div>
    );
}