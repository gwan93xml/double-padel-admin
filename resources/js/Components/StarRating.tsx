import { Star } from "lucide-react";
import { cn } from "@/lib/utils";

interface StarRatingProps {
    value: number;
    onChange: (rating: number) => void;
    error?: string;
    disabled?: boolean;
    size?: "sm" | "md" | "lg";
}

const sizeMap = {
    sm: "w-4 h-4",
    md: "w-6 h-6",
    lg: "w-8 h-8",
};

export default function StarRating({
    value,
    onChange,
    error,
    disabled = false,
    size = "lg",
}: StarRatingProps) {
    return (
        <div className="flex flex-col gap-2">
            <div className="flex gap-2">
                {[1, 2, 3, 4, 5].map((star) => (
                    <button
                        key={star}
                        type="button"
                        disabled={disabled}
                        onClick={() => onChange(star)}
                        className={cn(
                            "transition-all",
                            disabled && "opacity-50 cursor-not-allowed"
                        )}
                    >
                        <Star
                            className={cn(
                                sizeMap[size],
                                star <= value
                                    ? "fill-yellow-400 text-yellow-400"
                                    : "text-gray-300"
                            )}
                        />
                    </button>
                ))}
            </div>
            {error && (
                <p className="text-sm text-red-500">{error}</p>
            )}
        </div>
    );
}
