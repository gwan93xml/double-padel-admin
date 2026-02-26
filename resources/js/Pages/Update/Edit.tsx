import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import UpdateForm from "./Form";
import axios from "axios";

export default function Edit({ update, categories }: any) {
    async function handleSubmit(data: UpdateType) {
        await axios.put(`/update/${data.id}`, data);
    }

    return (
        <UpdateForm
            initialData={update}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data update berhasil diubah"
            redirectTo="/update"
            categories={categories}
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Update', href: '#' },
            { label: 'Daftar Update', href: '/update' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
