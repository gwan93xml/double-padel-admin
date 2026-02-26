'use client'

import DataTable from "@/Components/DataTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Button } from "@/Components/ui/button";
import CourtScheduleStatus from "@/Components/CourtScheduleStatus";
import { useState } from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
import can from "@/hooks/can";
import { WrenchIcon, Calendar } from "lucide-react";


export default function Page() {
    const [refreshTrigger, setRefreshTrigger] = useState(0);
    return (
        <>
            <DataTable
                refreshTrigger={refreshTrigger}
                canCreate={can("create-court-schedule")}
                canUpdate={can("update-court-schedule")}
                canDelete={can("delete-court-schedule")}
                title="Jadwal Lapangan"
                description="Daftar jadwal lapangan yang terdaftar dalam sistem"
                additionalTools={
                    <>
                        {can("create-court-schedule") && (
                            <Button variant="outline" onClick={() => router.visit('/court-schedule/calendar')}>
                                <Calendar className="w-4 h-4" />
                                Kalender
                            </Button>
                        )}
                        {can("create-court-schedule") && (
                            <Button variant="outline" onClick={() => router.visit('/court-schedule/bulk-generate')}>
                                <WrenchIcon />
                                Generate
                            </Button>
                        )}
                    </>
                }
                columns={[
                    {
                        accessorKey: 'court_id',
                        header: 'Lapangan',
                        cell: (info) => `${info.court.venue.name} - ${info.court.name}`,
                    },
                    {
                        accessorKey: 'date',
                        header: 'Tanggal',
                        cell: (info) => new Date(info.date).toLocaleDateString('id-ID'),
                    },
                    {
                        accessorKey: 'start_time',
                        header: 'Waktu',
                        cell: (info) => `${info.start_time} - ${info.end_time}`,
                    },
                    {
                        accessorKey: 'user',
                        header: 'Pengguna',
                        cell: (info) => info.user ? info.user.name : '-',
                    },
                    {
                        accessorKey: 'price',
                        header: 'Harga',
                        cell: (info) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(info.price),
                    },
                    {
                        accessorKey: 'normal_price',
                        header: 'Harga Normal',
                        cell: (info) => info.normal_price !== null && info.normal_price !== undefined
                            ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(info.normal_price)
                            : '-',
                    },
                    {
                        accessorKey: 'status',
                        header: 'Status',
                        cell: (info) => <CourtScheduleStatus status={info.status} />,
                    },
                ]}
                apiUrl="/court-schedule"
                onCreate={() => {
                    router.visit('/court-schedule/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/court-schedule/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/court-schedule/${id}`)
                        setRefreshTrigger(prev => prev + 1)
                    } catch (error) {
                        alert('Gagal menghapus data')
                    }
                }}
            />

        </>
    )
}
Page.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[

            { label: 'Dashboard', href: '/' },
            { label: 'Master Data', href: '#' },
            { label: 'Jadwal Lapangan', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
