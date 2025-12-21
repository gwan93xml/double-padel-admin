import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import BlogCategoryForm from "./Form";
import axios from "axios";

export default function Create() {
    async function handleSubmit(data: BlogCategoryType) {
        await axios.post(`/blog-category`, data);
    }

    return (
        <BlogCategoryForm
            initialData={{ name: '', description: '' }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data kategori blog berhasil ditambah"
            redirectTo="/blog-category/create"
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Blog', href: '#' },
            { label: 'Kategori', href: '/blog-category' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
