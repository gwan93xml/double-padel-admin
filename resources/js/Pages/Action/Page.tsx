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
                canCreate={can("create-action")}
                canUpdate={can("update-action")}
                canDelete={can("delete-action")}
                title="Aksi"
                description="Daftar aksi yang terdaftar dalam sistem"
                columns={[
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                ]}
                apiUrl="/action"
                onCreate={() => {
                    router.visit('/action/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/action/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/action/${id}`)
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
            { label: 'Pengaturan', href: '#' },
            { label: 'Aksi', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
