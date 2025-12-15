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
                title="Role"
                description="Daftar role yang terdaftar dalam sistem"
                canCreate={can("create-role")}
                canUpdate={can("update-role")}
                canDelete={can("delete-role")}
                columns={[
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                ]}
                apiUrl="/role"
                onCreate={() => {
                    router.visit('/role/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/role/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/role/${id}`)
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

            { label: 'Dashboard', href: '' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Role', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
