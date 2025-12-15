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
                title="Member"
                description="Daftar member yang terdaftar dalam sistem"
                canCreate={false}
                canUpdate={false}
                canDelete={can("delete-member")}
                columns={[
                    {
                        accessorKey: 'email',
                        header: 'Email',
                        cell: (info) => info.email,
                    },
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                ]}
                apiUrl="/member"
                
                onEdit={async (id) => {
                    router.visit(`/member/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/member/${id}`)
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
            { label: 'Master File', href: '#' },
            { label: 'Member', href: '/member' },
        ]}>{page}</AuthenticatedLayout>
);
