import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import UpdateCategoryForm from "./Form";
import axios from "axios";

export default function Create() {
    async function handleSubmit(data: UpdateCategoryType) {
        await axios.post('/update-category', data);
    }

    return (
        <UpdateCategoryForm
            initialData={{ name: '' }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data kategori update berhasil ditambah"
            redirectTo="/update-category/create"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Update', href: '#' },
            { label: 'Kategori', href: '/update-category' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
