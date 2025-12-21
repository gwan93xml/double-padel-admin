'use client'

import DataTable from "@/Components/DataTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
import { BookingStatus } from "@/Components/booking-status";
import { DropdownMenuItem } from "@/Components/ui/dropdown-menu";
import { BookingType } from "./@types/booking-type";
import { CheckCheck } from "lucide-react";
import { showAlertDialog } from "@/Components/ui/promise-alert-dialog";


export default function Page() {
    const [refreshTrigger, setRefreshTrigger] = useState(0);
    const handleCompleteBooking = async (booking: BookingType) => {
       showAlertDialog({
            title: `Selesaikan Booking ${booking.booking_number}`,
            description: `Apakah Anda yakin ingin menandai booking dengan nomor ${booking.booking_number} sebagai selesai?`,
            cancelText: 'Batal',
            confirmText: 'Selesaikan',
        }).then(async (confirmed : any) => {
            if (confirmed) {
                try {
                    await axios.post(`/booking/${booking.id}/complete`)
                    setRefreshTrigger(prev => prev + 1)
                } catch (error) {
                    alert('Failed to complete booking')
                }
            }   
       })
    }
    return (
        <>
            <DataTable
                refreshTrigger={refreshTrigger}
                title="Booking"
                description="Daftar booking yang terdaftar dalam sistem"
                isFilterDate
                canCreate={false}
                canUpdate={false}
                canDelete={false}
                columns={[
                    {
                        accessorKey: 'booking_number',
                        header: 'Nomor Booking',
                        cell: (info) => info.booking_number,
                    },
                    {
                        accessorKey: 'court_schedule_id',
                        header: 'Venue - Lapangan',
                        cell: (info) => info.court_schedule.court.venue.name + ' - ' + info.court_schedule.court.name,
                    },
                    {
                        accessorKey: 'user_id',
                        header: 'Member',
                        cell: (info) => info.user ? info.user.name : '-',
                    },
                    {
                        accessorKey: '',
                        header: 'Tanggal',
                        cell: (info) => new Date(info.court_schedule.date).toLocaleDateString('id-ID'),
                    },
                    {
                        accessorKey: '',
                        header: 'Waktu',
                        cell: (info) => info.court_schedule.start_time + ' - ' + info.court_schedule.end_time,
                    },
                    {
                        accessorKey: 'created_at',
                        header: 'Tanggal Booking',
                        cell: (info) => new Date(info.created_at).toLocaleDateString('id-ID') + ' ' + new Date(info.created_at).toLocaleTimeString('id-ID'),
                    },
                    {
                        accessorKey: 'status',
                        header: 'Status',
                        cell: (info) => <BookingStatus status={info.status} />,
                    }
                ]}
                apiUrl="/booking"
                onEdit={async (id) => {
                    router.visit(`/booking/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/booking/${id}`)
                        setRefreshTrigger(prev => prev + 1)
                    } catch (error) {
                        alert('Failed to delete data')
                    }
                }}
                additionalFilters={[
                    {
                        key: 'status',
                        label : 'Status',
                        type: 'select',
                        options: [
                            {
                                value : 'pending',
                                label : 'Pending'
                            },
                            {
                                value: 'cancelled',
                                label: 'Dibatalkan'
                            },
                            {
                                value: 'confirmed',
                                label: 'Dikonfirmasi'
                            },
                            {
                                value: 'completed',
                                label: 'Selesai'
                            },
                        ]
                    },

                ]}
                additionalActionButtons={(row: BookingType) => {
                    if (row.status !== 'confirmed') return null;
                    return (
                        <DropdownMenuItem onClick={() => handleCompleteBooking(row)}>
                            <CheckCheck className="mr-2 h-4 w-4" />
                            Selesaikan
                        </DropdownMenuItem>
                    )
                }}
            />

        </>
    )
}
Page.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[

            { label: 'Dashboard', href: '/' },
            { label: 'Transaksi', href: '#' },
            { label: 'Booking', href: '/booking' },
        ]}>{page}</AuthenticatedLayout>
);
