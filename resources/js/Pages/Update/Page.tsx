'use client'

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
                canCreate={can('create-update')}
                canUpdate={can('update-update')}
                canDelete={can('delete-update')}
                title="Update"
                description="Daftar update yang terdaftar dalam sistem"
                columns={[
                    {
                        accessorKey: 'title',
                        header: 'Judul',
                        cell: (info) => info.title,
                    },
                    {
                        accessorKey: 'category.name',
                        header: 'Kategori',
                        cell: (info) => info.category?.name,
                    },
                    {
                        accessorKey: 'slug',
                        header: 'Slug',
                        cell: (info) => info.slug,
                    },
                    {
                        accessorKey: 'created_at',
                        header: 'Tanggal Publikasi',
                        cell: (info) => info.created_at ? new Date(info.created_at).toLocaleDateString('id-ID') : '-',
                    },
                ]}
                apiUrl="/update"
                onCreate={() => {
                    router.visit('/update/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/update/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/update/${id}`)
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
            { label: 'Daftar Update', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
