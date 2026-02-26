import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import UpdateCategoryForm from "./Form";
import axios from "axios";

export default function Edit({ updateCategory }: any) {
    async function handleSubmit(data: UpdateCategoryType) {
        await axios.put(`/update-category/${data.id}`, data);
    }

    return (
        <UpdateCategoryForm
            initialData={updateCategory}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data kategori update berhasil diubah"
            redirectTo="/update-category"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Update', href: '#' },
            { label: 'Kategori', href: '/update-category' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
