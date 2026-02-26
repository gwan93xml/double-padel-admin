import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import { Checkbox } from "@/Components/ui/checkbox";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import {
    Tabs,
    TabsContent,
    TabsList,
    TabsTrigger,
} from "@/Components/ui/tabs";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useState, useMemo, useEffect } from "react";
import { Court } from "@/types/court-schedule-type";

interface Props {
    courts: Court[];
}

interface TimeSlot {
    start_time: string;
    end_time: string;
}

interface DateSlots {
    [date: string]: Set<number>;
}

interface SlotPrices {
    [key: string]: string;
}

export default function BulkGenerate({ courts }: Props) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        court_id: '',
        start_date: '',
        end_date: '',
        default_price: '',
        default_normal_price: '',
        status: 'available',
    } as any);

    const [dateSlots, setDateSlots] = useState<DateSlots>({});
    const [slotPrices, setSlotPrices] = useState<SlotPrices>({});
    const [slotNormalPrices, setSlotNormalPrices] = useState<SlotPrices>({});
    const [processing, setProcessing] = useState(false);

    // Generate 24 time slots (00:00-01:00 to 23:00-24:00)
    const timeSlots = useMemo(() => {
        const slots: TimeSlot[] = [];
        for (let i = 0; i < 24; i++) {
            const startHours = String(i).padStart(2, '0');
            const endHours = String(i + 1).padStart(2, '0');
            slots.push({
                start_time: `${startHours}:00`,
                end_time: `${endHours}:00`,
            });
        }
        return slots;
    }, []);

    // Generate dates between start_date and end_date
    const dates = useMemo(() => {
        if (!data.start_date || !data.end_date) return [];

        const dates = [];
        const current = new Date(data.start_date);
        const end = new Date(data.end_date);

        while (current <= end) {
            dates.push(current.toISOString().split('T')[0]);
            current.setDate(current.getDate() + 1);
        }

        return dates;
    }, [data.start_date, data.end_date]);

    // Initialize dateSlots when dates change
    useEffect(() => {
        const newDateSlots: DateSlots = {};
        dates.forEach(date => {
            if (!dateSlots[date]) {
                newDateSlots[date] = new Set();
            } else {
                newDateSlots[date] = new Set(dateSlots[date]);
            }
        });
        setDateSlots(newDateSlots);
    }, [dates]);

    const toggleSlot = (date: string, index: number) => {
        const newDateSlots = { ...dateSlots };
        const newSelected = new Set(newDateSlots[date]);
        const slotKey = `${date}-${index}`;
        
        if (newSelected.has(index)) {
            newSelected.delete(index);
            const newPrices = { ...slotPrices };
            const newNormalPrices = { ...slotNormalPrices };
            delete newPrices[slotKey];
            delete newNormalPrices[slotKey];
            setSlotPrices(newPrices);
            setSlotNormalPrices(newNormalPrices);
        } else {
            newSelected.add(index);
            // Initialize with default price
            const newPrices = { ...slotPrices };
            const newNormalPrices = { ...slotNormalPrices };
            newPrices[slotKey] = data.default_price || '';
            newNormalPrices[slotKey] = data.default_normal_price || data.default_price || '';
            setSlotPrices(newPrices);
            setSlotNormalPrices(newNormalPrices);
        }
        newDateSlots[date] = newSelected;
        setDateSlots(newDateSlots);
    };

    const updateSlotPrice = (date: string, index: number, price: string) => {
        const slotKey = `${date}-${index}`;
        const newPrices = { ...slotPrices };
        newPrices[slotKey] = price;
        setSlotPrices(newPrices);
    };

    const updateSlotNormalPrice = (date: string, index: number, price: string) => {
        const slotKey = `${date}-${index}`;
        const newNormalPrices = { ...slotNormalPrices };
        newNormalPrices[slotKey] = price;
        setSlotNormalPrices(newNormalPrices);
    };

    const selectAllForDate = (date: string) => {
        const newDateSlots = { ...dateSlots };
        const currentSet = newDateSlots[date];
        const newPrices = { ...slotPrices };

        if (currentSet.size === timeSlots.length) {
            // Deselect all
            const newNormalPrices = { ...slotNormalPrices };
            timeSlots.forEach((_, index) => {
                delete newPrices[`${date}-${index}`];
                delete newNormalPrices[`${date}-${index}`];
            });
            newDateSlots[date] = new Set();
            setSlotNormalPrices(newNormalPrices);
        } else {
            // Select all
            newDateSlots[date] = new Set(Array.from({ length: timeSlots.length }, (_, i) => i));
            const newNormalPrices = { ...slotNormalPrices };
            timeSlots.forEach((_, index) => {
                newPrices[`${date}-${index}`] = data.default_price || '';
                newNormalPrices[`${date}-${index}`] = data.default_normal_price || data.default_price || '';
            });
            setSlotNormalPrices(newNormalPrices);
        }
        setDateSlots(newDateSlots);
        setSlotPrices(newPrices);
    };

    const updateDefaultPrice = (price: string) => {
        setData('default_price', price);
        // Update all slot prices with new default
        const newPrices: SlotPrices = {};
        Object.entries(dateSlots).forEach(([date, slotIndices]) => {
            slotIndices.forEach(index => {
                const slotKey = `${date}-${index}`;
                newPrices[slotKey] = price;
            });
        });
        setSlotPrices(newPrices);
    };

    const updateDefaultNormalPrice = (price: string) => {
        setData('default_normal_price', price);
        const newNormalPrices: SlotPrices = {};
        Object.entries(dateSlots).forEach(([date, slotIndices]) => {
            slotIndices.forEach(index => {
                const slotKey = `${date}-${index}`;
                newNormalPrices[slotKey] = price;
            });
        });
        setSlotNormalPrices(newNormalPrices);
    };

    const selectAllAllDates = () => {
        const newDateSlots: DateSlots = {};
        const newPrices: SlotPrices = {};
        const newNormalPrices: SlotPrices = {};

        dates.forEach(date => {
            newDateSlots[date] = new Set(Array.from({ length: timeSlots.length }, (_, i) => i));
            timeSlots.forEach((_, index) => {
                newPrices[`${date}-${index}`] = data.default_price || '';
                newNormalPrices[`${date}-${index}`] = data.default_normal_price || data.default_price || '';
            });
        });

        setDateSlots(newDateSlots);
        setSlotPrices(newPrices);
        setSlotNormalPrices(newNormalPrices);
    };

    const deselectAllAllDates = () => {
        setDateSlots({});
        setSlotPrices({});
        setSlotNormalPrices({});
    };

    const totalSelectedSlots = Object.values(dateSlots).reduce((sum, set) => sum + set.size, 0);

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    async function handleSubmit() {
        const hasSlotsSelected = Object.values(dateSlots).some(set => set.size > 0);
        if (!hasSlotsSelected) {
            toast({
                variant: 'destructive',
                title: 'Gagal',
                description: 'Pilih minimal satu jam untuk satu tanggal',
            });
            return;
        }

        clearErrors();
        setProcessing(true);
        try {
            const bulkData = Object.entries(dateSlots).flatMap(([date, slotIndices]) => {
                return Array.from(slotIndices)
                    .sort((a, b) => a - b)
                    .map(index => ({
                        date,
                        ...timeSlots[index],
                        price: slotPrices[`${date}-${index}`],
                        normal_price: slotNormalPrices[`${date}-${index}`],
                    }));
            });

            await axios.post('/court-schedule/bulk-generate', {
                court_id: data.court_id,
                status: data.status,
                time_slots: bulkData,
            });
            toast({
                title: 'Sukses',
                description: 'Jadwal lapangan berhasil dibuat',
            });
            router.visit('/court-schedule');
        } catch (error: any) {
            if (error.response?.status === 422) {
                const errors = error.response.data.errors;
                setError(errors);
            } else {
                toast({
                    variant: 'destructive',
                    title: 'Gagal',
                    description: error.response?.data?.message || 'Terjadi kesalahan',
                });
            }
        } finally {
            setProcessing(false);
        }
    }

    const getSelectedSlotsForDate = (date: string) => {
        const selected = dateSlots[date];
        if (!selected) return [];
        return Array.from(selected)
            .sort((a, b) => a - b)
            .map(index => ({
                index,
                slot: timeSlots[index],
            }));
    };

    return (
        <div className="p-5">
            <Card>
                <CardHeader>
                    <CardTitle>Bulk Generate Jadwal Lapangan</CardTitle>
                    <CardDescription>Buat jadwal lapangan untuk beberapa tanggal sekaligus dengan custom harga per slot</CardDescription>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        <FormGroup
                            label="Lapangan"
                            error={errors.court_id}
                            required
                        >
                            <Select
                                value={data.court_id?.toString() || ''}
                                onValueChange={(value) => setData('court_id', value)}
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
                        </FormGroup>

                        <div className="grid grid-cols-2 gap-4">
                            <FormGroup
                                label="Tanggal Mulai"
                                error={errors.start_date}
                                required
                            >
                                <Input
                                    type="date"
                                    value={data.start_date || ''}
                                    onChange={(e) => setData('start_date', e.target.value)}
                                />
                            </FormGroup>

                            <FormGroup
                                label="Tanggal Selesai"
                                error={errors.end_date}
                                required
                            >
                                <Input
                                    type="date"
                                    value={data.end_date || ''}
                                    onChange={(e) => setData('end_date', e.target.value)}
                                />
                            </FormGroup>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <FormGroup
                                label="Harga Default"
                                error={errors.default_price}
                                required
                            >
                                <Input
                                    type="number"
                                    value={data.default_price || ''}
                                    onChange={(e) => updateDefaultPrice(e.target.value)}
                                    min="0"
                                    placeholder="Harga default untuk semua slot"
                                />
                            </FormGroup>

                            <FormGroup
                                label="Harga Normal Default"
                                error={errors.default_normal_price}
                                required
                            >
                                <Input
                                    type="number"
                                    value={data.default_normal_price || ''}
                                    onChange={(e) => updateDefaultNormalPrice(e.target.value)}
                                    min="0"
                                    placeholder="Harga normal default"
                                />
                            </FormGroup>
                        </div>

                        {dates.length > 0 && (
                            <FormGroup label="Jam Operasional per Tanggal" required>
                                <div className="space-y-3 mb-4">
                                    <div className="flex gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={selectAllAllDates}
                                        >
                                            Pilih Semua Tanggal & Jam
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={deselectAllAllDates}
                                        >
                                            Batal Semua Pilihan
                                        </Button>
                                        <div className="ml-auto flex items-center text-sm font-medium text-gray-600">
                                            Total Dipilih: {totalSelectedSlots} slot
                                        </div>
                                    </div>
                                </div>

                                <Tabs defaultValue={dates[0]} className="w-full">
                                    <div className="overflow-x-auto pb-4">
                                        <TabsList className="inline-flex gap-2">
                                            {dates.map((date) => (
                                                <TabsTrigger key={date} value={date} className="text-xs whitespace-nowrap">
                                                    {formatDate(date)}
                                                </TabsTrigger>
                                            ))}
                                        </TabsList>
                                    </div>

                                    {dates.map((date) => (
                                        <TabsContent key={date} value={date} className="space-y-4">
                                            <div className="space-y-3">
                                                <div className="flex items-center gap-2">
                                                    <Checkbox
                                                        checked={(dateSlots[date]?.size || 0) === timeSlots.length}
                                                        onCheckedChange={() => selectAllForDate(date)}
                                                        id={`select-all-${date}`}
                                                    />
                                                    <label htmlFor={`select-all-${date}`} className="text-sm font-medium cursor-pointer">
                                                        Pilih Semua ({dateSlots[date]?.size || 0}/{timeSlots.length})
                                                    </label>
                                                </div>
                                                <div className="grid grid-cols-6 gap-2 p-3 bg-gray-50 dark:bg-gray-950 rounded">
                                                    {timeSlots.map((slot, index) => (
                                                        <div key={index} className="flex items-center gap-1">
                                                            <Checkbox
                                                                checked={dateSlots[date]?.has(index) || false}
                                                                onCheckedChange={() => toggleSlot(date, index)}
                                                                id={`slot-${date}-${index}`}
                                                            />
                                                            <label
                                                                htmlFor={`slot-${date}-${index}`}
                                                                className="text-xs font-medium cursor-pointer whitespace-nowrap"
                                                            >
                                                                {slot.start_time}-{slot.end_time}
                                                            </label>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>

                                            {getSelectedSlotsForDate(date).length > 0 && (
                                                <div className="space-y-2 p-3 bg-black-50 dark:bg-black-950 rounded">
                                                    <h4 className="font-medium text-sm">Harga untuk slot yang dipilih:</h4>
                                                    <div className="grid grid-cols-2 gap-3">
                                                        {getSelectedSlotsForDate(date).map(({ index, slot }) => (
                                                            <div key={index} className="space-y-2 p-2 rounded border">
                                                                <label className="text-xs font-medium block">
                                                                    {slot.start_time}-{slot.end_time}
                                                                </label>
                                                                <Input
                                                                    type="number"
                                                                    value={slotPrices[`${date}-${index}`] || ''}
                                                                    onChange={(e) => updateSlotPrice(date, index, e.target.value)}
                                                                    min="0"
                                                                    placeholder="Harga"
                                                                    className="h-8"
                                                                />
                                                                <Input
                                                                    type="number"
                                                                    value={slotNormalPrices[`${date}-${index}`] || ''}
                                                                    onChange={(e) => updateSlotNormalPrice(date, index, e.target.value)}
                                                                    min="0"
                                                                    placeholder="Harga Normal"
                                                                    className="h-8"
                                                                />
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            )}
                                        </TabsContent>
                                    ))}
                                </Tabs>
                            </FormGroup>
                        )}

                        <FormGroup
                            label="Status"
                            error={errors.status}
                            required
                        >
                            <Select
                                value={data.status || 'available'}
                                onValueChange={(value) => setData('status', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="available">Tersedia</SelectItem>
                                    <SelectItem value="booked">Dipesan</SelectItem>
                                    <SelectItem value="closed">Ditutup</SelectItem>
                                </SelectContent>
                            </Select>
                        </FormGroup>
                    </div>
                    <Button
                        disabled={processing}
                        onClick={handleSubmit}
                        className="mt-6 w-full"
                    >
                        {processing ? 'Membuat...' : 'Buat Jadwal'}
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}

BulkGenerate.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Master Data', href: '#' },
            { label: 'Jadwal Lapangan', href: '/court-schedule' },
            { label: 'Bulk Generate', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
