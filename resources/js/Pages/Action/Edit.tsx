import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ActionForm from "./Form";
import axios from "axios";

export default function Edit({ action }: any) {
    async function handleSubmit(data: ActionType) {
        await axios.put(`/action/${data.id}`, data);
    }

    return (
        <ActionForm
            initialData={action}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data aksi berhasil diubah"
            redirectTo="/action"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Aksi', href: '/action' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
