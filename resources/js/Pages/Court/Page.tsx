'use client'

import DataTable from "@/Components/DataTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
import can from "@/hooks/can";


export default function Page() {
    const [refreshTrigger, setRefreshTrigger] = useState(0);
    return (
        <>
            <DataTable
                refreshTrigger={refreshTrigger}
                canCreate={can("create-court")}
                canUpdate={can("update-court")}
                canDelete={can("delete-court")}
                title="Court"
                description="Daftar court yang terdaftar dalam sistem"
                columns={[
                    {
                        accessorKey: 'name',
                        header: 'Nama Court',
                        cell: (info) => info.name,
                    },
                    {
                        accessorKey: 'court_type',
                        header: 'Tipe',
                        cell: (info) => info.court_type,
                    },
                    {
                        accessorKey: 'venue.name',
                        header: 'Venue',
                        cell: (info) => info.venue?.name || '-',
                    },
                    {
                        accessorKey: 'price_per_hour',
                        header: 'Harga/Jam',
                        cell: (info) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(info.price_per_hour),
                    },
                    {
                        accessorKey: 'capacity',
                        header: 'Kapasitas',
                        cell: (info) => `${info.capacity} orang`,
                    },
                    {
                        accessorKey: 'status',
                        header: 'Status',
                        cell: (info) => {
                            const statusMap: Record<string, { label: string; color: string }> = {
                                available: { label: 'Tersedia', color: 'bg-green-100 text-green-800' },
                                maintenance: { label: 'Maintenance', color: 'bg-yellow-100 text-yellow-800' },
                                closed: { label: 'Ditutup', color: 'bg-red-100 text-red-800' },
                            };
                            const status = statusMap[info.status] || { label: info.status, color: 'bg-gray-100 text-gray-800' };
                            return <span className={`px-2 py-1 rounded-full text-xs font-medium ${status.color}`}>{status.label}</span>;
                        },
                    },
                ]}
                apiUrl="/court"
                onCreate={() => {
                    router.visit('/court/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/court/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/court/${id}`)
                        setRefreshTrigger(prev => prev + 1)
                    } catch (error) {
                        alert('Failed to delete data')
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
            { label: 'Court', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
