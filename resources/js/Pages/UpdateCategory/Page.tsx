'use client'

import { Badge } from "@/Components/ui/badge";
import DataTable from "@/Components/DataTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { router } from "@inertiajs/react";
import { useState } from "react";
import axios from "axios";
import can from "@/hooks/can";

export default function Page() {
    const [refreshTrigger, setRefreshTrigger] = useState(0);

    return (
        <>
            <DataTable
                refreshTrigger={refreshTrigger}
                canCreate={can('create-update-category')}
                canUpdate={can('update-update-category')}
                canDelete={can('delete-update-category')}
                title="Kategori Update"
                description="Daftar kategori update yang terdaftar dalam sistem"
                columns={[
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                    {
                        accessorKey: 'slug',
                        header: 'Slug',
                        cell: (info) => (
                            <Badge variant="secondary">{info.slug}</Badge>
                        ),
                    },
                ]}
                apiUrl="/update-category"
                onCreate={() => {
                    router.visit('/update-category/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/update-category/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/update-category/${id}`)
                        setRefreshTrigger((prev) => prev + 1)
                    } catch (error) {
                        alert('Failed to delete data')
                    }
                }}
            />
        </>
    );
}

Page.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Update', href: '#' },
            { label: 'Kategori', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
