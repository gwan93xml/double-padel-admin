import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import PaymentMethodForm from "./Form";
import axios from "axios";

export default function Edit({ paymentMethod }: { paymentMethod: PaymentMethodType }) {
    async function handleSubmit(data: PaymentMethodType) {
        await axios.put(`/payment-method/${paymentMethod.id}`, data);
    }

    return (
        <PaymentMethodForm
            initialData={paymentMethod}
            onSubmit={handleSubmit}
            submitButtonText="Update"
            successMessage="Data metode pembayaran berhasil diubah"
            redirectTo="/payment-method"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Metode Pembayaran', href: '/payment-method' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
