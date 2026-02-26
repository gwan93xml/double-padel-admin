import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import HomeNavigationForm from "./Form";
import axios from "axios";

export default function Edit({ homeNavigation }: any) {
    async function handleSubmit(data: HomeNavigationType) {
        await axios.put(`/home-navigation/${data.id}`, data);
    }

    return (
        <HomeNavigationForm
            initialData={homeNavigation}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data home navigation berhasil diubah"
            redirectTo="/home-navigation"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Home Navigation', href: '#' },
            { label: 'Daftar', href: '/home-navigation' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
