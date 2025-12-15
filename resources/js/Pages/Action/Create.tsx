import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ActionForm from "./Form";
import axios from "axios";

export default function Create() {
    async function handleSubmit(data: ActionType) {
        await axios.post(`/action`, data);
    }

    return (
        <ActionForm
            initialData={{ name: '' }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data aksi berhasil ditambah"
            redirectTo="/action/create"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Aksi', href: '/action' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
