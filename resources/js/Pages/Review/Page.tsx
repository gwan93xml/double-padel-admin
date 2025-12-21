'use client'

import DataTable from "@/Components/DataTable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { useState } from "react";
import axios from "axios";
import can from "@/hooks/can";


export default function Page() {
    const [refreshTrigger, setRefreshTrigger] = useState(0);

    const renderRating = (value: number) => {
        if (!value) return '-';
        const stars = '⭐'.repeat(value);
        return `${stars} ${value}/5`;
    };

    return (
        <>
            <DataTable
                refreshTrigger={refreshTrigger}
                canDelete={can("delete-review")}
                title="Review"
                description="Daftar review dari pelanggan tentang venue"
                columns={[
                    {
                        accessorKey: 'venue.name',
                        header: 'Venue',
                        cell: (info) => info.venue?.name || '-',
                    },
                    {
                        accessorKey: 'user.name',
                        header: 'Pelanggan',
                        cell: (info) => info.user?.name || '-',
                    },
                    {
                        accessorKey: 'cleanliness_rating',
                        header: 'Kebersihan',
                        cell: (info) => renderRating(info.cleanliness_rating),
                    },
                    {
                        accessorKey: 'court_condition_rating',
                        header: 'Kondisi Lapangan',
                        cell: (info) => renderRating(info.court_condition_rating),
                    },
                    {
                        accessorKey: 'communication_rating',
                        header: 'Komunikasi',
                        cell: (info) => renderRating(info.communication_rating),
                    },
                    {
                        accessorKey: 'comment',
                        header: 'Komentar',
                        cell: (info) => {
                            const comment = info.comment || '-';
                            return comment.length > 50 ? comment.substring(0, 50) + '...' : comment;
                        },
                    },
                    {
                        accessorKey: 'created_at',
                        header: 'Tanggal Review',
                        cell: (info) => new Date(info.created_at).toLocaleDateString('id-ID'),
                    }
                ]}
                apiUrl="/review"
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/review/${id}`)
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
            { label: 'Review', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
