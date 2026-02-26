import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import HomeNavigationForm from "./Form";
import axios from "axios";

export default function Create() {
    async function handleSubmit(data: HomeNavigationType) {
        await axios.post('/home-navigation', data);
    }

    return (
        <HomeNavigationForm
            initialData={{ icon: '', small_icon: '', name: '', url: '' }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data home navigation berhasil ditambah"
            redirectTo="/home-navigation/create"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Home Navigation', href: '#' },
            { label: 'Daftar', href: '/home-navigation' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
