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
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/Components/ui/alert-dialog";
import { router, usePage } from "@inertiajs/react";
import { ChevronLeft, ChevronRight, Edit, Trash2 } from "lucide-react";
import { Court, CourtSchedule } from "@/types/court-schedule-type";
import { useState } from "react";

interface Props {
    courts: Court[];
    schedules: CourtSchedule[];
    selectedCourtId: number | null;
    currentMonth: number;
    currentYear: number;
}

export default function Calendar({
    courts,
    schedules,
    selectedCourtId,
    currentMonth,
    currentYear,
}: Props) {
    const { can } = usePage().props;
    const [selectedDay, setSelectedDay] = useState<number | null>(null);
    const [deleteScheduleId, setDeleteScheduleId] = useState<number | null>(null);

    const getDaysInMonth = (year: number, month: number) => {
        return new Date(year, month, 0).getDate();
    };

    const getFirstDayOfMonth = (year: number, month: number) => {
        return new Date(year, month - 1, 1).getDay();
    };

    const monthName = new Date(currentYear, currentMonth - 1).toLocaleDateString("id-ID", {
        month: "long",
        year: "numeric",
    });

    const daysInMonth = getDaysInMonth(currentYear, currentMonth);
    const firstDayOfMonth = getFirstDayOfMonth(currentYear, currentMonth);

    // Adjust for Sunday = 0 to Monday = 0
    const firstDay = firstDayOfMonth === 0 ? 6 : firstDayOfMonth - 1;

    const days = [];
    for (let i = 0; i < firstDay; i++) {
        days.push(null);
    }
    for (let i = 1; i <= daysInMonth; i++) {
        days.push(i);
    }

    const getSchedulesForDate = (day: number) => {
        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
        return schedules.filter((s) => {
            // Extract only the date part from datetime (YYYY-MM-DD)
            const scheduleDate = s.date.split(' ')[0];
            return scheduleDate === dateStr;
        });
    };

    const getStatusSummary = (day: number) => {
        const daySchedules = getSchedulesForDate(day);
        const available = daySchedules.filter((s) => s.status === "available").length;
        const booked = daySchedules.filter((s) => s.status === "booked").length;
        const closed = daySchedules.filter((s) => s.status === "closed").length;
        return { available, booked, closed, total: daySchedules.length };
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case "available":
                return "bg-green-100 text-green-800";
            case "booked":
                return "bg-yellow-100 text-yellow-800";
            case "closed":
                return "bg-red-100 text-red-800";
            default:
                return "bg-gray-100 text-gray-800";
        }
    };

    const handlePrevMonth = () => {
        let newMonth = currentMonth - 1;
        let newYear = currentYear;
        if (newMonth === 0) {
            newMonth = 12;
            newYear--;
        }
        const params = new URLSearchParams();
        if (selectedCourtId) params.append("court_id", selectedCourtId.toString());
        params.append("month", newMonth.toString());
        params.append("year", newYear.toString());
        router.visit(`/court-schedule/calendar?${params.toString()}`);
    };

    const handleNextMonth = () => {
        let newMonth = currentMonth + 1;
        let newYear = currentYear;
        if (newMonth === 13) {
            newMonth = 1;
            newYear++;
        }
        const params = new URLSearchParams();
        if (selectedCourtId) params.append("court_id", selectedCourtId.toString());
        params.append("month", newMonth.toString());
        params.append("year", newYear.toString());
        router.visit(`/court-schedule/calendar?${params.toString()}`);
    };

    const handleCourtChange = (courtId: string) => {
        const params = new URLSearchParams();
        if (courtId) params.append("court_id", courtId);
        params.append("month", currentMonth.toString());
        params.append("year", currentYear.toString());
        router.visit(`/court-schedule/calendar?${params.toString()}`);
    };

    const handleDeleteSchedule = () => {
        if (deleteScheduleId) {
            router.delete(`/court-schedule/${deleteScheduleId}`, {
                onSuccess: () => {
                    setDeleteScheduleId(null);
                },
            });
        }
    };

    return (
        <div className="p-5 space-y-4">
            <Card>
                <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <CardTitle>Kalender Jadwal Lapangan</CardTitle>
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() => {
                                    const params = new URLSearchParams();
                                    if (selectedCourtId) params.append("court_id", selectedCourtId.toString());
                                    params.append("year", currentYear.toString());
                                    router.visit(`/court-schedule/year?${params.toString()}`);
                                }}
                            >
                                Tahunan
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
                            {/* Month Navigation */}
                            <div className="flex items-center justify-between mb-6">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={handlePrevMonth}
                                >
                                    <ChevronLeft className="w-4 h-4" />
                                </Button>
                                <h2 className="text-lg font-semibold">{monthName}</h2>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={handleNextMonth}
                                >
                                    <ChevronRight className="w-4 h-4" />
                                </Button>
                            </div>

                            {/* Calendar Grid */}
                            <div className="space-y-3">
                                {/* Days Header */}
                                <div className="grid grid-cols-7 gap-2">
                                    {["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"].map((day) => (
                                        <div
                                            key={day}
                                            className="text-center font-semibold text-sm text-gray-600 py-2"
                                        >
                                            {day}
                                        </div>
                                    ))}
                                </div>

                                {/* Calendar Days */}
                                <div className="grid grid-cols-7 gap-2">
                                    {days.map((day, index) => (
                                        <div
                                            key={index}
                                            className={`min-h-32 p-2 border rounded-lg cursor-pointer transition-all ${
                                                day === null
                                                    ? "bg-gray-50"
                                                    : "   hover:border-blue-300"
                                            }`}
                                            onClick={() => day && setSelectedDay(day)}
                                        >
                                            {day && (
                                                <>
                                                    <div className="font-semibold text-sm mb-2">
                                                        {day}
                                                    </div>
                                                    <div className="space-y-1">
                                                        {/* Summary view */}
                                                        {getStatusSummary(day).total > 0 ? (
                                                            <div className="space-y-1">
                                                                {getStatusSummary(day).available > 0 && (
                                                                    <div className="text-xs">
                                                                        <Badge className="bg-green-100 text-green-800">
                                                                            {getStatusSummary(day).available} Tersedia
                                                                        </Badge>
                                                                    </div>
                                                                )}
                                                                {getStatusSummary(day).booked > 0 && (
                                                                    <div className="text-xs">
                                                                        <Badge className="bg-yellow-100 text-yellow-800">
                                                                            {getStatusSummary(day).booked} Dipesan
                                                                        </Badge>
                                                                    </div>
                                                                )}
                                                                {getStatusSummary(day).closed > 0 && (
                                                                    <div className="text-xs">
                                                                        <Badge className="bg-red-100 text-red-800">
                                                                            {getStatusSummary(day).closed} Ditutup
                                                                        </Badge>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        ) : (
                                                            <p className="text-gray-400 text-xs">Tidak ada jadwal</p>
                                                        )}
                                                    </div>
                                                </>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </>
                    ) : (
                        <div className="text-center py-12">
                            <p className="text-gray-500">Pilih lapangan untuk melihat jadwal</p>
                        </div>
                    )}

                    {/* Dialog for detailed schedule */}
                    {selectedDay && (
                        <Dialog open={selectedDay !== null} onOpenChange={() => setSelectedDay(null)}>
                            <DialogContent className="max-w-2xl max-h-[80vh] overflow-y-auto">
                                <DialogHeader>
                                    <DialogTitle>
                                        Jadwal {new Date(currentYear, currentMonth - 1, selectedDay).toLocaleDateString("id-ID", {
                                            weekday: "long",
                                            year: "numeric",
                                            month: "long",
                                            day: "numeric",
                                        })}
                                    </DialogTitle>
                                </DialogHeader>
                                <div className="space-y-3">
                                    {getSchedulesForDate(selectedDay).length > 0 ? (
                                        getSchedulesForDate(selectedDay).map((schedule) => (
                                            <div
                                                key={schedule.id}
                                                className="border rounded-lg p-4 space-y-2"
                                            >
                                                <div className="flex items-start justify-between">
                                                    <div>
                                                        <div className="font-semibold text-lg">
                                                            {schedule.start_time} - {schedule.end_time}
                                                        </div>
                                                        <div className="text-gray-600">
                                                            {new Intl.NumberFormat("id-ID", {
                                                                style: "currency",
                                                                currency: "IDR",
                                                            }).format(schedule.price)}
                                                        </div>
                                                    </div>
                                                    <Badge className={getStatusColor(schedule.status)}>
                                                        {schedule.status === "available"
                                                            ? "Tersedia"
                                                            : schedule.status === "booked"
                                                                ? "Dipesan"
                                                                : "Ditutup"}
                                                    </Badge>
                                                </div>
                                                {schedule.user && (
                                                    <div className="text-sm text-gray-600">
                                                        <span className="font-medium">Diatur oleh:</span> {schedule.user.name}
                                                    </div>
                                                )}
                                                <div className="flex gap-2 pt-2">
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() =>
                                                            router.visit(`/court-schedule/${schedule.id}/edit`)
                                                        }
                                                    >
                                                        <Edit className="w-4 h-4 mr-1" />
                                                        Edit
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="destructive"
                                                        onClick={() => setDeleteScheduleId(schedule.id)}
                                                    >
                                                        <Trash2 className="w-4 h-4 mr-1" />
                                                        Hapus
                                                    </Button>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <p className="text-gray-500 text-center py-8">Tidak ada jadwal untuk tanggal ini</p>
                                    )}
                                </div>
                            </DialogContent>
                        </Dialog>
                    )}

                    {/* Delete Confirmation Dialog */}
                    <AlertDialog open={deleteScheduleId !== null} onOpenChange={() => setDeleteScheduleId(null)}>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Hapus Jadwal</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Apakah Anda yakin ingin menghapus jadwal ini? Tindakan ini tidak dapat dibatalkan.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <div className="flex gap-2 justify-end">
                                <AlertDialogCancel>Batal</AlertDialogCancel>
                                <AlertDialogAction
                                    onClick={handleDeleteSchedule}
                                    className="bg-red-600 hover:bg-red-700"
                                >
                                    Hapus
                                </AlertDialogAction>
                            </div>
                        </AlertDialogContent>
                    </AlertDialog>
                </CardContent>
            </Card>
        </div>
    );
}

Calendar.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: "Dashboard", href: "/" },
            { label: "Master Data", href: "#" },
            { label: "Jadwal Lapangan", href: "/court-schedule" },
            { label: "Kalender", href: "#" },
        ]}
    >
        {page}
    </AuthenticatedLayout>
);
