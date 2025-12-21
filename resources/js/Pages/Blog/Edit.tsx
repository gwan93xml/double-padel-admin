import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import BlogForm from "./Form";
import axios from "axios";

export default function Edit({ blog, categories }: any) {
    async function handleSubmit(data: BlogType) {
        await axios.put(`/blog/${data.id}`, data);
    }

    return (
        <BlogForm
            initialData={blog}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data blog berhasil diubah"
            redirectTo="/blog"
            categories={categories}
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Blog', href: '#' },
            { label: 'Daftar Blog', href: '/blog' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
