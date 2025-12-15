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
                title="User"
                description="Daftar user yang terdaftar dalam sistem"
                canCreate={can("create-user")}
                canUpdate={can("update-user")}
                canDelete={can("delete-user")}
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
                    {
                        accessorKey: 'name',
                        header: 'Roles',
                        cell: (info) => info.roles.map((role: any) => role.name).join(', '),
                    },
                ]}
                apiUrl="/user"
                onCreate={() => {
                    router.visit('/user/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/user/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/user/${id}`)
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
            { label: 'User', href: '/user' },
        ]}>{page}</AuthenticatedLayout>
);
