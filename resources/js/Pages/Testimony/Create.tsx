import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import TestimonyForm from "./Form";
import axios from "axios";

export default function Create() {
    async function handleSubmit(data: TestimonyType) {
        await axios.post(`/testimony`, data);
    }

    return (
        <TestimonyForm
            initialData={{ name: '', title: '', content: '', image: '', rating: 5 }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data testimoni berhasil ditambah"
            redirectTo="/testimony/create"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Testimoni', href: '/testimony' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
