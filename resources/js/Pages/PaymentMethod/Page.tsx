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
                canCreate={can("create-payment-method")}
                canUpdate={can("update-payment-method")}
                canDelete={can("delete-payment-method")}
                title="Metode Pembayaran"
                description="Daftar metode pembayaran yang tersedia"
                columns={[
                    {
                        accessorKey: 'group',
                        header: 'Grup',
                        cell: (info) => info.group,
                    },
                    {
                        accessorKey: 'image',
                        header: 'Gambar',
                        cell: (info) => info.image ? <img src={info.image} alt={info.name} className="h-10 w-10 object-contain"/> : 'No Image',
                    },
                    {
                        accessorKey: 'code',
                        header: 'Kode',
                        cell: (info) => info.code,
                    },
                    {
                        accessorKey: 'name',
                        header: 'Nama',
                        cell: (info) => info.name,
                    },
                    {
                        accessorKey: 'transaction_fee',
                        header: 'Biaya Transaksi',
                        cell: (info) => `Rp ${info.transaction_fee?.toLocaleString('id-ID') || 0}`,
                    },
                ]}
                apiUrl="/payment-method"
                onCreate={() => {
                    router.visit('/payment-method/create')
                }}
                onEdit={async (id) => {
                    router.visit(`/payment-method/${id}/edit`)
                }}
                onDelete={async (id) => {
                    try {
                        await axios.delete(`/payment-method/${id}`)
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
            { label: 'Metode Pembayaran', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
