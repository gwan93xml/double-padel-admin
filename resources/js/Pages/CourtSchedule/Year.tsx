import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Badge } from "@/Components/ui/badge";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { router, usePage } from "@inertiajs/react";
import { ChevronLeft, ChevronRight, Calendar } from "lucide-react";
import { useState, useMemo } from "react";

interface MonthlySchedule {
    available: number;
    booked: number;
    closed: number;
    total: number;
}

interface SchedulesByDay {
    [date: string]: {
        available: number;
        booked: number;
        closed: number;
        total: number;
    };
}

interface Props {
    courts: any[];
    monthlySchedules: { [key: number]: MonthlySchedule };
    selectedCourtId: number | null;
    currentYear: number;
    schedules?: any[];
}

const MONTHS = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
];

export default function Year({
    courts,
    monthlySchedules,
    selectedCourtId,
    currentYear,
    schedules = [],
}: Props) {
    const { can } = usePage().props;

    // Build schedules by day for calendar display
    const schedulesByDay = useMemo<SchedulesByDay>(() => {
        const result: SchedulesByDay = {};
        schedules?.forEach((schedule) => {
            const date = schedule.date.split(' ')[0];
            if (!result[date]) {
                result[date] = { available: 0, booked: 0, closed: 0, total: 0 };
            }
            result[date].total++;
            const status = schedule.status as keyof Omit<MonthlySchedule, 'total'>;
            if (status === 'available' || status === 'booked' || status === 'closed') {
                result[date][status]++;
            }
        });
        return result;
    }, [schedules]);

    const getDaysInMonth = (year: number, month: number) => {
        return new Date(year, month, 0).getDate();
    };

    const getFirstDayOfMonth = (year: number, month: number) => {
        return new Date(year, month - 1, 1).getDay();
    };

    const getSchedulesForDay = (day: number, month: number) => {
        const dateStr = `${currentYear}-${String(month).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
        return schedulesByDay[dateStr] || { available: 0, booked: 0, closed: 0, total: 0 };
    };

    const getStatusColor = (available: number, booked: number, closed: number, total: number) => {
        if (total === 0) return "border ";
        if (closed > 0) return "bg-red-50 dark:bg-red-900/30";
        if (booked > 0) return "bg-yellow-50 dark:bg-yellow-900/30";
        return "bg-green-50 dark:bg-green-900/30";
    };

    const handlePrevYear = () => {
        const params = new URLSearchParams();
        if (selectedCourtId) params.append("court_id", selectedCourtId.toString());
        params.append("year", (currentYear - 1).toString());
        router.visit(`/court-schedule/year?${params.toString()}`);
    };

    const handleNextYear = () => {
        const params = new URLSearchParams();
        if (selectedCourtId) params.append("court_id", selectedCourtId.toString());
        params.append("year", (currentYear + 1).toString());
        router.visit(`/court-schedule/year?${params.toString()}`);
    };

    const handleCourtChange = (courtId: string) => {
        const params = new URLSearchParams();
        if (courtId) params.append("court_id", courtId);
        params.append("year", currentYear.toString());
        router.visit(`/court-schedule/year?${params.toString()}`);
    };

    const handleDayClick = (day: number, month: number) => {
        const params = new URLSearchParams();
        if (selectedCourtId) params.append("court_id", selectedCourtId.toString());
        params.append("month", month.toString());
        params.append("year", currentYear.toString());
        router.visit(`/court-schedule/calendar?${params.toString()}`);
    };

    const renderMiniCalendar = (month: number) => {
        const daysInMonth = getDaysInMonth(currentYear, month);
        const firstDayOfMonth = getFirstDayOfMonth(currentYear, month);
        const firstDay = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

        const days = [];
        for (let i = 0; i < firstDay; i++) {
            days.push(null);
        }
        for (let i = 1; i <= daysInMonth; i++) {
            days.push(i);
        }

        return (
            <div key={month} className="border rounded-lg p-3 ">
                <h3 className="font-semibold text-sm mb-2 text-center">{MONTHS[month - 1]}</h3>
                
                {/* Day headers */}
                <div className="grid grid-cols-7 gap-0.5 mb-1">
                    {["S", "M", "T", "W", "T", "F", "S"].map((day, idx) => (
                        <div key={idx} className="text-xs font-semibold text-center text-gray-600 py-1">
                            {["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"][idx]}
                        </div>
                    ))}
                </div>

                {/* Days */}
                <div className="grid grid-cols-7 gap-0.5">
                    {days.map((day, index) => {
                        if (day === null) {
                            return (
                                <div
                                    key={`empty-${index}`}
                                    className="aspect-square  rounded text-xs"
                                />
                            );
                        }

                        const dayStats = getSchedulesForDay(day, month);
                        const bgColor = getStatusColor(
                            dayStats.available,
                            dayStats.booked,
                            dayStats.closed,
                            dayStats.total
                        );

                        return (
                            <div
                                key={day}
                                className={`aspect-square rounded text-xs p-0.5 cursor-pointer transition-all hover:ring-2 hover:ring-blue-400 ${bgColor}`}
                                onClick={() => handleDayClick(day, month)}
                                title={`${MONTHS[month - 1]} ${day}: ${dayStats.total} jadwal (${dayStats.available} tersedia, ${dayStats.booked} dipesan, ${dayStats.closed} ditutup)`}
                            >
                                <div className="h-full flex flex-col justify-between">
                                    <span className="font-semibold ">{day}</span>
                                    {dayStats.total > 0 && (
                                        <div className="text-xs space-y-0.5">
                                            {dayStats.available > 0 && (
                                                <div className="bg-green-300 text-green-800 rounded px-0.5 text-xs font-bold">
                                                    {dayStats.available}
                                                </div>
                                            )}
                                            {dayStats.booked > 0 && (
                                                <div className="bg-yellow-300 text-yellow-800 rounded px-0.5 text-xs font-bold">
                                                    {dayStats.booked}
                                                </div>
                                            )}
                                            {dayStats.closed > 0 && (
                                                <div className="bg-red-300 text-red-800 rounded px-0.5 text-xs font-bold">
                                                    {dayStats.closed}
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        );
    };

    return (
        <div className="p-5 space-y-4">
            <Card>
                <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <CardTitle>Jadwal Lapangan - Tahunan</CardTitle>
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() => {
                                    const params = new URLSearchParams();
                                    if (selectedCourtId) params.append("court_id", selectedCourtId.toString());
                                    params.append("month", (new Date().getMonth() + 1).toString());
                                    params.append("year", currentYear.toString());
                                    router.visit(`/court-schedule/calendar?${params.toString()}`);
                                }}
                            >
                                Bulanan
                            </Button>
                        </div>
                        <div className="w-64">
                            <Select
                                value={selectedCourtId?.toString() || ""}
                                onValueChange={handleCourtChange}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih lapangan" />
                                </SelectTrigger>
                                <SelectContent>
                                    {courts.map((court) => (
                                        <SelectItem key={court.id} value={court.id.toString()}>
                                            {court.venue.name} - {court.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardHeader>
                <CardContent className="space-y-6">
                    {selectedCourtId ? (
                        <>
                            {/* Year Navigation */}
                            <div className="flex items-center justify-between mb-6">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={handlePrevYear}
                                >
                                    <ChevronLeft className="w-4 h-4" />
                                </Button>
                                <h2 className="text-2xl font-semibold">{currentYear}</h2>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={handleNextYear}
                                >
                                    <ChevronRight className="w-4 h-4" />
                                </Button>
                            </div>

                            {/* Mini Calendars Grid */}
                            <div className="grid grid-cols-3 gap-4">
                                {MONTHS.map((_, index) => renderMiniCalendar(index + 1))}
                            </div>
                        </>
                    ) : (
                        <div className="text-center py-12">
                            <p className="text-gray-500">Pilih lapangan untuk melihat jadwal tahunan</p>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}

Year.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: "Dashboard", href: "/" },
            { label: "Master Data", href: "#" },
            { label: "Jadwal Lapangan", href: "/court-schedule" },
            { label: "Tahunan", href: "#" },
        ]}
    >
        {page}
    </AuthenticatedLayout>
);
