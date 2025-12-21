import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import BlogCategoryForm from "./Form";
import axios from "axios";

export default function Edit({ blogCategory }: any) {
    async function handleSubmit(data: BlogCategoryType) {
        await axios.put(`/blog-category/${data.id}`, data);
    }

    return (
        <BlogCategoryForm
            initialData={blogCategory}
            onSubmit={handleSubmit}
            submitButtonText="Ubah"
            successMessage="Data kategori blog berhasil diubah"
            redirectTo="/blog-category"
        />
    );
}

Edit.layout = (page: any) => (
    <AuthenticatedLayout
        breadcrumbs={[
            { label: 'Dashboard', href: '/' },
            { label: 'Blog', href: '#' },
            { label: 'Kategori', href: '/blog-category' },
            { label: 'Edit', href: '#' },
        ]}>{page}</AuthenticatedLayout>
);
