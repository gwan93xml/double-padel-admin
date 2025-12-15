"use client"

import { Input } from "@/Components/ui/input"
import { ToggleGroup, ToggleGroupItem } from "@/Components/ui/toggle-group"
import { Calendar, CalendarDays, Clock, CalendarRangeIcon as DateRange } from "lucide-react"

type DateType = "year" | "month" | "date" | "range" | "until"

interface DateFilterProps {
    f: any
    setF: any
}
export default function DateFilter({
    f,
    setF
}: DateFilterProps) {

    const dateTypeOptions = [
        { value: "year", label: "Tahun", icon: Calendar },
        { value: "month", label: "Bulan", icon: CalendarDays },
        { value: "date", label: "Tanggal", icon: Clock },
        { value: "range", label: "Range", icon: DateRange },
        { value: "until", label: "Hingga", icon: Calendar },
    ]

    const renderDateInputs = () => {
        switch (f.dateType) {
            case "year":
                return (
                    <div className="space-y-2">
                        <Input
                            id="year"
                            type="number"
                            placeholder="2024"
                            min="1900"
                            max="2100"
                            value={f.year}
                            onChange={(e) => setF({ ...f, year: e.target.value })}
                        />
                    </div>
                )

            case "month":
                return (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Input
                                id="monthFrom"
                                type="month"
                                value={f.monthFrom}
                                onChange={(e) => setF({ ...f, monthFrom: e.target.value })}
                            />
                        </div>
                        <div className="space-y-2">
                            <Input
                                id="monthTo"
                                type="month"
                                value={f.monthTo}
                                onChange={(e) => setF({ ...f, monthTo: e.target.value })}
                            />
                        </div>
                    </div>
                )

            case "date":
                return (
                    <div className="space-y-2">
                        <Input id="date" type="date" value={f.date} onChange={(e) => setF({ ...f, date: e.target.value })} />
                    </div>
                )

            case "range":
                return (
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div className="space-y-2">
                            <Input
                                id="dateFrom"
                                type="date"
                                value={f.dateFrom}
                                onChange={(e) => setF({ ...f, dateFrom: e.target.value })}
                            />
                        </div>
                        <div className="space-y-2">
                            <Input
                                id="dateTo"
                                type="date"
                                value={f.dateTo}
                                onChange={(e) => setF({ ...f, dateTo: e.target.value })}
                            />
                        </div>
                    </div>
                )

            case "until":
                return (
                    <div className="space-y-2">
                        <Input
                            id="untilDate"
                            type="date"
                            value={f.untilDate}
                            onChange={(e) => setF({ ...f, untilDate: e.target.value })}
                        />
                    </div>
                )

            default:
                return null
        }
    }

    return (
        <div className="w-full">
            <div>
                <ToggleGroup
                    type="single"
                    value={f.dateType}
                    onValueChange={(value) => value && setF({ ...f, dateType: value as DateType })}
                    className="grid grid-cols-2 md:grid-cols-5 gap-2"
                >
                    {dateTypeOptions.map((option) => {
                        const Icon = option.icon
                        return (
                            <ToggleGroupItem
                                key={option.value}
                                value={option.value}
                            >
                                <Icon className="h-4 w-4" />
                                <span className="text-xs">{option.label}</span>
                            </ToggleGroupItem>
                        )
                    })}
                </ToggleGroup>
            </div>
            < div className="space-y-4 mt-1">{renderDateInputs()}</div >
        </div>
    )
}
