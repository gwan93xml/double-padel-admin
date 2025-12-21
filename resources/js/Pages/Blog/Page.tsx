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
                canCreate={can("create-blog")}
                canUpdate={can("update-blog")}
                canDelete={can("delete-blog")}
                title="Blog"
                description="Daftar blog yang terdaftar dalam sistem"
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
                    }
                ]}
                apiUrl="/blog"
                onCreate={() => {
                    router.visit('/blog/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/blog/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/blog/${id}`)
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
            { label: 'Blog', href: '#' },
            { label: 'Daftar Blog', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
