import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import BlogForm from "./Form";
import axios from "axios";

export default function Create({ categories }: any) {
    async function handleSubmit(data: BlogType) {
        await axios.post(`/blog`, data);
    }

    return (
        <BlogForm
            initialData={{ category_id: '', title: '', content: '', image: '', tags: '' }}
            onSubmit={handleSubmit}
            submitButtonText="Simpan"
            successMessage="Data blog berhasil ditambah"
            redirectTo="/blog/create"
            categories={categories}
        />
    );
}

Create.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Blog', href: '#' },
            { label: 'Daftar Blog', href: '/blog' },
            { label: 'Tambah', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
