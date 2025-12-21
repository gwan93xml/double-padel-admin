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
                canCreate={can("create-venue")}
                canUpdate={can("update-venue")}
                canDelete={can("delete-venue")}
                title="Venue"
                description="Daftar venue yang terdaftar dalam sistem"
                columns={[
                    {
                        accessorKey: 'slug',
                        header: 'Slug',
                        cell: (info) => info.slug,
                    },
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                    {
                        accessorKey: 'city',
                        header: 'Kota',
                        cell: (info) => info.city,
                    },
                    {
                        accessorKey: 'province',
                        header: 'Provinsi',
                        cell: (info) => info.province,
                    },
                    {
                        accessorKey: 'min_price',
                        header: 'Harga Min',
                        cell: (info) => info.min_price ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(info.min_price) : '-',
                    },
                    {
                        accessorKey: 'max_price',
                        header: 'Harga Max',
                        cell: (info) => info.max_price ? new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(info.max_price) : '-',
                    },
                    {
                        accessorKey: 'average_rating',
                        header: 'Rating',
                        cell: (info) => info.average_rating ? `${info.average_rating}/5` : '-',
                    },
                ]}
                apiUrl="/venue"
                onCreate={() => {
                    router.visit('/venue/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/venue/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/venue/${id}`)
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
            { label: 'Venue', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
