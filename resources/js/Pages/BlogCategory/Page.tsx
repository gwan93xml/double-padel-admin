'use client'

import DataTable from "@/Components/DataTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import axios from "axios";
import { router } from "@inertiajs/react";
import can from "@/hooks/can";
import { Badge } from "@/Components/ui/badge";


export default function Page() {
    const [refreshTrigger, setRefreshTrigger] = useState(0);
    return (
        <>
            <DataTable
                refreshTrigger={refreshTrigger}
                canCreate={can("create-blog-category")}
                canUpdate={can("update-blog-category")}
                canDelete={can("delete-blog-category")}
                title="Kategori Blog"
                description="Daftar kategori blog yang terdaftar dalam sistem"
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
                apiUrl="/blog-category"
                onCreate={() => {
                    router.visit('/blog-category/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/blog-category/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/blog-category/${id}`)
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
            { label: 'Kategori', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
