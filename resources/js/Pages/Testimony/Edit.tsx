import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import TestimonyForm from "./Form";
import axios from "axios";

export default function Edit({ testimony }: any) {
    async function handleSubmit(data: TestimonyType) {
        await axios.put(`/testimony/${data.id}`, data);
    }

    return (
        <TestimonyForm
            initialData={testimony}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data testimoni berhasil diubah"
            redirectTo="/testimony"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Testimoni', href: '/testimony' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
