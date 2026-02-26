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
                canCreate={can('create-home-navigation')}
                canUpdate={can('update-home-navigation')}
                canDelete={can('delete-home-navigation')}
                title="Home Navigation"
                description="Daftar home navigation yang terdaftar dalam sistem"
                columns={[
                    {
                        accessorKey: 'icon',
                        header: 'Icon',
                        cell: (info) => info.icon ? (
                            <img src={info.icon} alt="icon" className="h-10 w-10 rounded object-cover" />
                        ) : '-',
                    },
                    {
                        accessorKey: 'small_icon',
                        header: 'Small Icon',
                        cell: (info) => info.small_icon ? (
                            <img src={info.small_icon} alt="small icon" className="h-8 w-8 rounded object-cover" />
                        ) : '-',
                    },
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                    {
                        accessorKey: 'url',
                        header: 'URL',
                        cell: (info) => info.url,
                    },
                ]}
                apiUrl="/home-navigation"
                onCreate={() => {
                    router.visit('/home-navigation/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/home-navigation/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/home-navigation/${id}`)
                        setRefreshTrigger((prev) => prev + 1)
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
            { label: 'Home Navigation', href: '#' },
            { label: 'Daftar', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
