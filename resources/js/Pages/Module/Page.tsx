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
                canCreate={can("create-module")}
                canUpdate={can("update-module")}
                canDelete={can("delete-module")}
                title="Module"
                description="Daftar module yang terdaftar dalam sistem"
                columns={[
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                    {
                        accessorKey: 'name',
                        header: 'Aksi Modul',
                        cell: (info) => info.actions.map((action: any) => {
                            return (
                                <span key={action.id} className="text-xs text-white mr-3 bg-green-700 py-1  px-2 rounded-md">
                                    {action.name}
                                </span>
                            )
                        }),

                    },
                ]}
                apiUrl="/module"
                onCreate={() => {
                    router.visit('/module/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/module/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/module/${id}`)
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
            { label: 'Module', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
