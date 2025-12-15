import { DollarSign } from 'lucide-react'
import { Card } from "@/Components/ui/card"

interface StatsCardProps {
    icon?: React.ReactNode
    title: string
    subtitle: string
    iconBackground?: string
}

export default function StatsCard({
    icon = <DollarSign className="h-6 w-6" />,
    title = "132 Sales",
    subtitle = "12 waiting payments",
    iconBackground = "bg-blue-500"
}: StatsCardProps) {
    return (
        <Card className="p-4">
            <div className="flex items-center gap-4">
                <div className={`${iconBackground} dark:text-white p-3 rounded-lg`}>
                    {icon}
                </div>
                <div className="space-y-0.5">
                    <h3 className="text-base font-medium">{title}</h3>
                    <p className="text-sm text-muted-foreground">{subtitle}</p>
                </div>
            </div>
        </Card>
    )
}