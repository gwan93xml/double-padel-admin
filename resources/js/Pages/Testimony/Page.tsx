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
                canCreate={can("create-testimony")}
                canUpdate={can("update-testimony")}
                canDelete={can("delete-testimony")}
                title="Testimoni"
                description="Daftar testimoni pelanggan"
                columns={[
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                    {
                        accessorKey: 'title',
                        header: 'Judul',
                        cell: (info) => info.title,
                    },
                    {
                        accessorKey: 'rating',
                        header: 'Rating',
                        cell: (info) => `⭐ ${info.rating}/5`,
                    },
                ]}
                apiUrl="/testimony"
                onCreate={() => {
                    router.visit('/testimony/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/testimony/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/testimony/${id}`)
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
            { label: 'Testimoni', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
