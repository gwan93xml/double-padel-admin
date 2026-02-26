import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import UpdateForm from "./Form";
import axios from "axios";

export default function Create({ categories }: any) {
    async function handleSubmit(data: UpdateType) {
        await axios.post('/update', data);
    }

    return (
        <UpdateForm
            initialData={{ update_category_id: '', title: '', body: '', image: '' }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data update berhasil ditambah"
            redirectTo="/update/create"
            categories={categories}
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Update', href: '#' },
            { label: 'Daftar Update', href: '/update' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
