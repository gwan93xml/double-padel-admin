
import { useEffect, useState } from "react"
import { format, subDays, subMonths } from "date-fns"
import { CalendarIcon } from "lucide-react"
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, XAxis, YAxis } from "recharts"

import { cn } from "@/lib/utils"
import { Button } from "@/Components/ui/button"
import { Calendar } from "@/Components/ui/calendar"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card"
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/Components/ui/chart"
import { Popover, PopoverContent, PopoverTrigger } from "@/Components/ui/popover"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select"
import axios from "axios"
import moment from "moment"
import { parse } from "path"

// Opsi rentang waktu
const timeRangeOptions = [
    { value: "7d", label: "7 Hari Terakhir" },
    { value: "30d", label: "30 Hari Terakhir" },
    { value: "90d", label: "90 Hari Terakhir" },
    { value: "6m", label: "6 Bulan Terakhir" },
    { value: "custom", label: "Kustom" },
]

export default function PurchaseChart() {
    const [timeRange, setTimeRange] = useState("30d")
    const [dateRange, setDateRange] = useState({
        from: subDays(new Date(), 30),
        to: new Date(),
    })
    const [isCalendarOpen, setIsCalendarOpen] = useState(false)

    const [purchases, setPurchases] = useState([])


    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat("id-ID").format(value)
    }

    const [totalPurchases, setTotalPurchases] = useState(0)
    const [avgDailySales, setAvgDailySales] = useState(0)
    const [maxSales, setMaxSales] = useState(0)

    const handleTimeRangeChange = (value: string) => {
        setTimeRange(value)
        if (value !== "custom") {
            setIsCalendarOpen(false)
        }
    }

    useEffect(() => {
        async function fetchData() {
            const { data } = await axios.get("/admin/purchase/chart", {
                params: {
                    time_range: timeRange,
                    start_date: moment(dateRange.from).format("YYYY-MM-DD"),
                    end_date: moment(dateRange.to).format("YYYY-MM-DD"),
                },
            })
            const _purchases = data.data.map((item: any) => ({
                date: item.date,
                purchases: parseFloat(item.purchases),
            }))
            const total = _purchases.reduce((acc: number, item: any) => acc + parseFloat(item.purchases), 0)
            const avg = total / _purchases.length
            const max = Math.max(..._purchases.map((item: any) => item.purchases))
            setTotalPurchases(total)
            setAvgDailySales(avg)
            setMaxSales(max)
            setPurchases(_purchases)
        }
        fetchData()
    }, [timeRange, dateRange])

    return (
        <Card>
            <CardHeader className="pb-4">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <CardTitle>Grafik Pembelian</CardTitle>
                        <CardDescription>Analisis pembelian berdasarkan rentang waktu</CardDescription>
                    </div>
                    <div className="flex flex-col sm:flex-row gap-2">
                        <Select value={timeRange} onValueChange={handleTimeRangeChange}>
                            <SelectTrigger className="w-[180px]">
                                <SelectValue placeholder="Pilih rentang waktu" />
                            </SelectTrigger>
                            <SelectContent>
                                {timeRangeOptions.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {timeRange === "custom" && (
                            <Popover open={isCalendarOpen} onOpenChange={setIsCalendarOpen}>
                                <PopoverTrigger asChild>
                                    <Button
                                        variant="outline"
                                        className={cn(
                                            "w-[280px] justify-start text-left font-normal",
                                            !dateRange && "text-muted-foreground",
                                        )}
                                    >
                                        <CalendarIcon className="mr-2 h-4 w-4" />
                                        {dateRange?.from ? (
                                            dateRange.to ? (
                                                <>
                                                    {format(dateRange.from, "dd/MM/yyyy")} - {format(dateRange.to, "dd/MM/yyyy")}
                                                </>
                                            ) : (
                                                format(dateRange.from, "dd/MM/yyyy")
                                            )
                                        ) : (
                                            <span>Pilih tanggal</span>
                                        )}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent className="w-auto p-0" align="start">
                                    <Calendar
                                        initialFocus
                                        mode="range"
                                        defaultMonth={dateRange?.from}
                                        selected={dateRange}
                                        onSelect={(range) => {
                                            setDateRange({
                                                from: range?.from || subDays(new Date(), 30),
                                                to: range?.to || new Date(),
                                            })
                                        }}
                                        numberOfMonths={2}
                                    />
                                </PopoverContent>
                            </Popover>
                        )}
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <Card className="mb-6">
                        <CardHeader className="pb-2">
                            <CardDescription>Total Pembelian</CardDescription>
                            <CardTitle className="text-xl">{formatCurrency(totalPurchases)}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="mb-6">
                        <CardHeader className="pb-2">
                            <CardDescription>Rata-rata Harian</CardDescription>
                            <CardTitle className="text-xl">{formatCurrency(avgDailySales)}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card className="mb-6">
                        <CardHeader className="pb-2">
                            <CardDescription>Pembelian Tertinggi</CardDescription>
                            <CardTitle className="text-xl">{formatCurrency(maxSales)}</CardTitle>
                        </CardHeader>
                    </Card>
                </div>
                <div className="min-h-[300px]">
                    <ChartContainer
                        config={{
                            purchases: {
                                label: "Pembelian",
                                color: "hsl(var(--chart-1))",
                            },
                        }}
                    >
                        <ResponsiveContainer width="100%" height="100%">
                            <BarChart data={purchases} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} />
                                <XAxis
                                    dataKey="date"
                                    tickFormatter={(date) => {
                                        // Jika data lebih dari 30 hari, tampilkan format yang lebih ringkas
                                        if (purchases.length > 30) {
                                            return format(new Date(date), "dd/MM")
                                        }
                                        return format(new Date(date), "dd/MM/yyyy")
                                    }}
                                    tick={{ fontSize: 12 }}
                                    tickMargin={0}
                                />
                                <YAxis
                                    tickFormatter={(value) => `${formatCurrency(value / 1000000)}Jt`}
                                    tick={{ fontSize: 12 }}
                                    tickMargin={0}
                                />
                                <ChartTooltip
                                    content={
                                        <ChartTooltipContent
                                            formatter={(value) => `${formatCurrency((value as number) / 1000000)}Jt`}
                                            labelFormatter={(label) => format(new Date(label as string), "dd MMMM yyyy")}
                                        />
                                    }
                                />
                                <Bar dataKey="purchases" fill="blue" radius={[4, 4, 0, 0]} />
                            </BarChart>
                        </ResponsiveContainer>
                    </ChartContainer>
                </div>
            </CardContent>
        </Card>
    )
}
