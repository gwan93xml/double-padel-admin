import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import ModuleForm from "./Form";
import axios from "axios";

export default function Edit({ module, actions }: { module: ModuleType; actions: ActionType[] }) {
    async function handleSubmit(data: ModuleType) {
        await axios.put(`/module/${data.id}`, data);
    }

    return (
        <ModuleForm
            initialData={module}
            actions={actions}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data module berhasil diubah"
            redirectTo="/module"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Pengaturan', href: '#' },
            { label: 'Module', href: '/module' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
