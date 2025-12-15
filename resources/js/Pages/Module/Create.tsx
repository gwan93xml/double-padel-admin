import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ModuleForm from "./Form";
import axios from "axios";

export default function Create({ actions }: { actions: ActionType[] }) {
    async function handleSubmit(data: ModuleType) {
        await axios.post(`/module`, data);
    }

    return (
        <ModuleForm
            initialData={{ name: '', actions: [] }}
            actions={actions}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data module berhasil ditambah"
            redirectTo="/module/create"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Module', href: '/module' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
