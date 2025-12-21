import { Button } from "@/Components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/Components/ui/card";
import FormGroup from "@/Components/ui/form-group";
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { toast } from "@/hooks/use-toast";
import { router, useForm } from "@inertiajs/react";
import axios from "axios";
import { useEffect, useState } from "react";
import { CourtSchedule, Court, User } from "@/types/court-schedule-type";

interface FormProps {
    initialData?: CourtSchedule;
    courts: Court[];
    users: User[];
    onSubmit: (data: any) => Promise<void>;
    submitButtonText: string;
    successMessage: string;
    redirectTo: string;
}

export default function CourtScheduleForm({
    initialData,
    courts,
    users,
    onSubmit,
    submitButtonText,
    successMessage,
    redirectTo
}: FormProps) {
    const { data, setData, clearErrors, errors, setError } = useForm({
        id : initialData?.id || '',
        court_id: initialData?.court_id || '',
        user_id: initialData?.user_id || '',
        date: initialData?.date || '',
        start_time: initialData?.start_time || '',
        end_time: initialData?.end_time || '',
        price: initialData?.price || '',
        status: initialData?.status || 'available',
    } as any);

    const [processing, setProcessing] = useState(false);

    async function handleSubmit() {
        clearErrors();
        setProcessing(true);
        try {
            await onSubmit(data);
            toast({
                title: 'Sukses',
                description: successMessage,
            });
            router.visit(redirectTo);
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

    useEffect(() => {
        if (data.start_time) {
            const [hours] = data.start_time.split(':').map(Number);
            const formattedStartTime = `${String(hours).padStart(2, '0')}:00`;
            
            const startDate = new Date();
            startDate.setHours(hours, 0);
            startDate.setHours(startDate.getHours() + 1);
            const endHours = String(startDate.getHours()).padStart(2, '0');
            setData({
                ...data,
                start_time: formattedStartTime,
                end_time: `${endHours}:00`,
            })
        }
    }, [data.start_time])

    return (
        <div className="p-5">
            <Card>
                <CardHeader>
                    <CardTitle>Form Jadwal Lapangan</CardTitle>
                    <CardDescription>Isikan data jadwal lapangan dengan benar</CardDescription>
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

                        <FormGroup
                            label="Tanggal"
                            error={errors.date}
                            required
                        >
                            <Input
                                type="date"
                                value={data.date || ''}
                                onChange={(e) => setData('date', e.target.value)}
                            />
                        </FormGroup>

                        <div className="grid grid-cols-2 gap-4">
                            <FormGroup
                                label="Jam Mulai"
                                error={errors.start_time}
                                required
                            >
                                <Input
                                    type="time"
                                    value={data.start_time || ''}
                                    onChange={(e) => setData('start_time', e.target.value)}
                                />
                            </FormGroup>

                            <FormGroup
                                label="Jam Selesai"
                                error={errors.end_time}
                                required
                            >
                                <Input
                                    type="time"
                                    value={data.end_time || ''}
                                    onChange={(e) => setData('end_time', e.target.value)}
                                />
                            </FormGroup>
                        </div>

                        <FormGroup
                            label="Harga"
                            error={errors.price}
                            required
                        >
                            <Input
                                type="number"
                                value={data.price || ''}
                                onChange={(e) => setData('price', e.target.value)}
                                min="0"
                            />
                        </FormGroup>

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
                        {processing ? `${submitButtonText}...` : submitButtonText}
                    </Button>
                </CardContent>
            </Card>
        </div>
    );
}
