import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import PaymentMethodForm from "./Form";
import axios from "axios";

export default function Create() {
    async function handleSubmit(data: PaymentMethodType) {
        await axios.post(`/payment-method`, data);
    }

    return (
        <PaymentMethodForm
            initialData={{ code: '', name: '', image: '', how_to_pay: [] }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data metode pembayaran berhasil ditambah"
            redirectTo="/payment-method"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Metode Pembayaran', href: '/payment-method' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
